<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'description',
])]
class Category extends Model
{
    use HasFactory;

    /**
     * Get the medicines belonging to this category.
     */
    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
    }
}
