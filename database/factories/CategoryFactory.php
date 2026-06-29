<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating Category model instances.
 *
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $index = 0;

        // Pakai nama ATC WHO yang realistis, fallback ke unique word jika kehabisan
        $atcNames = [
            'Sistem Pencernaan & Metabolisme',
            'Sistem Kardiovaskular',
            'Sistem Saraf Pusat',
            'Anti-Infeksi Sistemik',
            'Sistem Pernapasan',
            'Sistem Endokrin',
            'Antineoplastik',
        ];

        $name = isset($atcNames[$index])
            ? $atcNames[$index]
            : 'Kategori '.fake()->unique()->word();

        $index++;

        return [
            'name' => $name,
            'description' => fake()->optional()->sentence(),
        ];
    }
}
