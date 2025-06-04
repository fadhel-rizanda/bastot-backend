<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\game\Game;
use App\Models\game\Stats;
use App\Traits\ResponseAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    use ResponseAPI;

    public function createStats(Request $request, $gameId, $playerId)
    {
        $fields = $request->validate([
            'minutes' => 'required|integer',
            'points' => 'required|integer',
            'rebounds' => 'required|integer',
            'assists' => 'required|integer',
            'steals' => 'required|integer',
            'blocks' => 'required|integer',
            'turnovers' => 'required|integer',
            '3pm' => 'required|integer',
            '3pa' => 'required|integer',
            '2pm' => 'required|integer',
            '2pa' => 'required|integer',
            'ftm' => 'required|integer',
            'fta' => 'required|integer',
            'notes' => 'nullable|string'
        ]);

        $fields = array_merge($fields, [
            'game_id' => $gameId,
            'user_id' => $playerId,
        ]);

        DB::beginTransaction();
        try {
            $stats = Stats::create($fields);
            DB::commit();
            return $this->sendSuccessResponse('Stats created successfully', 201, 'success', $stats);
        }catch (\Exception $exception){
        DB::rollBack();
            return $this->sendExceptionResponse('Failed to create stats', 500, 'error', $exception);
        }
    }

    public function updateStats(Request $request, $gameId, $playerId)
    {
        $fields = $request->validate([
            'minutes' => 'required|integer',
            'points' => 'required|integer',
            'rebounds' => 'required|integer',
            'assists' => 'required|integer',
            'steals' => 'required|integer',
            'blocks' => 'required|integer',
            'turnovers' => 'required|integer',
            '3pm' => 'required|integer',
            '3pa' => 'required|integer',
            '2pm' => 'required|integer',
            '2pa' => 'required|integer',
            'ftm' => 'required|integer',
            'fta' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            $stats = Stats::where('game_id', $gameId)->where('user_id', $playerId)->first();
            if (!$stats) {
                return $this->sendErrorResponse('Stats not found', 404, 'error', []);
            }

            $stats->update($fields);

            DB::commit();
            return $this->sendSuccessResponse('Stats updated successfully', 200, 'success', $stats);
        }catch (\Exception $exception){
        DB::rollBack();
            return $this->sendExceptionResponse('Failed to update stats', 500, 'error', $exception);
        }
    }
}
