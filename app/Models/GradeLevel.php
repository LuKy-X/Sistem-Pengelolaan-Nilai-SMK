<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'name',
])]
class GradeLevel extends Model
{
    use HasFactory;

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }
}
