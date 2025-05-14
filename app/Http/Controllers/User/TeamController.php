<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\game\Team;
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
                'logo' => $team->logo,
                'name' => $team->name,
                'members' => $team->userTeam->count(),
                'created_at' => $team->created_at,
                'updated_at' => $team->updated_at,
            ];
        });

        return $this->sendSucccessPaginationResponse('Owned Teams', 200, 'success', $data);
    }

    public function detailTeam(Request $request, $teamId): JsonResponse{
        $team = Team::find($teamId);
        if (!$team) {
            return $this->sendErrorResponse("Team not found", 404, 'error', []);
        }
        $team = $team->with(['userTeam' => function ($query) {
            $query->with(['user', 'role', 'status']);
        }])->first();
        $data = [
            'team_id' => $team->id,
            'owner_id' => $team->team_owner_id,
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
        return $this->sendSucccessResponse('Team Detail', 200, 'success', $data);
    }

    public function createTeam(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'role' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $team = Team::create([
                'name' => $request->name,
                'logo' => $request->logo,
                'team_owner_id' => $userId,
            ]);

            $request->user()->userTeam()->create([
                'user_id' => $userId,
                'team_id' => $team->id,
                'role_id' => $request->role,
                'status_id' => 'ACTIVE',
            ]);

            DB::commit();
            return $this->sendSucccessResponse('Team created successfully', 201, 'success', $team);
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

            DB::commit();
            return $this->sendSucccessResponse('Successfully joined the team', 200, 'success', $team);
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

            DB::commit();

            return $this->sendSucccessResponse('Successfully left the team', 200, 'success', $team);
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

            DB::commit();
            return $this->sendSucccessResponse('Successfully rejoined the team', 200, 'success', $team);
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

            DB::commit();
            return $this->sendSucccessResponse('Successfully activated the player in the team', 200, 'success', $team);
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

            DB::commit();
            return $this->sendSucccessResponse('Successfully kicked the player from the team', 200, 'success', $team);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendExceptionResponse('Failed to kick player from team', 500, 'error', $exception);
        }
    }

    public function invitePlayer(Request $request, $teamId, $userId): JsonResponse
    {
        $fields = $request->validate([
            'role' => 'required|string',
        ]);
        $ownerId = $request->user()->id;
        $team = Team::find($teamId);

//        if ($team->team_owner_id != $ownerId) {
//            return response()->json([
//                'message' => 'You cannot invite player to this team',
//            ], 403);
//        }
        if ($team->userTeam()->exists('user_id', $userId)) {
            return $this->sendErrorResponse("Player already in the team", 400, 'error', []);

        }
        if ($team->isFull()) {
            return $this->sendErrorResponse("Team is full", 400, 'error', []);
        }

        DB::beginTransaction();
        try {
            $team->users()->attach($userId, [
                'role_id' => $fields['role'],
                'status_id' => 'INVITED',
            ]);
            DB::commit();
            $data = [
                'message' => 'Successfully invited player to the team',
                'team' => $team,
            ];
            return $this->sendSucccessResponse('Successfully invited player to the team', 200, 'success', $data);
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

            DB::commit();
            return $this->sendSucccessResponse('Successfully accepted the invite to the team', 200, 'success', $team);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendExceptionResponse('Failed to accept invite to team', 500, 'error', $exception);
        }
    }
}
