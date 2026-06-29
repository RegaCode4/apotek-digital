<?php

namespace App\Services;

use App\Contracts\BpjsServiceInterface;
use App\Models\Medicine;
use Illuminate\Support\Str;

/**
 * Layanan BPJS simulasi untuk v1.0.
 *
 * Ganti binding ini di AppServiceProvider dengan implementasi LiveBpjsService
 * setelah MoU API BPJS resmi tersedia (lihat PRD §4.4).
 */
class MockBpjsService implements BpjsServiceInterface
{
    /**
     * Aturan verifikasi anggota simulasi:
     *  - Tepat 13 digit  → aktif (kelas 2)
     *  - Selain itu      → nonaktif
     *
     * @return array{status: string, name: string|null, kelas: string|null}
     */
    public function verifyMember(string $bpjsNumber): array
    {
        $cleaned = preg_replace('/\D/', '', $bpjsNumber);

        if (strlen((string) $cleaned) === 13) {
            return [
                'status' => 'aktif',
                'name' => 'Nama Peserta Simulasi',
                'kelas' => '2',
            ];
        }

        return [
            'status' => 'nonaktif',
            'name' => null,
            'kelas' => null,
        ];
    }

    /**
     * Memeriksa apakah obat ada dalam Formularium Nasional (Fornas).
     *
     * Mencocokkan dengan mencari nama obat dan memeriksa apakah
     * nama generik Fornas yang sudah di-hardcode muncul sebagai substring (case-insensitive).
     * Ini sengaja dibuat sederhana untuk v1.0 — implementasi asli akan
     * melakukan query ke tabel fornas_medicines khusus atau API BPJS.
     */
    public function isFornas(int $medicineId): bool
    {
        $medicine = Medicine::find($medicineId);

        if (! $medicine) {
            return false;
        }

        // Memeriksa nama merek dan nama generik
        $namesToCheck = array_filter([
            Str::lower($medicine->name),
            Str::lower((string) $medicine->generic_name),
        ]);

        foreach ($namesToCheck as $name) {
            foreach ($this->fornasKeywords() as $keyword) {
                if (str_contains($name, $keyword)) {
                    return true;
                }
            }
        }

        return false;
    }

    // ── Helper privat ────────────────────────────────────────

    /**
     * Kata kunci nama generik Fornas yang di-hardcode (huruf kecil).
     * Sumber: Fornas Indonesia (subset sederhana untuk simulasi).
     *
     * @return list<string>
     */
    private function fornasKeywords(): array
    {
        return [
            // Analgesik & antipiretik
            'paracetamol',
            'parasetamol',
            'ibuprofen',
            'aspirin',
            'asam asetilsalisilat',
            'metamizol',
            'tramadol',

            // Antibiotik
            'amoxicillin',
            'amoksisilin',
            'ampisilin',
            'cotrimoxazole',
            'kotrimoksazol',
            'ciprofloxacin',
            'siprofloksasin',
            'metronidazol',

            // Antihipertensi
            'amlodipine',
            'amlodipin',
            'captopril',

            // Antidiabetik
            'metformin',
            'glibenclamide',
            'glibenklamid',

            // Antihistamin
            'cetirizine',
            'setirizin',
            'loratadine',
            'loratadin',
            'klorfeniramin',

            // Saluran pencernaan
            'omeprazole',
            'omeprazol',
            'ranitidin',
            'ranitidine',
            'antasida',

            // Vitamin & mineral
            'vitamin c',
            'asam askorbat',
            'vitamin b',
            'tablet tambah darah',
            'sulfat ferosus',
        ];
    }
}
