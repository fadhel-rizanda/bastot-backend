<?php

namespace App\Http\Controllers\User;

use App\Enums\Enums\Type;
use App\Http\Controllers\Controller;
use App\Models\game\Team;
use App\Models\Notification;
use App\Traits\ResponseAPI;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    use ResponseAPI;

    public function ownedTeams(Request $request): JsonResponse
    {
        $teams = Team::where('team_owner_id', $request->user()->id)->paginate(10);
        $data = $teams->through(function ($team) {
            return [
                'team_id' => $team->id,
                'initial' => $team->initial,
                'logo' => $team->logo,
                'name' => $team->name,
                'members' => $team->userTeam->count(),
                'created_at' => $team->created_at,
                'updated_at' => $team->updated_at,
            ];
        });

        return $this->sendSuccessPaginationResponse('Owned Teams', 200, 'success', null, $data);
    }

    public function detailTeam(Request $request, $teamId): JsonResponse
    {
        $team = Team::with(['userTeam' => function ($query) {
            $query->with(['user', 'role', 'status']);
        }])->find($teamId);

        if (!$team) {
            return $this->sendErrorResponse("Team not found", 404, 'error', []);
        }

        $data = [
            'team_id' => $team->id,
            'owner_id' => $team->team_owner_id,
            'initial' => $team->initial,
            'logo' => $team->logo,
            'name' => $team->name,
            'members' => $team->userTeam->count(),
            'created_at' => $team->created_at,
            'updated_at' => $team->updated_at,
            'members_list' => $team->userTeam->map(function ($member) {
                return [
                    'user_id' => $member->user->id,
                    'name' => $member->user->name,
                    'email' => $member->user->email,
                    'role' => $member->role_id,
                    'status' => $member->status_id,
                ];
            }),
        ];
        return $this->sendSuccessResponse('Team Detail', 200, 'success', $data);
    }

    public function createTeam(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $request->validate([
            'name' => 'required|string|max:255',
            'initial' => 'required|string|max:5|unique:teams,initial',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'role' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $team = Team::create([
                'name' => $request->name,
                'initial' => $request->initial,
                'logo' => $request->logo,
                'team_owner_id' => $userId,
            ]);

            $request->user()->userTeam()->create([
                'user_id' => $userId,
                'team_id' => $team->id,
                'role_id' => $request->role,
                'status_id' => 'ACTIVE',
            ]);

            Notification::create([
                'user_id' => $userId,
                'type' => Type::TEAM,
                'title' => 'Team Created',
                'message' => "You have successfully created a new team named '{$team->name}' with initial '{$team->initial}'.",
                'data' => [
                    'team_id' => $team->id,
                ]
            ]);

            DB::commit();
            return $this->sendSuccessResponse('Team created successfully', 201, 'success', $team);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendExceptionResponse('Failed to create team', 500, 'error', $exception);
        }
    }

    public function joinTeam(Request $request, $teamId): JsonResponse
    {
        $userId = $request->user()->id;
        $fields = $request->validate([
            'role' => 'required|string',
        ]);
        $team = Team::find($teamId);
        if ($team->isFull()) {
            return $this->sendErrorResponse("Team is full", 400, 'error', []);
        }

        DB::beginTransaction();
        try {
            $team->users()->attach($userId, [
                'role_id' => $fields['role'],
                'status_id' => 'ACTIVE',
            ]);

            Notification::create([
                'user_id' => $userId,
                'type' => Type::TEAM,
                'title' => 'Joined Team',
                'message' => "You have successfully joined the team '{$team->name}' as {$fields['role']}.",
                'data' => [
                    'user_id' => $userId,
                    'team_id' => $team->id,
                ]
            ]);

            DB::commit();
            return $this->sendSuccessResponse('Successfully joined the team', 200, 'success', $team);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendExceptionResponse('Failed to join team', 500, 'error', $exception);
        }
    }

    public function leaveTeam(Request $request, $teamId): JsonResponse
    {
        $userId = $request->user()->id;
        $team = Team::find($teamId);
        $userStatus = $team->userTeam->where('user_id', $userId)->first()?->status_id;

        if (!$team) {
            return $this->sendErrorResponse("Team not found", 404, 'error', []);
        }
        if ($team->team_owner_id == $userId) {
            return $this->sendErrorResponse("You cannot leave the team as the owner", 403, 'error', []);
        } else if ($userStatus != 'ACTIVE') {
            return $this->sendErrorResponse("Player already {$userStatus} from the team", 400, 'error', []);
        }

        DB::beginTransaction();
        try {
            $team->userTeam()->where('user_id', $userId)->update([
                'status_id' => 'INACTIVE',
            ]);

            Notification::create([
                'user_id' => $userId,
                'type' => Type::TEAM,
                'title' => 'Left Team',
                'message' => "You have left the team '{$team->name}'.",
                'data' => [
                    'user_id' => $userId,
                    'team_id' => $team->id,
                ]
            ]);

            Notification::create([
                'user_id' => $team->team_owner_id,
                'type' => Type::TEAM,
                'title' => 'Player Left Team',
                'message' => "User ID {$userId} has left your team '{$team->name}'.",
                'data' => [
                    'user_id' => $userId,
                    'team_id' => $team->id,
                ]
            ]);

            DB::commit();

            return $this->sendSuccessResponse('Successfully left the team', 200, 'success', $team);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendExceptionResponse('Failed to leave team', 500, 'error', $exception);
        }
    }

    public function rejoin(Request $request, $teamId): JsonResponse
    {
        $userId = $request->user()->id;
        $team = Team::find($teamId);
        $userStatus = $team->userTeam->where('user_id', $userId)->first()?->status_id;

        if (!$team) {
            return $this->sendErrorResponse("Team not found", 404, 'error', []);
        }
        if ($team->isFull()) {
            return $this->sendErrorResponse("Team is full", 400, 'error', []);
        }

        if ($userStatus == null) {
            return $this->sendErrorResponse("Player not found in the team please join the team", 404, 'error', []);
        } else if ($userStatus = 'ACTIVE') {
            return $this->sendErrorResponse("Player already {$userStatus} from the team", 400, 'error', []);
        } else if ($userStatus == 'DEACTIVATED' || $userStatus == 'BANNED') {
            return $this->sendErrorResponse("Please contact the team owner to rejoin", 400, 'error', []);
        }

        DB::beginTransaction();
        try {
            $team->userTeam()->where('user_id', $userId)->update([
                'status_id' => 'ACTIVE',
            ]);

            Notification::create([
                'user_id' => $userId,
                'type' => Type::TEAM,
                'title' => 'Rejoined Team',
                'message' => "You have successfully rejoined the team '{$team->name}'.",
                'data' => [
                    'user_id' => $userId,
                    'team_id' => $team->id,
                ]
            ]);

            Notification::create([
                'user_id' => $team->team_owner_id,
                'type' => Type::TEAM,
                'title' => 'Player Rejoined',
                'message' => "User ID {$userId} has rejoined your team '{$team->name}'.",
                'data' => [
                    'user_id' => $userId,
                    'team_id' => $team->id,
                ]
            ]);

            DB::commit();
            return $this->sendSuccessResponse('Successfully rejoined the team', 200, 'success', $team);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendExceptionResponse('Failed to rejoin team', 500, 'error', $exception);
        }

    }

    public function activatePlayer(Request $request, $teamId, $userId): JsonResponse
    {
        $team = Team::find($teamId);
        $userStatus = $team->userTeam->where('user_id', $userId)->first()?->status_id;

        if (!$team) {
            return $this->sendErrorResponse("Team not found", 404, 'error', []);
        }

//        if ($team->team_owner_id != $request->user()->id) {
//            return response()->json([
//                'message' => 'You are not authorized to activate this player',
//            ], 403);
//        } else

        if ($userStatus == null) {
            return $this->sendErrorResponse("Player not found in the team", 404, 'error', []);
        } else if ($userStatus != 'DEACTIVATED' || $userStatus != 'BANNED') {
            return $this->sendErrorResponse("Player already {$userStatus} from the team", 400, 'error', []);
        }

        DB::beginTransaction();
        try {
            $team->userTeam()->where('user_id', $userId)->update([
                'status_id' => 'ACTIVE',
            ]);

            Notification::create([
                'user_id' => $userId,
                'type' => Type::GAME,
                'title' => 'Team Activation',
                'message' => "You have been reactivated in team {$team->name}.",
                'data' => [
                    'user_id' => $userId,
                    'team_id' => $team->id,
                ]
            ]);

            Notification::create([
                'user_id' => $request->user()->id,
                'type' => Type::GAME,
                'title' => 'Team Activation Log',
                'message' => "You reactivated user ID {$userId} in team {$team->name}.",
                'data' => [
                    'user_id' => $userId,
                    'team_id' => $team->id,
                ]
            ]);

            DB::commit();
            return $this->sendSuccessResponse('Successfully activated the player in the team', 200, 'success', $team);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendExceptionResponse('Failed to activate player in team', 500, 'error', $exception);
        }
    }

    public function kickPlayer(Request $request, $teamId, $userId): JsonResponse
    {
        $team = Team::find($teamId);
        $userStatus = $team->userTeam->where('user_id', $userId)->first()?->status_id;

        if (!$team) {
            return $this->sendErrorResponse("Team not found", 404, 'error', []);
        }

//        if ($team->team_owner_id != $request->user()->id) {
//            return response()->json([
//                'message' => 'You are not authorized to kick this player',
//            ], 403);
//        } else

        if ($team->team_owner_id == $userId) {
            return $this->sendErrorResponse("You cannot leave the team as the owner", 403, 'error', []);
        } else if ($userStatus == null) {
            return $this->sendErrorResponse("Player not found in the team", 404, 'error', []);
        } else if ($userStatus != 'ACTIVE') {
            return $this->sendErrorResponse("Player already {$userStatus} from the team", 400, 'error', []);
        }

        DB::beginTransaction();
        try {
            $team->userTeam()->where('user_id', $userId)->update([
                'status_id' => 'DEACTIVATED',
            ]);

            Notification::create([
                'user_id' => $userId,
                'type' => Type::TEAM,
                'title' => 'Removed from Team',
                'message' => "You have been removed from the team '{$team->name}' by the team owner.",
                'data' => [
                    'user_id' => $userId,
                    'team_id' => $team->id,
                ]
            ]);

            Notification::create([
                'user_id' => $team->team_owner_id,
                'type' => Type::TEAM,
                'title' => 'Player Removed',
                'message' => "You have removed a player (User ID: {$userId}) from the team '{$team->name}'.",
                'data' => [
                    'user_id' => $userId,
                    'team_id' => $team->id,
                ]
            ]);

            DB::commit();
            return $this->sendSuccessResponse('Successfully kicked the player from the team', 200, 'success', $team);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendExceptionResponse('Failed to kick player from team', 500, 'error', $exception);
        }
    }

//    public function invitePlayer(Request $request, $teamId, $userId): JsonResponse
//    {
//        $fields = $request->validate([
//            'role' => 'required|string',
//        ]);
//        $ownerId = $request->user()->id;
//        $team = Team::find($teamId);
//
////        if ($team->team_owner_id != $ownerId) {
////            return response()->json([
////                'message' => 'You cannot invite player to this team',
////            ], 403);
////        }
////        dd($team->userTeam()->exists('user_id', $userId));
//        if ($team->userTeam()->where('user_id', $userId)->exists()) {
//            return $this->sendErrorResponse("Player already in the team", 400, 'error', []);
//
//        }
//        if ($team->isFull()) {
//            return $this->sendErrorResponse("Team is full", 400, 'error', []);
//        }
//
//        DB::beginTransaction();
//        try {
//            $team->users()->attach($userId, [
//                'role_id' => $fields['role'],
//                'status_id' => 'INVITED',
//            ]);
//            DB::commit();
//            $data = [
//                'message' => 'Successfully invited player to the team',
//                'team' => $team,
//            ];
//            return $this->sendSuccessResponse('Successfully invited player to the team', 200, 'success', $data);
//        } catch (\Exception $exception) {
//            DB::rollBack();
//            return $this->sendExceptionResponse('Failed to invite player to team', 500, 'error', $exception);
//        }
//    }
//
    public function invitePlayer(Request $request): JsonResponse
    {
        $fields = $request->validate([
            'role_id' => 'required|string',
        ]);
        $team = Team::find($request->team_id);

        $userTeam = $team->userTeam()->where('user_id', $request->user_id)->first();
        if ($userTeam) {
            return $this->sendErrorResponse("Player already {$userTeam->status_id} to the team", 400, 'error', []);
        }
        if ($team->isFull()) {
            return $this->sendErrorResponse("Team is full", 400, 'error', []);
        }

        DB::beginTransaction();
        try {
            $team->users()->attach($request->user_id, [
                'role_id' => $fields['role_id'],
                'status_id' => 'INVITED',
            ]);

            Notification::create([
                'user_id' => $request->user()->id,
                'type' => Type::GAME,
                'title' => 'Team Invitation',
                'message' => "You have sent an invitation to user ID {$request->user_id} to join team ID {$request->team_id} as {$request->role_id}.",
                'data' => [
                    'user_id' => $request->user_id,
                    'team_id' => $team->id,
                ]
            ]);

            Notification::create([
                'user_id' => $request->user_id,
                'type' => Type::GAME,
                'title' => 'Team Invitation',
                'message' => "You have been invited by user ID {$request->user()->id} to join team ID {$request->team_id} as {$request->role_id}.",
                'data' => [
                    'user_id' => $request->user_id,
                    'team_id' => $team->id,
                ]
            ]);

            DB::commit();
            return $this->sendSuccessResponse('Successfully invited player to the team', 200, 'success', $team);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendExceptionResponse('Failed to invite player to team', 500, 'error', $exception);
        }
    }

    public function acceptInvite(Request $request, $teamId): JsonResponse
    {
        $userId = $request->user()->id;
        $team = Team::find($teamId);
        $userStatus = $team->userTeam->where('user_id', $userId)->first()?->status_id;

        if (!$team) {
            return $this->sendErrorResponse("Team not found", 404, 'error', []);
        }
        if ($team->isFull()) {
            return $this->sendErrorResponse("Team is full", 400, 'error', []);
        }

        if ($userStatus == null) {
            return $this->sendErrorResponse("Player not found in the team please join the team", 404, 'error', []);
        } else if ($userStatus != 'INVITED') {
            return $this->sendErrorResponse("Player already {$userStatus} from the team", 400, 'error', []);
        }

        DB::beginTransaction();
        try {
            $team->userTeam()->where('user_id', $userId)->update([
                'status_id' => 'ACTIVE',
            ]);

            Notification::create([
                'user_id' => $userId,
                'type' => Type::TEAM,
                'title' => 'Invitation Accepted',
                'message' => "You have successfully accepted the invitation to join the team '{$team->name}'.",
                'data' => [
                    'user_id' => $userId,
                    'team_id' => $team->id,
                ]
            ]);

            Notification::create([
                'user_id' => $team->team_owner_id,
                'type' => Type::TEAM,
                'title' => 'Player Joined',
                'message' => "User ID {$userId} has accepted your invitation and joined the team '{$team->name}'.",
                'data' => [
                    'user_id' => $userId,
                    'team_id' => $team->id,
                ]
            ]);

            DB::commit();
            return $this->sendSuccessResponse('Successfully accepted the invite to the team', 200, 'success', $team);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendExceptionResponse('Failed to accept invite to team', 500, 'error', $exception);
        }
    }

    public function getPlayers(Request $request, $teamId): JsonResponse
    {
        $team = Team::with('userTeam.user')->find($teamId);
        if (!$team) {
            return $this->sendErrorResponse("Team not found", 404, 'error', []);
        }
        $players = $team->userTeam;

        $data = [
            'team_id' => $teamId,
            'players' => $players->map(function ($player) {
                return [
                    'user_id' => $player->user->id,
                    'name' => $player->user->name,
                    'email' => $player->user->email,
                    'role' => $player->role_id,
                    'status' => $player->status_id,
                ];
            }),
        ];
        return $this->sendSuccessResponse('Team Players', 200, 'success', $data);
    }
}
