<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'medicine_id',
    'type',
    'quantity',
    'reference_id',
    'notes',
    'created_by',
])]
/**
 * Mencatat mutasi stok obat (masuk/keluar) dengan referensi transaksi.
 */
class StockMutation extends Model
{
    /**
     * Casting atribut ke tipe data tertentu.
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * Mendapatkan obat yang memiliki mutasi stok ini.
     */
    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    /**
     * Mendapatkan pengguna yang membuat mutasi stok ini.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
