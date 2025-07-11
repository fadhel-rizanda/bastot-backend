<?php

namespace App\Http\Controllers\User;

use App\Enums\CacheDuration;
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
use App\Models\Status;
use App\Models\Tag;
use App\Models\User;
use App\Traits\ResponseAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AllController extends Controller
{
    use ResponseAPI;

    public function users(Request $request)
    {
        $cacheKey = 'users:' . ($request->has('name') ? 'name:' . $request->name : 'all') . ':user:' . $request->user()->id;

        $data = Cache::remember($cacheKey, CacheDuration::MEDIUM->value, function () use ($request) {
            if( $request->has('name')) {
                return User::where('name', $request->name)
                    ->where('id', '!=', $request->user()->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                return User::where('id', '!=', $request->user()->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        });
        return $this->sendSuccessResponse("Users retrieved successfully", 200, null, $data);
    }

    public function roles(Request $request)
    {
        $cacheKey = 'roles:' . ($request->has('type') ? 'type:' . $request->type : 'all');
        $data = Cache::remember($cacheKey, CacheDuration::MEDIUM->value, function () use ($request) {
            if ($request->has('type')) {
                return Role::where('type', $request->type)->orderBy('created_at', 'desc')->get();
            } else {
                return Role::orderBy('created_at', 'desc')->get();
            }
        });
        return $this->sendSuccessResponse("Roles retrieved successfully", 200, null, $data);
    }

    public function status(Request $request)
    {
        $cacheKey = 'status:' . ($request->has('type') ? 'type:' . $request->type : 'all');
        $data = Cache::remember($cacheKey, CacheDuration::MEDIUM->value, function () use ($request) {
            if ($request->has('type')) {
                return Status::where('type', $request->type)->orderBy('created_at', 'desc')->get();
            } else {
                return Status::orderBy('created_at', 'desc')->get();
            }
        });
        return $this->sendSuccessResponse("Status retrieved successfully", 200, null, $data);
    }

    public function tags(Request $request)
    {
        $cacheKey = 'tags:' . ($request->has('type') ? 'type:' . $request->type : 'all');
        $data = Cache::remember($cacheKey, CacheDuration::MEDIUM->value, function () use ($request) {
            if ($request->has('type')) {
                return Tag::where('type', $request->type)->orderBy('created_at', 'desc')->get();
            } else {
                return Tag::orderBy('created_at', 'desc')->get();
            }
        });
        return $this->sendSuccessResponse("Tags retrieved successfully", 200, null, $data);
    }

    public function teams(Request $request)
    {
        $cacheKey = 'teams:' . ($request->has('name') ? 'name:' . $request->name : 'all');
        $data = Cache::remember($cacheKey, CacheDuration::MEDIUM->value, function () use ($request) {
            if ($request->has('name')) {
                return Team::where('name', $request->name)->orderBy('created_at', 'desc')->get();
            } else {
                return Team::orderBy('created_at', 'desc')->get();
            }
        });
        return $this->sendSuccessResponse("Teams retrieved successfully", 200, null, $data);
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

        return $this->sendSuccessResponse("Notifications retrieved successfully", 200, null, $data);
    }

    public function courts(Request $request)
    {
        $cacheKey = 'courts:' . ($request->has('name') ? 'name:' . $request->name : 'all');
        $data = Cache::remember($cacheKey, CacheDuration::MEDIUM->value, function () use ($request) {
            if ($request->has('name')) {
                return Court::where('name', $request->name)->orderBy('created_at', 'desc')->get();
            } else {
                return Court::orderBy('created_at', 'desc')->get();
            }
        });
        return $this->sendSuccessResponse("Courts retrieved successfully", 200, null, $data);
    }

    public function fields(Request $request)
    {
        $cacheKey = 'fields:' . ($request->has('name') ? 'name:' . $request->name : ($request->has('court_id') ? 'court_id:' . $request->court_id : 'all'));
        $data = Cache::remember($cacheKey, CacheDuration::MEDIUM->value, function () use ($request) {
            if ($request->has('name')) {
                return Field::where('name', $request->name)->orderBy('created_at', 'desc')->get();
            } else if ($request->has('court_id')) {
                return Field::where('court_id', $request->court_id)->orderBy('created_at', 'desc')->get();
            } else {
                return Field::orderBy('created_at', 'desc')->get();
            }
        });
        return $this->sendSuccessResponse("Fields retrieved successfully", 200, null, $data);
    }

    public function schedules(Request $request)
    {
        $cacheKeyParts = ['schedules'];

        if ($request->has('name')) {
            $cacheKeyParts[] = 'name:' . $request->name;
        }

        if ($request->has('field_id')) {
            $cacheKeyParts[] = 'field:' . $request->field_id;
        }

        if ($request->has('court_id')) {
            $cacheKeyParts[] = 'court:' . $request->court_id;
        }

        if ($request->has('is_available')) {
            $cacheKeyParts[] = 'available:' . ($request->boolean('is_available') ? '1' : '0');
        }

        $cacheKey = implode('|', $cacheKeyParts);

        $data = Cache::remember($cacheKey, CacheDuration::SHORT->value, function () use ($request) {
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
                $query->where('is_available', true);
            }

            return $query->get();
        });

        return $this->sendSuccessResponse("Schedules retrieved successfully", 200, null, $data);
    }

    public function getCommunities(Request $request)
    {
        if($request->has('name')) {
            $community = Community::where('name', 'like', '%' . $request->name . '%')
                ->with(['users', 'events', 'tags', 'reviews', 'baseCourt'])
                ->orderBy('created_at', 'desc')
                ->paginate(5);
            return $this->sendSuccessPaginationResponse('Communities retrieved successfully', 200, 'success', null, $community);
        }
        \Log::info(Community::first()->type);
        $community = Community::with(['users', 'events', 'tags', 'reviews', 'baseCourt'])->orderBy('created_at', 'desc')->paginate(5);
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
