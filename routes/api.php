<?php

use App\Http\Controllers\Api\EmailVerificationNotificationController;
use App\Http\Controllers\Api\VerifyEmailController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\AuthController;
use App\Http\Middleware\isPlayer;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware(['auth:api', isPlayer::class]);

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:api')->group(function () {
    Route::get("/user", function () {
        $user = \Illuminate\Support\Facades\Auth::user();
        return response()->json([
            "user" => [
                "name" => $user->name,
                "email" => $user->email,
            ]
        ]);
    })->middleware(isPlayer::class);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get("/posts", [PostController::class, 'index']);
    Route::get("/posts/detail", [PostController::class, 'show']);
});

Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['auth:api', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth:api', 'throttle:6,1'])
    ->name('verification.send');
