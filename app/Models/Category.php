<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'description',
])]
class Category extends Model
{
    /**
     * Get the medicines belonging to this category.
     */
    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
    }
}
