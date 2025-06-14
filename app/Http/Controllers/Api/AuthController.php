<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Traits\ResponseAPI;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Token;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\RefreshTokenRepository;
use Carbon\Carbon;

class AuthController extends Controller
{
    use ResponseAPI;

    protected $tokenRepository;
    protected $refreshTokenRepository;

    public function __construct(
        TokenRepository        $tokenRepository,
        RefreshTokenRepository $refreshTokenRepository
    )
    {
        $this->tokenRepository = $tokenRepository;
        $this->refreshTokenRepository = $refreshTokenRepository;
    }

    public function register(Request $request)
    {
        DB::beginTransaction();
        try {
            $fields = $request->validate([
                "name" => "required|string",
                "email" => "required|string|unique:users,email",
                'phone' => "nullable|string|unique:users,phone",
                "password" => "required|string|min:8",
                "role" => "required|string|in:SUPER_ADMIN,PLAYER,CAREER_PROVIDER,COURT_OWNER",
                "profile_picture" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048",
            ]);

            if ($fields["role"] == "CAREER_PROVIDER" && str_contains($fields["role"], "CAREER_PROVIDER")) {
                return response()->json([
                    "status" => "error",
                    "message" => "You must use your company email"
                ], 401);
            }

            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('images/profile', $fileName, 'public');
                $fields['profile_picture'] = $path;
            } else {
                $fields['profile_picture'] = null;
            }

            $user = User::create([
                'name' => $fields["name"],
                'email' => $fields["email"],
                'phone' => $fields['phone'] ?? null,
                'password' => bcrypt($fields["password"]),
                'role_id' => $fields["role"],
                'profile_picture' => $fields['profile_picture'],
            ]);

            $data = $this->createTokenAndRefreshToken($user);

            DB::commit(); // REDIS MSH ERROR NANTI PINDAHIN KEBAWAH REDIS

            Redis::set("users:{$request->email}", json_encode($user->toArray()), 'EX', 60);
            event(new Registered($user));

            return $this->sendSuccessResponse(
                "Register successfully",
                201,
                null,
                $data,
            );
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                "status" => "error",
                "message" => $e->errors()
            ], 422);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse($validator->errors(), 422, 'Validation failed', null);
            }

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return $this->sendErrorResponse('User not found', 404, null, null);
            }

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $data = $this->createTokenAndRefreshToken($user);


            //            $user = Redis::get("users:{$request->email}") || Redis::set("users:{$request->email}", 60, function() use ($request) {
            //                    Log::info("Caching user data for key");
            //                    return User::where('email', $request->email)->firstOrFail();
            //                });

            //            $user = Redis::remember("users:{$request->email}", 60, function() use ($request) {
            //                Log::info("Caching user data for key");
            //                return User::where('email', $request->email)->firstOrFail();
            //            });

            return $this->sendSuccessResponse(
                "Login successfully",
                null,
                null,
                $data,
            );
        } catch (ValidationException $e) {
            return $this->sendExceptionResponse(
                "Unauthorized",
                401,
                null,
                $e,
            );
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->token()->revoke();
            $this->tokenRepository->revokeAccessToken($request->user()->token()->id);
            $this->refreshTokenRepository->revokeRefreshTokensByAccessTokenId($request->user()->token()->id);
            return $this->sendSuccessResponse(
                "Logout successfully",
                //            204, tidak akan mengembalikan content
                200,
                null,
                null
            );
        } catch (ValidationException $e) {
            return $this->sendExceptionResponse(null, 500, 'Logout fail', $e);
        }
    }

    public function logoutAll(Request $request)
    {
        $user = $request->user();

        $user->tokens->each(function ($token) {
            $token->revoke();
            $this->tokenRepository->revokeAccessToken($token->id);
            $this->refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);
        });

        return $this->sendSuccessResponse(
            'Logged out from all devices',
            200,
            null,
            null
        );
    }


    public function refreshToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendErrorResponse($validator->errors(), 422, 'Validation failed', null);
        }

        $refreshToken = $this->refreshTokenRepository->find($request->refresh_token);

        if (!$refreshToken || $refreshToken->revoked || $refreshToken->expires_at->isPast()) {
            return $this->sendErrorResponse('Invalid or expired refresh token', 401, 'Unauthorized', null);
        }

        $token = $refreshToken->accessToken;

        if (!$token || $token->revoked) {
            return $this->sendErrorResponse('Invalid or expired token', 401, 'Unauthorized', null);
        }

        $user = $token->user;

        $token->revoke();

        $newTokenResult = $user->createToken('API Token');
        $newToken = $newTokenResult->token;
        $newToken->expires_at = now()->addHours(1);
        $newToken->save();

        $refreshToken->access_token_id = $newToken->id;
        $refreshToken->save();

        $data = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role ?? 'user',
                'profile_picture' => $user->profile_picture ? asset('storage/' . $user->profile_picture) : null,
            ],
            "access_token" => $newTokenResult->accessToken,
            'refresh_token' => $refreshToken->id,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'expires_at' => $token->expires_at->toISOString(),
        ];

        return $this->sendSuccessResponse(
            "Token refreshed successfully",
            200,
            null,
            $data,
        );
    }

    public function profile(Request $request)
    {
        try {
            $user = $request->user();
            return response()->json([
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role ?? 'user',
                    'profile_picture' => $user->profile_picture ? asset('storage/' . $user->profile_picture) : null,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get profile'
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();
            $fields = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
                'phone' => $user->phone,
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('images/profile', $fileName, 'public');
                $fields['profile_picture'] = $path;
            }

            $user->update($fields);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role ?? 'user',
                    'profile_picture' => $user->profile_picture ? asset('storage/' . $user->profile_picture) : null,
                    'updated_at' => $user->updated_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                null,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function test()
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Test successful',
                'data' => [
                    'message' => 'API is working',
                    'timestamp' => now()->toISOString(),
                    'passport_installed' => class_exists('Laravel\Passport\Passport'),
                    'users_count' => User::count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createTokenAndRefreshToken($user): array
    {
        $tokenResult = $user->createToken('API Token');
        $token = $tokenResult->token;
        $token->expires_at = now()->addHours(1);
        $token->save();

        $refreshToken = $this->createRefreshToken($token);

        $data = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role ?? 'user',
                'profile_picture' => $user->profile_picture ? asset('storage/' . $user->profile_picture) : null,
            ],
            'access_token' => $tokenResult->accessToken,
            'refresh_token' => $refreshToken->id,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'expires_at' => $token->expires_at->toISOString(),
        ];
        return $data;
    }

    private function createRefreshToken($accessToken)
    {
        return $this->refreshTokenRepository->create([
            'id' => Str::uuid()->toString(),
            'access_token_id' => $accessToken->id,
            'revoked' => false,
            'expires_at' => now()->addDays(30),
        ]);
    }

    public function roles(Request $request)
    {
        $data = [];
        if(!$request){
            $data = Role::all();
        }
        else{
            $data = Role::where('type', $request->type)->get();
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Roles retrieved successfully',
            'data' => $data
        ], 200);
    }
}
