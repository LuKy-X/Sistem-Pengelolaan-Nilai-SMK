<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'gradebook_column_id',
    'student_id',
    'raw_score',
    'final_score',
    'max_score_snapshot',
    'late_minutes',
    'late_deduction',
    'feedback',
    'source',
    'graded_by',
    'graded_at',
])]
class GradebookScore extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'raw_score' => 'decimal:2',
            'final_score' => 'decimal:2',
            'max_score_snapshot' => 'decimal:2',
            'late_minutes' => 'integer',
            'late_deduction' => 'decimal:2',
            'graded_at' => 'datetime',
        ];
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(GradebookColumn::class, 'gradebook_column_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class, 'graded_by');
    }

    public function rubricScores(): HasMany
    {
        return $this->hasMany(RubricScore::class, 'gradebook_score_id');
    }
}
