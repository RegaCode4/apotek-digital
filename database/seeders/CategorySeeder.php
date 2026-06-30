<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Seed categories table with ATC-based medicine categories.
 */
class CategorySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Sistem Pencernaan & Metabolisme',
                'description' => 'Obat saluran cerna, asam lambung, dan metabolisme.',
            ],
            [
                'name' => 'Sistem Kardiovaskular',
                'description' => 'Obat jantung, hipertensi, dan pembuluh darah.',
            ],
            [
                'name' => 'Sistem Saraf Pusat',
                'description' => 'Analgesik, antikonvulsan, antidepresan, dan psikotropika.',
            ],
            [
                'name' => 'Anti-Infeksi Sistemik',
                'description' => 'Antibiotik dan antimikroba sistemik.',
            ],
            [
                'name' => 'Sistem Pernapasan',
                'description' => 'Bronkodilator, mukolitik, antihistamin, dan dekongestan.',
            ],
            [
                'name' => 'Sistem Endokrin',
                'description' => 'Antidiabetik, hormon, dan kortikosteroid.',
            ],
            [
                'name' => 'Antineoplastik',
                'description' => 'Obat kemoterapi dan terapi kanker.',
            ],
            [
                'name' => 'Dermatologikal',
                'description' => 'Obat topikal untuk kulit (krim, salep, losion).',
            ],
            [
                'name' => 'Sistem Muskuloskeletal',
                'description' => 'NSAID, antirematik, dan obat asam urat.',
            ],
            [
                'name' => 'Organ Sensorik',
                'description' => 'Obat mata, telinga, dan hidung.',
            ],
            [
                'name' => 'Sistem Genitourinari & Hormon Seks',
                'description' => 'Obat saluran kemih, prostat, dan kontrasepsi/hormon seks.',
            ],
            [
                'name' => 'Darah & Organ Pembentuk Darah',
                'description' => 'Suplemen darah, antikoagulan, dan antifibrinolitik.',
            ],
            [
                'name' => 'Antiparasit, Insektisida & Repelen',
                'description' => 'Obat cacing, skabisida, dan antiparasit.',
            ],
            [
                'name' => 'Berbagai Macam (Various)',
                'description' => 'Antiseptik, rehidrasi, dan produk lain-lain.',
            ],
            [
                'name' => 'Vitamin dan Suplemen',
                'description' => 'Vitamin, mineral, dan suplemen kesehatan.',
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}
