<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Medicine;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/medicines', fn () => Medicine::with('category')->orderBy('name')->get());