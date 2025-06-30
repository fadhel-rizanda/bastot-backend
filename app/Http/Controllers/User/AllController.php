<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\community\Community;
use App\Models\community\Event;
use App\Models\community\Tournament;
use App\Models\court\Court;
use App\Models\court\Field;
use App\Models\court\Schedule;
use App\Models\game\Team;
use App\Models\Notification;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;
use App\Traits\ResponseAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AllController extends Controller
{
    use ResponseAPI;

    public function users(Request $request)
    {
        $data = [];
        if ($request->has('name')) {
            $data = User::where('name', $request->name)->where('id', '!=', $request->user()->id)->orderBy('created_at', 'desc')->get();
        } else {
            $data = User::where('id', '!=', $request->user()->id)->orderBy('created_at', 'desc')->get();
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Users retrieved successfully',
            'data' => $data
        ], 200);
    }

    public function roles(Request $request)
    {
        $data = [];
        if ($request->has('type')) {
            $data = Role::where('type', $request->type)->orderBy('created_at', 'desc')->get();
        } else {
            $data = Role::orderBy('created_at', 'desc')->get();
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Roles retrieved successfully',
            'data' => $data
        ], 200);
    }

    public function teams(Request $request)
    {
        $data = [];
        if ($request->has('name')) {
            $data = Team::where('name', $request->name)->orderBy('created_at', 'desc')->get();
        } else {
            $data = Team::orderBy('created_at', 'desc')->get();
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Teams retrieved successfully',
            'data' => $data
        ], 200);
    }

    public function myNotifications(Request $request)
    {
        $userId = $request->user()->id;
        $notif = [];
        if ($request->has('type')) {
            $notif = Notification::where('user_id', $userId)->where('type', $request->type)->orderBy('created_at', 'desc')->get();
        } else {
            $notif = Notification::where('user_id', $userId)->orderBy('created_at', 'desc')->get();
        }

        $unread = $notif->where('is_read', false)->count();

        $data = [
            'notifications' => $notif,
            'unread_count' => $unread
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Notifications retrieved successfully',
            'data' => $data
        ], 200);
    }

    public function courts(Request $request)
    {
        $data = [];
        if ($request->has('name')) {
            $data = Court::where('name', $request->name)->orderBy('created_at', 'desc')->get();
        } else {
            $data = Court::orderBy('created_at', 'desc')->get();
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Court retrieved successfully',
            'data' => $data
        ], 200);
    }

    public function fields(Request $request)
    {
        $data = [];
        if ($request->has('name')) {
            $data = Field::where('name', $request->name)->orderBy('created_at', 'desc')->get();
        } else if ($request->has('court_id')) {
            $data = Field::where('court_id', $request->court_id)->orderBy('created_at', 'desc')->get();
        } else {
            $data = Field::orderBy('created_at', 'desc')->get();
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Fields retrieved successfully',
            'data' => $data
        ], 200);
    }

    public function schedules(Request $request)
    {
        $query = Schedule::query()->orderBy('created_at', 'desc');

        if ($request->has('name')) {
            $query->where('name', $request->name);
        }

        if ($request->has('field_id')) {
            $query->where('field_id', $request->field_id);
        }

        if ($request->has('court_id')) {
            $query->whereHas('field.court', function ($q) use ($request) {
                $q->where('id', $request->court_id);
            });
        }

        if ($request->boolean('is_available')) {
            $query->where('is_available', $request->boolean('is_available'));
        }

        $data = $query->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Schedule retrieved successfully',
            'data' => $data
        ], 200);
    }

    public function getCommunities(Request $request)
    {
        $community = Community::with(['users', 'events', 'tags', 'reviews', 'baseCourt'])->paginate(5);
        return $this->sendSuccessPaginationResponse('Communities retrieved successfully', 200, 'success', null, $community);
    }

    public function createReview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'type' => 'required|string|in:event,court,tournament,field',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return $this->sendErrorResponse($validator->errors(), 422, 'Validation failed', null);
        }

        $modelClass = match ($request->type) {
            'event' => Event::class,
            'court' => Court::class,
            'tournament' => Tournament::class,
            'field' => Field::class,
            default => null,
        };

        if (!$modelClass || !$model = $modelClass::find($request->id)) {
            return $this->sendErrorResponse(null, 404, 'Target not found', null);
        }

        DB::beginTransaction();
        try {
            $review = Review::create([
                'user_id' => $request->user()->id,
                'title' => $request->title,
                'body' => $request->body,
                'rating' => $request->rating,
            ]);

            $model->reviews()->attach($review->id);

            DB::commit();
            return $this->sendSuccessResponse("Review created", 201, null, $review);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendExceptionResponse("Failed to create review", 500, null, $e);
        }
    }
}
