<?php

namespace App\Concerns;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules\Password;

/** Aturan validasi kata sandi. */
trait PasswordValidationRules
{
    /**
     * Mendapatkan aturan validasi yang digunakan untuk memvalidasi kata sandi.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function passwordRules(): array
    {
        return ['required', 'string', Password::default(), 'confirmed'];
    }

    /**
     * Mendapatkan aturan validasi yang digunakan untuk memvalidasi kata sandi saat ini.
     *
     * @return array<int, ValidationRule|array<mixed>|string>
     */
    protected function currentPasswordRules(): array
    {
        return ['required', 'string', 'current_password'];
    }
}
