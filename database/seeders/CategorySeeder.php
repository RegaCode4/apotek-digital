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
                'description' => 'Obat untuk saluran pencernaan, metabolisme, dan gangguan terkait (ATC: A)',
            ],
            [
                'name' => 'Sistem Kardiovaskular',
                'description' => 'Obat untuk jantung dan pembuluh darah (ATC: C)',
            ],
            [
                'name' => 'Sistem Saraf Pusat',
                'description' => 'Obat untuk gangguan sistem saraf pusat termasuk analgesik dan anestesi (ATC: N)',
            ],
            [
                'name' => 'Anti-Infeksi Sistemik',
                'description' => 'Antibiotik, antivirus, antifungal, dan antiparasit sistemik (ATC: J)',
            ],
            [
                'name' => 'Sistem Pernapasan',
                'description' => 'Obat untuk saluran pernapasan termasuk bronkodilator dan antitusif (ATC: R)',
            ],
            [
                'name' => 'Sistem Endokrin',
                'description' => 'Hormon dan obat untuk gangguan endokrin termasuk diabetes (ATC: H)',
            ],
            [
                'name' => 'Antineoplastik',
                'description' => 'Obat kemoterapi dan imunomodulator untuk pengobatan kanker (ATC: L)',
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
