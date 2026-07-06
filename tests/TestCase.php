<?php

namespace Tests;

/**
 * Base TestCase untuk aplikasi Apotek Digital.
 * Menyediakan helper skipUnlessFortifyHas untuk men-skip test
 * jika fitur Fortify tertentu tidak aktif.
 */

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        $connection = config('database.default');
        $dbName = config("database.connections.{$connection}.database");

        // FAILSAFE: Mencegah RefreshDatabase menghapus database lokal utama jika config ter-cache
        if ($connection !== 'sqlite' && $dbName === 'apotek_digital') {
            die("\n[CRITICAL FAILSAFE] Eksekusi test dihentikan! Test mendeteksi penggunaan database utama '{$dbName}'. Jalankan 'php artisan config:clear' untuk menggunakan pengaturan database in-memory.\n\n");
        }
    }

    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}
