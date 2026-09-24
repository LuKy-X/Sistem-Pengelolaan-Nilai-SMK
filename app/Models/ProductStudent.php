<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id',
    'student_id',
])]
class ProductStudent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'product_students';

    public function product(): BelongsTo
    {
        return $this->belongsTo(StudentProduct::class, 'product_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }
}
