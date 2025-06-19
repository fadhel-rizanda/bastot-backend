<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DriveController extends Controller
{
    public function token()
    {
        $client_id = \Config('services.google.client_id');
        $client_secret = \Config('services.google.client_secret');
        $refresh_token = \Config('services.google.refresh_token');
        $response = Http::post('https://oauth2.googleapis.com/token', [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'refresh_token' => $refresh_token,
            'grant_type' => 'refresh_token',
        ]);
        return json_decode((string)$response->getBody(), true)['access_token'];
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'file|required',
            'file_name' => 'required|string'
        ]);

        $file = $request->file('file');
        $filename = $request->file_name . '.' . $file->getClientOriginalExtension();

        Storage::disk('google')->put($filename, File::get($file));

        $adapter = Storage::disk('google')->getAdapter();
        $service = $adapter->getService();
        $file = $service->files->listFiles([
            'q' => "name='{$filename}'",
            'spaces' => 'drive',
            'fields' => 'files(id, name)',
        ]);

        if (count($file->files) > 0) {
            return response()->json([
                'file_id' => $file->files[0]->id,
                'message' => 'File uploaded to Google Drive',
            ], 201);
        }

        return response()->json([
            'message' => 'Upload succeeded, but file not found in listing.',
        ], 200);
    }
}
