<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'school_name',
    'npsn',
    'principal_name',
    'address',
    'phone',
    'email',
    'website',
    'description',
    'vision',
    'mission',
    'history',
    'logo',
    'hero_image',
])]
class SchoolProfile extends Model
{
    use HasFactory;

    protected $table = 'school_profile';
}
