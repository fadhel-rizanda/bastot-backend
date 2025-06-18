<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\game\Game;
use App\Models\game\Stats;
use App\Models\game\Team;
use App\Models\game\UserTeam;
use App\Models\Role;
use App\Traits\ResponseAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    use ResponseAPI;

    public function roles(){
        return $this->sendSuccessResponse('Roles retrieved successfully', 200, 'success', Role::where('type', 'BASKETBALL')->get());
    }

    public function  gamesSummary()
    {
        $games = Game::paginate(10);
        if ($games->isEmpty()) {
            return $this->sendErrorResponse('No games found', 404, 'error', null);
        }
        $data = $games->map(function ($game) {
            return [
                'id' => $game->id,
                'name' => $game->name,
                'description' => $game->description,
                'game_time' => $game->game_time,
                'home_team_score' => $game->home_score,
                'away_team_score' => $game->away_score,
                'status' => $game->status ? [
                    'id' => $game->status_id,
                    'name' => $game->status->name,
                    'color' => $game->status->color,
                ] : null,
                'court' => $game->field ? [
                    'id' => $game->field->court->id,
                    'name' => $game->field->court->name,
                    'address' => $game->field->court->address,
                ] : null,
                'home_team' => $game->homeTeam ? [
                    'id' => $game->homeTeam->id,
                    'initial' => $game->homeTeam->initial,
                    'name' => $game->homeTeam->name,
                    'logo' => $game->homeTeam->logo,
                ] : null,
                'away_team' => $game->awayTeam ? [
                    'id' => $game->awayTeam->id,
                    'initial' => $game->awayTeam->initial,
                    'name' => $game->awayTeam->name,
                    'logo' => $game->awayTeam->logo,
                ] : null,
            ];
        });
        return $this->sendSuccessPaginationResponse('Games retrieved successfully', 200, 'success', $data, $games);
    }

    public function createGame(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'string',
            'field_id' => 'required|integer',
            'home_team_id' => 'required|integer',
            'away_team_id' => 'required|integer',
            'game_time' => 'required|date_format:Y-m-d H:i:s',
            'home_team_score' => 'integer|nullable',
            'away_team_score' => 'integer|nullable',
            'status_id' => 'required|in:SCHEDULED,ongoing,completed,cancelled',
        ]);

        DB::beginTransaction();
        try {
            $game = Game::create($fields);
            DB::commit();
            return $this->sendSuccessResponse('Game created successfully', 201, 'success', $game);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendExceptionResponse('Failed to create game', 500, 'error', $exception);
        }
    }

    public function getStats($gameId)
    {
        $game = Game::find($gameId);
        if (!$game) {
            return $this->sendErrorResponse('Game not found', 400, 'error', null);
        }

        $homeTeam = $game->homeTeam->userTeam()->with(['user.stats' => function ($query) use ($gameId) {
            $query->where('game_id', $gameId);
        }])->get()->map(function ($userTeam) {
            $user = $userTeam->user;

            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'team_role' => $userTeam->role_id,
                'team_status' => $userTeam->status_id,
                'stats' => optional($user->stats->first(), function ($stat) {
                    return [
                        'game_id' => $stat->game_id,
                        'minutes' => $stat->minutes,
                        'points' => $stat->points,
                        'rebounds' => $stat->rebounds,
                        'assists' => $stat->assists,
                        'steals' => $stat->steals,
                        'blocks' => $stat->blocks,
                        'turnovers' => $stat->turnovers,
                        '3pm' => $stat->{'3pm'},
                        '3pa' => $stat->{'3pa'},
                        '2pm' => $stat->{'2pm'},
                        '2pa' => $stat->{'2pa'},
                        'ftm' => $stat->ftm,
                        'fta' => $stat->fta,
                    ];
                }),
            ];
        });

        $awayTeam = $game->awayTeam->userTeam()->with(['user.stats' => function ($query) use ($gameId) {
            $query->where('game_id', $gameId);
        }])->get()->map(function ($userTeam) {
            $user = $userTeam->user;

            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'team_role' => $userTeam->role_id,
                'team_status' => $userTeam->status_id,
                'stats' => optional($user->stats->first(), function ($stat) {
                    return [
                        'game_id' => $stat->game_id,
                        'minutes' => $stat->minutes,
                        'points' => $stat->points,
                        'rebounds' => $stat->rebounds,
                        'assists' => $stat->assists,
                        'steals' => $stat->steals,
                        'blocks' => $stat->blocks,
                        'turnovers' => $stat->turnovers,
                        '3pm' => $stat->{'3pm'},
                        '3pa' => $stat->{'3pa'},
                        '2pm' => $stat->{'2pm'},
                        '2pa' => $stat->{'2pa'},
                        'ftm' => $stat->ftm,
                        'fta' => $stat->fta,
                    ];
                }),
            ];
        });

        $data = [
            'game_id' => $gameId,
            'home_team' =>
                $homeTeam
            ,
            'away_team' =>
                $awayTeam
        ];

        return $this->sendSuccessResponse('Stats retrieved successfully', 200, 'success', $data);

    }

    public function getUserStats($gameId, $userId)
    {
        $stats = Stats::with('user')
            ->where('game_id', $gameId)
            ->where('user_id', $userId)
            ->first();

        return $this->sendSuccessResponse('User stats retrieved successfully', 200, 'success', $stats);
    }
}
