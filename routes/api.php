<?php

/**
 * File: api.php
 *
 * Route API untuk komunikasi frontend/eksternal dengan Sanctum auth.
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Medicine;

// Mendapatkan data user yang sedang login (via Sanctum)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Mendapatkan daftar obat beserta kategori (publik — via API)
Route::get('/medicines', fn () => Medicine::with('category')->orderBy('name')->get());