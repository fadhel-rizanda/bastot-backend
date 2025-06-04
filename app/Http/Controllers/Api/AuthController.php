<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ResponseAPI;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ResponseAPI;

    public function register(Request $request)
    {
        DB::beginTransaction();
        try {
            $fields = $request->validate([
                "name" => "required|string",
                "email" => "required|string|unique:users,email",
                "password" => "required|string|min:8",
                "role" => "required|string|in:SUPER_ADMIN,PLAYER,CAREER_PROVIDER,COURT_OWNER",
            ]);

            if ($fields["role"] == "CAREER_PROVIDER" && str_contains($fields["role"], "CAREER_PROVIDER")) {
                return response()->json([
                    "status" => "error",
                    "message" => "You must use your company email"
                ], 401);
            }

            $user = User::create([
                'name' => $fields["name"],
                'email' => $fields["email"],
                'password' => bcrypt($fields["password"]),
                'role_id' => $fields["role"],
            ]);

            $tokenResult = $user->createToken('API Token');
            $token = $tokenResult->token;
            $token->expires_at = now()->addHours(1);
            $token->save();

            $refreshToken = $user->createToken('Refresh Token');
            $refreshTokenObj = $refreshToken->token;
            $refreshTokenObj->expires_at = now()->addDays(30);
            $refreshTokenObj->save();

            $data = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ?? 'user',
                ],
                'access_token' => $tokenResult->accessToken,
                'refresh_token' => $refreshToken->accessToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'expires_at' => $token->expires_at->toISOString(),
            ];

            DB::commit(); // REDIS MSH ERROR NANTI PINDAHIN KEBAWAH REDIS

            Redis::set("users:{$request->email}", json_encode($user->toArray()), 'EX', 60);
            event(new Registered($user));

            return $this->sendSuccessResponse(
                $data,
                201,
                "Register successfully",
                "",
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
                return $this->sendErrorResponse('User notfound', 404, null, null);
            }

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $tokenResult = $user->createToken('API Token');
            $token = $tokenResult->token;
            $token->expires_at = now()->addHours(1);
            $token->save();

            $refreshToken = $user->createToken('Refresh Token');
            $refreshTokenObj = $refreshToken->token;
            $refreshTokenObj->expires_at = now()->addDays(30);
            $refreshTokenObj->save();

            $data = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ?? 'user',
                ],
                'access_token' => $tokenResult->accessToken,
                'refresh_token' => $refreshToken->accessToken,
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'expires_at' => $token->expires_at->toISOString(),
            ];


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
            return $this->sendSuccessResponse(
                null,
                //            204, tidak akan mengembalikan content
                200,
                "Logout successfully",
                null
            );
        }catch (ValidationException $e) {
            return $this->sendExceptionResponse(null, 500, 'Logout fail', $e);
        }
    }

    public function refreshToken(Request $request)
    {
        $request->user()->tokens()->delete();
        $user = auth()->user();

        $data = [
            "user" => [
                "name" => $user->name,
                "email" => $user->email,
            ],
            "token" => $request->user()->createToken('API Token')->accessToken,
            "token_type" => "Bearer",
            "message" => "Refresh token successfully"
        ];

        return $this->sendSuccessResponse(
            $data,
            null,
            "Register successfully",
            "",
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
                    'role' => $user->role ?? 'user',
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
}
