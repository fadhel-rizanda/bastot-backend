<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\game\Game;
use App\Models\game\Stats;
use App\Traits\ResponseAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    use ResponseAPI;
    public function createGame(Request $request){
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'string',
            'court_id' => 'required|integer',
            'home_team_id' => 'required|integer',
            'away_team_id' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            $game = Game::create($fields);
            DB::commit();
            return $this->sendSucccessResponse('Game created successfully', 201, 'success', $game);
        }catch (\Exception $exception){
            DB::rollBack();
            return $this->sendExceptionResponse('Failed to create game', 500, 'error', $exception);
        }
    }
}
