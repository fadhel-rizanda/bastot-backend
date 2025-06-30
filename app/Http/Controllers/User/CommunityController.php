<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\community\Community;
use App\Models\community\Event;
use App\Models\community\Tournament;
use App\Models\community\UserCommunity;
use App\Models\court\Court;
use App\Models\court\Field;
use App\Models\Location;
use App\Models\Review;
use App\Traits\ResponseAPI;
use Google\Service\Dfareporting\EventTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class CommunityController extends Controller
{
    use ResponseAPI;

    public function createCommunity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_court' => 'required|integer',
            'users' => 'required|array',
            'users.*.user_id' => 'required|integer',
            'users.*.role_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendErrorResponse($validator->errors(), 422, 'Validation failed', null);
        }

        DB::beginTransaction();
        try {
            $community = Community::create([
                'name' => $request->name,
                'description' => $request->description,
                'base_court' => $request->base_court
            ]);

            $userCommunities = collect();
            foreach ($request->users as $user) {
                $userCommunity = UserCommunity::create([
                    'user_id' => $user['user_id'],
                    'community_id' => $community->id,
                    'role_id' => $user['role_id'],
                    'status_id' => 'ACTIVE'
                ]);
                $userCommunities->add($userCommunity);
            }

            $data = [
                'community' => $community,
                'user_communities' => $userCommunities,
            ];

            DB::commit();
            return $this->sendSuccessResponse("Community created", 201, null, $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendExceptionResponse("Failed to create community", 500, null, $e);
        }
    }

    public function createEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'location_id' => 'nullable|integer',
            'community_id' => 'required|integer',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'tags' => 'nullable|array',
            'tags.*' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->sendErrorResponse($validator->errors(), 422, 'Validation failed', null);
        }

        $uriResponse = Http::withHeaders([
            'Accept' => 'application/json',
            'User-Agent' => 'MyAppName/1.0 (myemail@example.com)'
        ])->get('https://nominatim.openstreetmap.org/reverse', [
            'format' => 'jsonv2',
            'lat' => $request->latitude,
            'lon' => $request->longitude,
            'addressdetails' => 1,
        ])->json();

        DB::beginTransaction();
        try {
            $locationId = null;
            if (!$request->filled('location_id')) {
                $location = Location::firstOrCreate(
                    ['id' => $uriResponse['place_id']],
                    [
                        'name' => $uriResponse['display_name'],
                        'residential' => $uriResponse['address']['residential'] ?? null,
                        'road' => $uriResponse['address']['road'] ?? null,
                        'city_block' => $uriResponse['address']['city_block'] ?? null,
                        'suburb' => $uriResponse['address']['suburb'] ?? null,
                        'city_district' => $uriResponse['address']['city_district'] ?? null,
                        'village' => $uriResponse['address']['village'] ?? null,
                        'city' => $uriResponse['address']['city'] ?? null,
                        'state' => $uriResponse['address']['state'] ?? null,
                        'region' => $uriResponse['address']['region'] ?? null,
                        'country' => $uriResponse['address']['country'] ?? null,
                        'country_code' => $uriResponse['address']['country_code'] ?? null,
                        'postcode' => $uriResponse['address']['postcode'] ?? null,
                    ]
                );


                $locationId = $location->id;
            }

            $event = Event::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'location_id' => $locationId,
                'community_id' => $request->community_id,
                'status_id' => 'SCHEDULED',
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            $tags = collect();

            foreach ($request->tags as $tag) {
                $eventTag = $event->tags->attach($tag);
                $tags->push($eventTag);
            }

            $data = [
                'event' => $event,
                'tags' => $tags
            ];

            DB::commit();
            return $this->sendSuccessResponse("Community created", 201, null, $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendExceptionResponse("Failed to create community", 500, null, $e);
        }
    }

    public function createTournament(Request $request, $eventId)
    {
        $validator = Validator::make($request->all(), [
            'court_id' => 'required|integer',
            'tags' => 'nullable|array',
            'tags.*' => 'nullable|integer',
            'games' => 'nullable|array',
            'games.*.user_id' => 'nullable|integer',
            'games.*.round' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendErrorResponse($validator->errors(), 422, 'Validation failed', null);
        }

        DB::beginTransaction();
        try {
            $tournament = Tournament::create([
               'event_id' => $eventId,
               'court_id' => $request->court_id,
            ]);

            $tags = collect();

            foreach ($request->tags as $tag) {
                $tournamentTag = $tournament->tags->attach($tag);
                $tags->push($tournamentTag);
            }

            $games = collect();

            foreach ($request->games as $game) {
                $tournamentGame = $tournament->games()->attach($game->user_id, ['round' => $game->round]);
                $games->push($tournamentGame);
            }

            $data = [
                'event' => $tournament,
                'tags' => $tags,
                'games' => $games
            ];

            DB::commit();
            return $this->sendSuccessResponse("Tournament created", 201, null, $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendExceptionResponse("Failed to create Tournament", 500, null, $e);
        }
    }
}
