<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'BuildingOS API',
        'docs' => url('/api/documentation'),
    ]);
});
