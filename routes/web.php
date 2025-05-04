<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
       "status" => "success",
       "message" => "Selamat datang di aplikasi fupet"
    ]);
});
