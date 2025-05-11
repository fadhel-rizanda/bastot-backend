<?php

use App\Http\Controllers\Api\EmailVerificationNotificationController;
use App\Http\Controllers\Api\VerifyEmailController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\AuthController;
use App\Http\Middleware\isPlayer;
use \App\Http\Middleware\isTeamOwner;
use \App\Http\Controllers\User\PlayerController;

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

// player routes
Route::middleware(['auth:api', isPlayer::class])->group(function () {
    Route::get("/my-teams", [PlayerController::class, 'myTeams']);
    Route::post("/teams", [PlayerController::class, 'createTeam']);
    Route::post("/teams/{teamId}/join", [PlayerController::class, 'joinTeam']);
    Route::delete("/teams/{teamId}/leave", [PlayerController::class, 'leaveTeam']);
    Route::delete("/teams/{teamId}/kick/{userId}", [PlayerController::class, 'kickPlayer'])->middleware(isTeamOwner::class);
    Route::post("/teams/{teamId}/rejoin", [PlayerController::class, 'rejoin']);
    Route::post("/teams/{teamId}/activate/{userId}", [PlayerController::class, 'activatePlayer'])->middleware(isTeamOwner::class);
    Route::post("/teams/{teamId}/invite/{userId}", [PlayerController::class, 'invitePlayer'])->middleware(isTeamOwner::class);
    Route::post("/teams/{teamId}/accept-invite", [PlayerController::class, 'acceptInvite']);
    Route::get("/own-teams", [PlayerController::class, 'ownedTeams']);
    Route::get("/detail-teams/{teamId}", [PlayerController::class, 'detailTeam']);
});
