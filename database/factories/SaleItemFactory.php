<?php

namespace Database\Factories;

use App\Models\Medicine;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating SaleItem model instances.
 *
 * @extends Factory<SaleItem>
 */
class SaleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = fake()->randomFloat(2, 5000, 150000);
        $quantity = fake()->numberBetween(1, 10);
        $discount = 0;

        return [
            'sale_id' => Sale::factory(),
            'medicine_id' => Medicine::factory(),
            'prescription_no' => null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount' => $discount,
            'subtotal' => ($unitPrice * $quantity) - $discount,
        ];
    }
}
