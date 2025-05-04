<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ResponseAPI;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
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

            $user = User::create([
                'name' => $fields["name"],
                'email' => $fields["email"],
                'password' => bcrypt($fields["password"]),
                'role_id' => $fields["role"],
            ]);


            $token = $user->createToken('auth_token')->accessToken;

            DB::commit();

            Redis::set("users:{$request->email}", json_encode($user->toArray()), 'EX', 60);
            event(new Registered($user));

            $data = [
                "user" => [
                    "name" => $fields["name"],
                    "email" => $fields["email"],
                    "role" => $fields["role"],
                ],
                "token" => $token,
                "token_type" => "Bearer",
            ];
            return $this->sendSucccessResponse(
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
            $fields = $request->validate([
                "email" => "required|string|email",
                "password" => "required|string",
            ]);

            if (!Auth::attempt($fields)) {
                return response()->json([
                    "status" => "error",
                    "message" => "Username or password incorrect"
                ], 401);
            }

            $user = User::where('email', $request->email)->firstOrFail();

            //            $user = Redis::get("users:{$request->email}") || Redis::set("users:{$request->email}", 60, function() use ($request) {
            //                    Log::info("Caching user data for key");
            //                    return User::where('email', $request->email)->firstOrFail();
            //                });

            //            $user = Redis::remember("users:{$request->email}", 60, function() use ($request) {
            //                Log::info("Caching user data for key");
            //                return User::where('email', $request->email)->firstOrFail();
            //            });

            $token = $user->createToken('auth_token')->accessToken;

            $data = [
                "user" => [
                    "name" => $user->name,
                    "email" => $user->email,
                ],
                "token" => $token,
                "token_type" => "Bearer",
            ];

            return $this->sendSucccessResponse(
                $data,
                null,
                "Login successfully",
                "",
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
        // For Sanctum, this is the correct way to revoke a token
        $request->user()->token()->revoke();
        return $this->sendSucccessResponse(
            null,
            //            204, tidak akan mengembalikan content
            200,
            "Logout successfully",
            null
        );
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
            "token" => $request->user()->createToken('auth_token')->accessToken,
            "token_type" => "Bearer",
            "message" => "Refresh token successfully"
        ];

        return $this->sendSucccessResponse(
            $data,
            null,
            "Register successfully",
            "",
        );
    }
}
