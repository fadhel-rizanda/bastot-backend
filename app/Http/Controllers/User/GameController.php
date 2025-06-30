<?php

namespace App\Http\Controllers\User;

use App\Enums\Enums\Type;
use App\Http\Controllers\Controller;
use App\Models\court\Reservation;
use App\Models\court\Schedule;
use App\Models\game\Game;
use App\Models\game\Stats;
use App\Models\game\Team;
use App\Models\game\UserTeam;
use App\Models\Notification;
use App\Models\Role;
use App\Traits\ResponseAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GameController extends Controller
{
    use ResponseAPI;

    public function roles()
    {
        return $this->sendSuccessResponse('Roles retrieved successfully', 200, 'success', Role::where('type', 'BASKETBALL')->get());
    }

    public function gamesSummary()
    {
        $games = Game::orderBy('start_time')->paginate(10);
        if ($games->isEmpty()) {
            return $this->sendErrorResponse('No games found', 404, 'error', null);
        }
        $data = $games->map(function ($game) {
            return [
                'id' => $game->id,
                'name' => $game->name,
                'description' => $game->description,
                'start_time' => $game->start_time,
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

//    Create Game v1
    /*   public function createGame(Request $request)
       {
           $fields = $request->validate([
               'name' => 'required|string|max:255',
               'description' => 'string',
               'field_id' => 'required|integer',
               'home_team_id' => 'required|integer',
               'away_team_id' => 'required|integer',
               'start_time' => 'required|date_format:Y-m-d H:i:s',
               'end_time' => 'required|date_format:Y-m-d H:i:s',
               'home_team_score' => 'integer|nullable',
               'away_team_score' => 'integer|nullable',
               'status_id' => 'required|in:SCHEDULED,ongoing,completed,cancelled',
           ]);

           DB::beginTransaction();
           try {
               $game = Game::create($fields);

               Notification::create([
                   'user_id' => $request->user()->id,
                   'type' => Type::GAME,
                   'title' => 'Game Created',
                   'message' => "Game '{$fields['name']}' between Team ID {$fields['home_team_id']} and Team ID {$fields['away_team_id']} has been scheduled.",
                   'data' => [
                       'game_id' => $game->id,
                   ]
               ]);

               Notification::create([
                   'user_id' => Team::find($fields['home_team_id'])->team_owner_id,
                   'type' => Type::GAME,
                   'title' => 'Home Game Scheduled',
                   'message' => "Your team is scheduled to play against Team ID {$fields['away_team_id']} at {$fields['start_time']}.",
                   'data' => [
                       'game_id' => $game->id,
                   ]
               ]);

               Notification::create([
                   'user_id' => Team::find($fields['away_team_id'])->team_owner_id,
                   'type' => Type::GAME,
                   'title' => 'Away Game Scheduled',
                   'message' => "Your team is scheduled to play against Team ID {$fields['home_team_id']} at {$fields['start_time']}.",
                   'data' => [
                       'game_id' => $game->id,
                   ]
               ]);

               DB::commit();
               return $this->sendSuccessResponse('Game created successfully', 201, 'success', $game);
           } catch (\Exception $exception) {
               DB::rollBack();
               return $this->sendExceptionResponse('Failed to create game', 500, 'error', $exception);
           }
       }*/

    public function createGameAndReservation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'field_id' => 'required|integer',
            'home_team_id' => 'required|integer',
            'away_team_id' => 'required|integer',
//            'reservations' => 'required|array|min:1',
//            'reservations.*.schedule_id' => 'required|numeric',
            'reservations' => 'required|array',
            'reservations.*' => 'required|integer|exists:schedules,id',
        ]);

        if ($validator->fails()) {
            return $this->sendErrorResponse($validator->errors(), 422, 'Validation failed', null);
        }

        $user = $request->user();
        $reservations = [];
        $totalCost = 0;

        DB::beginTransaction();
        try {
//            $schedules = Schedule::whereIn('id', collect($request->reservations)->pluck('schedule_id'))
            $schedules = Schedule::whereIn('id', $request->reservations)
                ->where('is_available', true)
                ->orderBy('start_time')
                ->get();
            if ($schedules->count() !== count($request->reservations)) {
                DB::rollBack();
                return $this->sendErrorResponse("Some schedules are unavailable", 409, null, null);
            }

            $homeTeamUserIds = UserTeam::where('team_id', $request->home_team_id)->pluck('user_id')->toArray();
            $awayTeamUserIds = UserTeam::where('team_id', $request->away_team_id)->pluck('user_id')->toArray();

            $duplicateUserIds = array_intersect($homeTeamUserIds, $awayTeamUserIds);

            if (!empty($duplicateUserIds)) {
                return $this->sendErrorResponse("A user cannot be in both home and away team.", 422, null, null);
            }

            $grouped = [];
            $currentGroup = [];
            $lastEndTime = null;

            foreach ($schedules as $schedule) {
                $gap = $lastEndTime ? abs(Carbon::parse($schedule->start_time)->diffInMinutes($lastEndTime)) : 0;
                Log::info("gap: " . $gap);
                if (!$lastEndTime || $gap <= 30) {
                    $currentGroup[] = $schedule;
                } else {
                    $grouped[] = $currentGroup;
                    $currentGroup = [$schedule];
                }
                $lastEndTime = Carbon::parse($schedule->end_time);
            }
            if (count($currentGroup) > 0) $grouped[] = $currentGroup;

            foreach ($grouped as $index => $group) {
                $first = $group[0];
                $last = end($group);

                $game = Game::create([
                    'name' => $request->name . ' #' . ($index + 1),
                    'description' => $request->description,
                    'field_id' => $request->field_id,
                    'home_team_id' => $request->home_team_id,
                    'away_team_id' => $request->away_team_id,
                    'start_time' => $first->start_time,
                    'end_time' => $last->end_time,
                    'status_id' => 'SCHEDULED',
                ]);

                Notification::create([
                    'user_id' => $request->user()->id,
                    'type' => Type::GAME,
                    'title' => 'Game Created',
                    'message' => "Game '{$game->name}' between Team ID {$game->home_team_id} and Team ID {$game->away_team_id} has been scheduled.",
                    'data' => [
                        'game_id' => $game->id,
                    ]
                ]);

                Notification::create([
                    'user_id' => Team::find($game->home_team_id)->team_owner_id,
                    'type' => Type::GAME,
                    'title' => 'Home Game Scheduled',
                    'message' => "Your team is scheduled to play against Team ID {$game->away_team_id} at {$game->start_time}.",
                    'data' => [
                        'game_id' => $game->id,
                    ]
                ]);

                Notification::create([
                    'user_id' => Team::find($game->away_team_id)->team_owner_id,
                    'type' => Type::GAME,
                    'title' => 'Away Game Scheduled',
                    'message' => "Your team is scheduled to play against Team ID {$game->home_team_id} at {$game->start_time}.",
                    'data' => [
                        'game_id' => $game->id,
                    ]
                ]);

                foreach ($group as $sched) {
                    $sched->update(['is_available' => false]);

                    $reservation = Reservation::create([
                        'schedule_id' => $sched->id,
                        'user_id' => $user->id,
                        'status_id' => 'SCHEDULED',
                        'game_id' => $game->id,
                    ]);

                    $fieldName = optional($sched->field)->name ?? 'Unknown Field';
                    $formattedTime = Carbon::parse($sched->start_time)->format('d M Y H:i');

                    Notification::create([
                        'user_id' => Team::find($game->away_team_id)?->team_owner_id,
                        'type' => Type::GAME,
                        'title' => 'Game Reservation Scheduled',
                        'message' => "Your team is scheduled to play against team ID {$game->home_team_id} on {$formattedTime} at {$fieldName}.",
                        'data' => [
                            'game_id' => $game->id,
                            'schedule_id' => $sched->id,
                        ],
                    ]);

                    $reservations[] = $reservation;
                    $totalCost += $sched->price_per_hour ?? 0;
                }
            }

            DB::commit();

            return $this->sendSuccessResponse("Games and reservations created", 201, null, [
                'reservations' => $reservations,
                'total_cost' => $totalCost,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendExceptionResponse("Failed to create games", 500, null, $e);
        }
    }

    public function gameDetail($gameId)
    {
        $game = Game::find($gameId);
        if (!$game) {
            return $this->sendErrorResponse('Game not found', 400, 'error', null);
        }

        $playByPlays = $game->playByPlays()->with('user')->get();

        $playByPlays = $playByPlays->map(function ($play) {
            return [
                'id' => $play->id,
                'team_id' => $play->team_id,
                'status_id' => $play->status_id,
                'quarter' => $play->quarter,
                'time_seconds' => $play->time_seconds,
                'home_score' => $play->home_score,
                'away_score' => $play->away_score,
                'title' => $play->title,
                'description' => $play->description,
                'created_at' => $play->created_at,
                'updated_at' => $play->updated_at,
                'user' => [
                    'id' => $play->user->id,
                    'name' => $play->user->name,
                    'profile_picture' => $play->user->profile_picture,
                ],
            ];
        });


        $homeTeam = $game->homeTeam->userTeam()->with(['user.stats' => function ($query) use ($gameId) {
            $query->where('game_id', $gameId);
        }])->get()->map(function ($userTeam) {
            $user = $userTeam->user;

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $userTeam->role_id,
                'status' => $userTeam->status_id,
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
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $userTeam->role_id,
                'status' => $userTeam->status_id,
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

        $gameDetail = [
            'id' => $game->id,
            'name' => $game->name,
            'description' => $game->description,
            'court' => $game->field ? [
                'id' => $game->field->court->id,
                'name' => $game->field->court->name,
                'address' => $game->field->court->address,
                'field' => [
                    'id' => $game->field->id,
                    'name' => $game->field->name,
                ],
            ] : null,
            'home_score' => $game->home_score,
            'away_score' => $game->away_score,
            'start_time' => $game->start_time,
            'end_time' => $game->end_time,
            'status_id' => $game->status_id,
            'created_at' => $game->created_at,
            'updated_at' => $game->updated_at,
        ];

        $data = [
            'game' => $gameDetail,
            'play_by_play' => $playByPlays,
            'home_team' => [
                'id' => $game->home_team_id,
                'name' => $game->homeTeam->name,
                'initial' => $game->homeTeam->initial,
                'logo' => $game->homeTeam->logo,
                'team_owner_id' => $game->homeTeam->team_owner_id,
                'created_at' => $game->homeTeam->created_at,
                'players' => $homeTeam
            ],
            'away_team' => [
                'id' => $game->away_team_id,
                'name' => $game->awayTeam->name,
                'initial' => $game->awayTeam->initial,
                'logo' => $game->awayTeam->logo,
                'team_owner_id' => $game->awayTeam->team_owner_id,
                'created_at' => $game->awayTeam->created_at,
                'players' => $awayTeam
            ],
        ];

        return $this->sendSuccessResponse('Stats retrieved successfully', 200, 'success', $data);
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
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $userTeam->role_id,
                'status' => $userTeam->status_id,
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
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $userTeam->role_id,
                'status' => $userTeam->status_id,
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
