<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'medicine_id',
    'batch_number',
    'quantity',
    'expiry_date',
])]
/**
 * Batch obat dari suatu purchase order, melacak nomor batch dan tanggal kadaluwarsa.
 */
class MedicineBatch extends Model
{
    /**
     * Casting atribut ke tipe data tertentu.
     */
    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
        ];
    }

    /**
     * Mendapatkan obat yang memiliki batch ini.
     */
    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    /**
     * Scope query untuk pengurutan First Expired, First Out.
     */
    public function scopeFefo(Builder $query): void
    {
        $query->orderBy('expiry_date', 'asc');
    }
}
