<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'invoice_no',
    'buyer_name',
    'cashier_id',
    'payment_method',
    'subtotal',
    'discount_amount',
    'tax_amount',
    'grand_total',
    'bpjs_claim_no',
    'notes',
    'sale_date',
])]
class Sale extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sale_date' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    /**
     * Get the cashier who processed this sale.
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    /**
     * Get the line items for this sale.
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Generate the next invoice number for today.
     *
     * Format: INV-YYYYMMDD-XXX (sequential per day, zero-padded to 3 digits)
     */
    public static function generateInvoiceNo(): string
    {
        $today = now()->format('Ymd');
        $prefix = "INV-{$today}-";

        $lastInvoice = static::where('invoice_no', 'like', "{$prefix}%")
            ->orderByDesc('invoice_no')
            ->value('invoice_no');

        $sequence = $lastInvoice
            ? (int) substr($lastInvoice, -3) + 1
            : 1;

        return $prefix.str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}
