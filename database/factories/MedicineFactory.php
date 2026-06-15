<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Medicine>
 */
class MedicineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'generic_name' => fake()->words(2, true),
            'category_id' => Category::query()->inRandomOrder()->value('id')
                ?? Category::factory()->create()->id,
            'manufacturer' => fake()->company(),
            'unit' => 'tablet',
            'price' => fake()->randomFloat(2, 5000, 150000),
            'stock' => fake()->numberBetween(0, 100),
            'min_stock' => 10,
            'expiry_date' => fake()->dateTimeBetween('now', '+2 years'),
            'requires_prescription' => fake()->boolean(30),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
