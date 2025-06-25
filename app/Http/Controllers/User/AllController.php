<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\court\Court;
use App\Models\court\Field;
use App\Models\court\Schedule;
use App\Models\game\Team;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use App\Traits\ResponseAPI;
use Illuminate\Http\Request;

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
        }else {
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
        $data = [];
        if ($request->has('name')) {
            $data = Schedule::where('name', $request->name)->orderBy('created_at', 'desc')->get();
        } else if ($request->has('court_id')) {
            $data = Schedule::whereHas('field', function ($query) use ($request) {
                $query->whereHas('court', function ($query) use ($request) {
                    $query->where('id', $request->court_id);
                });
            })->orderBy('created_at', 'desc')->get();
        }else if($request->has('field_id')){
            $data = Schedule::where('field_id', $request->field_id)->orderBy('created_at', 'desc')->get();
        }else {
            $data = Schedule::orderBy('created_at', 'desc')->get();
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Schedule retrieved successfully',
            'data' => $data
        ], 200);
    }
}
