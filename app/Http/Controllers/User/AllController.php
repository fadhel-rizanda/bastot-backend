<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
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
        if(!$request->name){
            $data = User::where('id', '!=', $request->user()->id)->get();
        }
        else{
            $data = User::where('name', $request->name)->where('id', '!=', $request->user()->id)->get();
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
        if(!$request->type){
            $data = Role::all();
        }
        else{
            $data = Role::where('type', $request->type)->get();
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
        if(!$request->name){
            $data = Team::all();
        }
        else{
            $data = Team::where('name', $request->name)->get();
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
        $data = [];
        if(!$request->type){
            $data = Notification::where('user_id', $userId)->get();
        }
        else{
            $data = Notification::where('user_id', $userId)->where('type', $request->type)->get();
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Notifications retrieved successfully',
            'data' => $data
        ], 200);
    }
}
