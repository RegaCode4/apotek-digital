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
    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}
