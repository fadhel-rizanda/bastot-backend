<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\court\Court;
use App\Models\court\Field;
use App\Models\court\Reservation;
use App\Models\court\Schedule;
use App\Models\game\Game;
use App\Models\game\UserTeam;
use App\Models\Location;
use App\Traits\ResponseAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CourtOwnerController extends Controller
{
    use ResponseAPI;

    public function createCourt(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'location_id' => 'nullable|integer',
        ]);

        $uriResponse = Http::withHeaders([
            'Accept' => 'application/json',
            'User-Agent' => 'MyAppName/1.0 (myemail@example.com)'
        ])->get('https://nominatim.openstreetmap.org/reverse', [
            'format' => 'jsonv2',
            'lat' => $fields['latitude'],
            'lon' => $fields['longitude'],
            'addressdetails' => 1,
        ])->json();


        DB::beginTransaction();
        try {
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('images/court', $fileName, 'public');
                $fields['profile_picture'] = $path;
            } else {
                $fields['profile_picture'] = null;
            }

            if (!isset($fields['location_id'])) {
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


                $fields['location_id'] = $location->id;
            }

            $court = Court::create($fields);
            DB::commit();
            return $this->sendSuccessResponse('Court created successfully', 201, 'success', $court);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendExceptionResponse('Failed to create court', 500, 'error', $exception);
        }
    }

    public function createField(Request $request)
    {
        $fields = $request->validate([
            'court_id' => 'required|numeric',
            'name' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'default_price_per_hour' => 'required|numeric|min:0',
            'is_available' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('images/court/field', $fileName, 'public');
                $fields['image'] = $path;
            } else {
                $fields['image'] = null;
            }

            $court = Field::create($fields);
            DB::commit();
            return $this->sendSuccessResponse('Field created successfully', 201, 'success', $court);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendExceptionResponse('Failed to create field', 500, 'error', $exception);
        }
    }

    public function createScheduleByList(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'field_id' => 'required|numeric',
                'list' => 'required|array|min:1',
                'list.*.start_time' => 'required|date|after:now',
                'list.*.end_time' => 'required|date|after:list.*.start_time',
                'list.*.price_per_hour' => 'required|numeric',
                'list.*.is_available' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse($validator->errors(), 422, 'Validation failed', null);
            }

            $field = Field::where('id', $request->field_id)->first();
            $schedules = [];

            DB::beginTransaction();
            foreach ($request->list as $item) {
                $schedule = Schedule::create([
                    'field_id' => $request->field_id,
                    'start_time' => $item['start_time'],
                    'end_time' => $item['end_time'],
                    'price_per_hour' => $item['price_per_hour'] ?? $field->default_price_per_hour,
                    'is_available' => $item['is_available'] ?? true
                ]);
                $schedules[] = $schedule;
            }

            DB::commit();
            return $this->sendSuccessResponse(
                "Schedule list created",
                201,
                null,
                $schedules
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendExceptionResponse(
                "Failed to create schedule list",
                500,
                null,
                $e
            );
        }
    }

    public function createReservations(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'list' => 'required|array|min:1',
                'list.*.schedule_id' => 'required|numeric',
                'list.*.game_id' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse($validator->errors(), 422, 'Validation failed', null);
            }

            $user = $request->user();
            $reservations = [];
            $totalCost = 0;

            DB::beginTransaction();
            foreach ($request->list as $item) {
                $schedule = Schedule::where('id', $item['schedule_id'])
                    ->where('is_available', true) // pastikan belum dipesan
                    ->first();

                if (!$schedule) {
                    DB::rollBack();
                    return $this->sendErrorResponse(
                        "One or more schedules are no longer available",
                        409,
                        null,
                        null
                    );
                }

                $schedule->update(['is_available' => false]);

                $reservation = Reservation::create([
                    'schedule_id' => $item['schedule_id'],
                    'user_id' => $user->id,
                    'status_id' => 'SCHEDULED',
                    'game_id' => $item['game_id'] ?? null,
                ]);

                $reservations[] = $reservation;
                $totalCost += $reservation->schedule->price_per_hour ?? 0;
            }

            DB::commit();

            $data = [
                'reservation' => $reservations,
                'total_cost' => $totalCost
            ];

            return $this->sendSuccessResponse(
                "Reservations created successfully",
                201,
                null,
                $data
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendExceptionResponse(
                "Failed to create reservation",
                500,
                null,
                $e
            );
        }
    }

}
