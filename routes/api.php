<?php

use App\Http\Controllers\Api\EmailVerificationNotificationController;
use App\Http\Controllers\Api\VerifyEmailController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\AuthController;
use App\Http\Middleware\isPlayer;
use \App\Http\Middleware\isTeamOwner;
use \App\Http\Middleware\isCourtOwner;
use \App\Http\Controllers\User\TeamController;
use \App\Http\Controllers\User\CourtOwnerController;
use \App\Http\Controllers\User\PlayerController;
use \App\Http\Controllers\User\StatsController;
use \App\Http\Controllers\User\GameController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware(['auth:api', isPlayer::class]);

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

Route::get('/', function () {
    return response()->json([
        "status" => "success",
        "message" => "Selamat datang di aplikasi bastot"
    ]);
});


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

    Route::get("/games/{gameId}/stats", [GameController::class, 'getStats']);
    Route::get("/games/{gameId}/stats/{userId}", [GameController::class, 'getUserStats']);
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
    Route::get("/my-stats", [PlayerController::class, 'myStats']);
    Route::get("/my-educations", [PlayerController::class, 'myEducations']);


    Route::post("/teams", [TeamController::class, 'createTeam']);
    Route::post("/teams/{teamId}/join", [TeamController::class, 'joinTeam']);
    Route::delete("/teams/{teamId}/leave", [TeamController::class, 'leaveTeam']);
    Route::delete("/teams/{teamId}/kick/{userId}", [TeamController::class, 'kickPlayer'])->middleware(isTeamOwner::class);
    Route::post("/teams/{teamId}/rejoin", [TeamController::class, 'rejoin']);
    Route::post("/teams/{teamId}/activate/{userId}", [TeamController::class, 'activatePlayer'])->middleware(isTeamOwner::class);
    Route::post("/teams/{teamId}/invite/{userId}", [TeamController::class, 'invitePlayer'])->middleware(isTeamOwner::class);
    Route::post("/teams/{teamId}/accept-invite", [TeamController::class, 'acceptInvite']);
    Route::get("/own-teams", [TeamController::class, 'ownedTeams']);
    Route::get("/detail-teams/{teamId}", [TeamController::class, 'detailTeam']);
    Route::get("/teams/{teamId}/players", [TeamController::class, 'getPlayers']); //
});

Route::middleware(['auth:api', isCourtOwner::class])->group(function () {
    Route::post("/games",[GameController::class, 'createGame']);
    Route::post("/courts",[CourtOwnerController::class, 'createCourt']);
    Route::post("/games/{gameId}/stats/{playerId}", [StatsController::class, 'createStats']);
    Route::put("/games/{gameId}/stats/{playerId}", [StatsController::class, 'updateStats']);
});
