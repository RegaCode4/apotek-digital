<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 20000, 500000);
        $discount = 0;
        $tax = 0;

        return [
            'invoice_no' => 'INV-'.now()->format('Ymd').'-'.str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'buyer_name' => fake()->name(),
            'cashier_id' => User::factory(),
            'payment_method' => fake()->randomElement(['cash', 'transfer', 'bpjs', 'insurance']),
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'grand_total' => $subtotal - $discount + $tax,
            'bpjs_claim_no' => null,
            'notes' => null,
            'sale_date' => now(),
        ];
    }

    /**
     * Set payment method to BPJS with a claim number.
     */
    public function bpjs(string $claimNo = '0001234567890'): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'bpjs',
            'bpjs_claim_no' => $claimNo,
        ]);
    }
}
