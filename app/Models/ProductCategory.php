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
class ProductCategory extends Model
{
    use HasFactory;

    public function products(): HasMany
    {
        return $this->hasMany(StudentProduct::class, 'category_id');
    }
}
