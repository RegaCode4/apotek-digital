<?php

namespace App\Contracts;

interface BpjsServiceInterface
{
    /**
     * Verify a BPJS member by their membership number.
     *
     * @return array{status: string, name: string|null, kelas: string|null}
     */
    public function verifyMember(string $bpjsNumber): array;

    /**
     * Check whether a medicine is listed on the National Formulary (Fornas).
     */
    public function isFornas(int $medicineId): bool;
}
