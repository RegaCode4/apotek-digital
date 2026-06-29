<?php

namespace App\Models;

use Database\Factories\MedicineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'generic_name',
    'category_id',
    'manufacturer',
    'unit',
    'price',
    'stock',
    'min_stock',
    'expiry_date',
    'requires_prescription',
    'description',
])]
/**
 * Representasi obat dalam sistem dengan informasi stok, harga, dan kategori.
 */
class Medicine extends Model
{
    /** @use HasFactory<MedicineFactory> */
    use HasFactory;

    /**
     * Casting atribut ke tipe data tertentu.
     */
    protected function casts(): array
    {
        return [
            'requires_prescription' => 'boolean',
            'expiry_date' => 'date:Y-m-d',
            'price' => 'decimal:2',
        ];
    }

    /**
     * Mendapatkan kategori dari obat ini.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Mendapatkan mutasi stok untuk obat ini.
     */
    public function stockMutations(): HasMany
    {
        return $this->hasMany(StockMutation::class);
    }

    /**
     * Mendapatkan batch untuk obat ini.
     */
    public function medicineBatches(): HasMany
    {
        return $this->hasMany(MedicineBatch::class);
    }

    /**
     * Mendapatkan penjualan yang mencakup obat ini.
     */
    public function sales(): BelongsToMany
    {
        return $this->belongsToMany(Sale::class, 'sale_items');
    }

    /**
     * Filter obat yang stoknya sudah mencapai batas minimum.
     */
    public function scopeLowStock(Builder $query): void
    {
        $query->whereColumn('stock', '<=', 'min_stock');
    }

    /**
     * Filter obat yang akan kadaluwarsa dalam jumlah bulan tertentu.
     */
    public function scopeExpiringSoon(Builder $query, int $months = 3): void
    {
        $query->where('expiry_date', '<=', now()->addMonths($months));
    }
}
