<?php

/**
 * File: api.php
 *
 * Route API untuk komunikasi frontend/eksternal dengan Sanctum auth.
 */

use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Mendapatkan data pengguna yang sedang login saat ini (via Sanctum).
 * Membutuhkan autentikasi token Sanctum.
 */
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/**
 * Mendapatkan daftar seluruh obat beserta kategorinya.
 * Rute publik yang dapat diakses melalui API.
 */
Route::get('/medicines', fn () => Medicine::with('category')->orderBy('name')->get());
