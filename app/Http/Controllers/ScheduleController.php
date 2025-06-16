<?php

namespace App\Http\Controllers;

use App\Models\court\Court;
use App\Models\court\Schedule;
use App\Traits\ResponseAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ScheduleController extends Controller
{
    use ResponseAPI;

    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'court_id' => 'required',
                'time' => 'required|time',
                'price' => 'required|number'
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse($validator->errors(), 422, 'Validation failed', null);
            }

            Schedule::create([
                'court_id' => $request->court_id,

            ]);

            $request->schedules;

        } catch (ValidationException $e) {
            return $this->sendExceptionResponse(
                "Failed to create schedules",
                401,
                null,
                null);
        }
    }
}
