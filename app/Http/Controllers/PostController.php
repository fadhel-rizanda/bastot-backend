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
    public function askGemini(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
        ]);

        $apiKey = env('GEMINI_API_KEY'); // Simpan di .env file, klo null: php artisan config:clear
        $prompt = $request->input('prompt');
        $promptHeader = "You are an assistant for Bastot, a basketball platform that helps players, teams, and fans connect and grow. Answer the following question in a helpful and concise manner: ";


        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $promptHeader . $prompt]
                    ]
                ]
            ]
        ]);

        if ($response->failed()) {
            return response()->json([
                'message' => 'Failed to fetch response from Gemini.',
                'error' => $response->body(),
            ], $response->status());
        }

        $data = $response->json();

        $output = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response generated.';

        return response()->json([
            'prompt' => $prompt,
            'response' => $output
        ]);
    }

    public function index()
    {
        return Cache::store('redis')->remember("posts", 60, fn() => Http::get("http://127.0.0.1:8001/posts")->json()
        );
    }

    public function show()
    {
        $id = Auth::id();
        Log::info($id);
        return Cache::remember("posts:{$id}", 60, fn() => Http::get("http://127.0.0.1:8001/posts/{$id}")->json());
    }
}
