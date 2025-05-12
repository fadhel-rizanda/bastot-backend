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
                'name' => $team->team->name ?? null,
                'logo' => $team->team->logo ?? null,
//                'role'   => $team->role->name ?? null,
                'role' => $team->role_id ?? null,
                'status' => $team->status_id,
            ];
        });

        return $this->sendSucccessResponse('My Teams', 200, 'success', $data);
    }

    public function myStats(Request $request): JsonResponse{
        $data = $request->user()->stats()->with(['game.homeTeam', 'game.awayTeam'])->paginate(10);
        return $this->sendSucccessResponse('My Stats', 200, 'success', $data);
    }

}
