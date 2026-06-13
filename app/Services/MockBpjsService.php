<?php

namespace App\Services;

use App\Contracts\BpjsServiceInterface;
use App\Models\Medicine;
use Illuminate\Support\Str;

/**
 * Simulated BPJS service for v1.0.
 *
 * Replace this binding in AppServiceProvider with a LiveBpjsService
 * implementation once the official BPJS API MoU is in place (see PRD §4.4).
 */
class MockBpjsService implements BpjsServiceInterface
{
    /**
     * Simulated member verification rules:
     *  - Exactly 13 digits  → aktif (kelas 2)
     *  - Anything else      → nonaktif
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
     * Check whether a medicine is on the National Formulary (Fornas).
     *
     * Matches by looking up the medicine name and checking if any of the
     * hardcoded Fornas generic names appear as a substring (case-insensitive).
     * This is intentionally simple for v1.0 — a real implementation would
     * query a dedicated fornas_medicines table or BPJS API.
     */
    public function isFornas(int $medicineId): bool
    {
        $medicine = Medicine::find($medicineId);

        if (! $medicine) {
            return false;
        }

        // Check both brand name and generic name
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

    // ── Private helpers ───────────────────────────────────────

    /**
     * Hardcoded Fornas generic name keywords (lowercase).
     * Source: Fornas Indonesia (simplified subset for simulation).
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
