<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Traits\ResponseAPI;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    use ResponseAPI;

    public function myTeams(Request $request): JsonResponse
    {
        $teams = $request->user()->userTeam()->with(['team'])->paginate(10);

        $data = $teams->through(function ($team) { // through adalah map untuk mengambil data dari pagination tanpa merusak metadatanya
            return [
                'id' => $team->team_id,
                'initial' => $team->team->initial ?? null,
                'name' => $team->team->name ?? null,
                'logo' => $team->team->logo ?? null,
                'role' => $team->role_id ?? null,
                'status' => $team->status_id,
            ];
        });

        return $this->sendSuccessPaginationResponse('My Teams', 200, 'success', null, $data);
    }

    public function myStats(Request $request): JsonResponse
    {
        $stats = $request->user()->stats()->with(['game.homeTeam', 'game.awayTeam'])->paginate(10);
        $userId = $request->user()->id;

        $data = $stats->through(function ($stat) use ($userId) {
            return [
                'game_id' => $stat->game_id,
                'game_name' => $stat->game->name,
                'game_description' => $stat->game->description,
                'court' => $stat->game->field->court,
                'home_team' => $stat->game->homeTeam ? [
                    'id' => $stat->game->homeTeam->id,
                    'name' => $stat->game->homeTeam->name,
                ] : null,
                'away_team' => $stat->game->awayTeam ? [
                    'id' => $stat->game->awayTeam->id,
                    'name' => $stat->game->awayTeam->name,
                ] : null,
                'my_team' => $stat->game->getUserTeam($userId),
                'created_at' => $stat->created_at,
                'updated_at' => $stat->updated_at,
            ];
        });

        return $this->sendSuccessPaginationResponse('My Stats', 200, 'success', null, $data);
    }

    public function myEducations(Request $request): JsonResponse {
        $data = $request->user()->educations()->with(['school'])->paginate(10)->through(function ($education) {
            return [
                'id' => $education->id,
                'school' => $education->school->name,
                'degree' => $education->degree,
                'grade' => $education->grade,
                'activities' => $education->activities,
                'start_date' => $education->start_date,
                'end_date' => $education->end_date,
            ];
        });

        return $this->sendSuccessPaginationResponse('My Educations', 200, 'success', null, $data);
    }
}
