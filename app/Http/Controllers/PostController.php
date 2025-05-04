<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class PostController extends Controller
{
    public function index(){
        return Cache::store('redis')->remember("posts", 60, fn() =>
            Http::get("http://127.0.0.1:8001/posts")->json()
        );
    }

    public function show(){
        $id = Auth::id();
        Log::info($id);
        return Cache::remember("posts:{$id}", 60, fn() => Http::get("http://127.0.0.1:8001/posts/{$id}")->json());
    }
}
