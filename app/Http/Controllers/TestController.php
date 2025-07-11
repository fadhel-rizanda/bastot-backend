<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function post(Request $request){
        $message = $request->input('message');

        broadcast(new MessageSent($message));

        return response()->json(['status' => 'Message sent']);
    }
}
