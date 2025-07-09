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
use \App\Http\Controllers\User\AllController;
use \App\Http\Controllers\User\CommunityController;
use \App\Http\Controllers\GeneralController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware(['auth:api', isPlayer::class]);

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

Route::post('/ask-gemini', [PostController::class, 'askGemini']);

Route::get('/', function () {
    return response()->json([
        "status" => "success",
        "message" => "Selamat datang di aplikasi bastot"
    ]);
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
});

Route::middleware('auth:api')->group(callback: function () {
    Route::get("/user", function () {
        $user = \Illuminate\Support\Facades\Auth::user();
        return response()->json([
            "user" => [
                "name" => $user->name,
                "email" => $user->email,
            ]
        ]);
    })->middleware(isPlayer::class);
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::get('/roles', [AuthController::class, 'roles']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/delete', [AuthController::class, 'logout']);
    });

    Route::get("/posts", [PostController::class, 'index']);
    Route::get("/posts/detail", [PostController::class, 'show']);

    Route::prefix('all')->group(function () {
        Route::get("/users", [AllController::class, 'users']);
        Route::get('/roles', [AllController::class, 'roles']);
        Route::get('/status', [AllController::class, 'status']);
        Route::get('/tags', [AllController::class, 'tags']);
        Route::get('/teams', [AllController::class, 'teams']);
        Route::get('/courts', [AllController::class, 'courts']);
        Route::get('/fields', [AllController::class, 'fields']);
        Route::get('/schedules', [AllController::class, 'schedules']);
        Route::get('/notifications', [AllController::class, 'myNotifications']);
        Route::get('/communities', [AllController::class, 'getCommunities']);

        Route::post('/reviews', [AllController::class, 'createReview']);
    });

    Route::prefix('games')->group(function () {
        Route::get("/{gameId}/stats", [GameController::class, 'getStats']);
        Route::get("/{gameId}/details", [GameController::class, 'gameDetail']);
        Route::get("/{gameId}/stats/{userId}", [GameController::class, 'getUserStats']);
        Route::post("/{gameId}/stats/highlights", [GameController::class, 'createStats']);
    });

    Route::prefix('community')->group(function () {
        Route::post('/', [CommunityController::class, 'createCommunity']);
        Route::post('/event', [CommunityController::class, 'createEvent']);
        Route::post('/event/{eventId}/tournament', [CommunityController::class, 'createTournament']);
    });
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
    Route::get("/my-games", [PlayerController::class, 'myGames']);
    Route::get("/my-stats", [PlayerController::class, 'myStats']);
    Route::get("/my-educations", [PlayerController::class, 'myEducations']);


    Route::post("/teams", [TeamController::class, 'createTeam']);
    Route::post("/teams/{teamId}/join", [TeamController::class, 'joinTeam']);
    Route::delete("/teams/{teamId}/leave", [TeamController::class, 'leaveTeam']);
    Route::delete("/teams/{teamId}/kick/{userId}", [TeamController::class, 'kickPlayer'])->middleware(isTeamOwner::class);
    Route::post("/teams/{teamId}/rejoin", [TeamController::class, 'rejoin']);
    Route::post("/teams/{teamId}/activate/{userId}", [TeamController::class, 'activatePlayer'])->middleware(isTeamOwner::class);
    Route::post("/teams/{teamId}/invite/{userId}", [TeamController::class, 'invitePlayer'])->middleware(isTeamOwner::class);
    Route::post("/teams/invite", [TeamController::class, 'invitePlayer'])->middleware(isTeamOwner::class);
    Route::post("/teams/{teamId}/accept-invite", [TeamController::class, 'acceptInvite']);
    Route::get("/own-teams", [TeamController::class, 'ownedTeams']);
    Route::get("/detail-teams/{teamId}", [TeamController::class, 'detailTeam']);
    Route::get("/teams/{teamId}/players", [TeamController::class, 'getPlayers']); //
});

Route::middleware(['auth:api', isCourtOwner::class])->group(function () {
    Route::get("/games-summary",[GameController::class, 'gamesSummary']);
//    Route::post("/games",[GameController::class, 'createGame']);
    Route::post("/games",[GameController::class, 'createGameAndReservation']);
    Route::post("/games/{gameId}/play-by-play", [TeamController::class, 'createPlayByPlay']);
    Route::get("/games/{gameId}/play-by-play", [TeamController::class, 'getPlayByPlay']);
    Route::post("/courts",[CourtOwnerController::class, 'createCourt']);
    Route::post("/fields", [CourtOwnerController::class, 'createField']);
    Route::post("/schedule", [CourtOwnerController::class, 'createScheduleByList']);
    Route::post("/reservation", [CourtOwnerController::class, 'createReservations']);
    Route::post("/games/{gameId}/stats/{playerId}", [StatsController::class, 'createStat']);
    Route::post("/games/stats/", [StatsController::class, 'createStat']);
    Route::post("/games/stats/{statId}/highlights", [StatsController::class, 'createHighlights']);
    Route::put("/games/{gameId}/stats/{playerId}", [StatsController::class, 'updateStats']);
});

Route::get('/game/roles', [GameController::class, 'roles'])->middleware('auth:api');

Route::get('/test', [AuthController::class, 'test']);
Route::prefix('/test')->group(function () {
    Route::post('/', [\App\Http\Controllers\TestController::class, 'post']);
   Route::get('/token', [\App\Http\Controllers\DriveController::class, 'token']);
   Route::post('/store', [\App\Http\Controllers\DriveController::class, 'store']);
});

use Illuminate\Http\Request;

Route::post('/test-message', function (Request $request) {
    $message = $request->input('message');
    broadcast(new \App\Events\MessageSent($message));

    return response()->json(['status' => 'Message sent', 'message' => $message]);
});
