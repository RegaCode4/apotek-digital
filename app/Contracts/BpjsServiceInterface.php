<?php

namespace App\Contracts;

/** Kontrak layanan BPJS. */
interface BpjsServiceInterface
{
    /**
     * Memverifikasi anggota BPJS berdasarkan nomor kepesertaannya.
     *
     * @return array{status: string, name: string|null, kelas: string|null}
     */
    public function verifyMember(string $bpjsNumber): array;

    /**
     * Memeriksa apakah suatu obat terdaftar dalam Formularium Nasional (Fornas).
     */
    public function isFornas(int $medicineId): bool;
}
