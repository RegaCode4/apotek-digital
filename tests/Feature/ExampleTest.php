<?php

/** Feature test contoh: memverifikasi halaman home dapat diakses. */

// Test: halaman home mengembalikan response sukses
test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
});