<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\court\Court;
use App\Models\game\Game;
use App\Models\game\UserTeam;
use App\Models\Location;
use App\Traits\ResponseAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

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
                $fields['profile_picture'] = Storage::disk('public')->put('images/court', $request->file('image'));
            }

            if (!isset($fields['location_id'])){
                $location = Location::firstOrCreate(
                    ['id' => $uriResponse['place_id']],
                    [
                        'name' => $uriResponse['display_name'],
                        'residential' => $uriResponse['address']['residential'],
                        'village' => $uriResponse['address']['village'],
                        'city' => $uriResponse['address']['city'],
                        'state' => $uriResponse['address']['state'],
                        'region' => $uriResponse['address']['region'],
                        'country' => $uriResponse['address']['country'],
                        'country_code' => $uriResponse['address']['country_code'],
                        'postcode' => $uriResponse['address']['postcode'],
                    ]);

                $fields['location_id'] = $location->id;
            }

            $court = Court::create($fields);
            DB::commit();
            return $this->sendSuccessResponse('Stats created successfully', 201, 'success', $court);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendExceptionResponse('Failed to create stats', 500, 'error', $exception);
        }
    }
}
