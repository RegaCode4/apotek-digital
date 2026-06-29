<?php

/**
 * File: providers.php
 *
 * Daftar service provider yang didaftarkan ke aplikasi Laravel.
 */

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
];
