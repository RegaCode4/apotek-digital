<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Attributes\Fillable;

/**
 * Kategori untuk mengelompokkan obat-obatan.
 */
#[Fillable(['name'])]
class Category extends Model
{
    use HasFactory;

    /**
     * Daftar obat dalam kategori ini.
     */
    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
    }
}
