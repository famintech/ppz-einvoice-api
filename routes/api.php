<?php

use Illuminate\Support\Facades\Route;


Route::get('/test', function () {
    return response()->json([
        'message' => 'API v1 is working'
    ]);
});
