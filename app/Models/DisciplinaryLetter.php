<?php

namespace App\Models;

use App\Enums\DisciplinaryLetterType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'student_id',
    'academic_year_id',
    'type',
    'reason',
    'issued_at',
    'issued_by',
    'notes',
    'document_path',
    'status',
])]
class DisciplinaryLetter extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => DisciplinaryLetterType::class,
            'issued_at' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
