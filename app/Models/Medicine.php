<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'generic_name',
    'category',
    'manufacturer',
    'unit',
    'price',
    'stock',
    'min_stock',
    'expiry_date',
    'requires_prescription',
    'description',
])]
class Medicine extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requires_prescription' => 'boolean',
            'expiry_date' => 'date',
            'price' => 'decimal:2',
        ];
    }

    /**
     * Get the stock mutations for the medicine.
     */
    public function stockMutations(): HasMany
    {
        return $this->hasMany(StockMutation::class);
    }

    /**
     * Get the batches for the medicine.
     */
    public function medicineBatches(): HasMany
    {
        return $this->hasMany(MedicineBatch::class);
    }

    /**
     * Get the sales that include this medicine.
     */
    public function sales(): BelongsToMany
    {
        return $this->belongsToMany(Sale::class, 'sale_items');
    }

    /**
     * Scope a query to only include medicines at or below minimum stock.
     */
    public function scopeLowStock(Builder $query): void
    {
        $query->whereColumn('stock', '<=', 'min_stock');
    }

    /**
     * Scope a query to only include medicines expiring within the given months.
     */
    public function scopeExpiringSoon(Builder $query, int $months = 3): void
    {
        $query->where('expiry_date', '<=', now()->addMonths($months));
    }
}
