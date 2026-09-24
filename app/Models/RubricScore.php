<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'gradebook_score_id',
    'rubric_criterion_id',
    'points_awarded',
    'note',
    'graded_by',
])]
class RubricScore extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'points_awarded' => 'decimal:2',
        ];
    }

    public function gradebookScore(): BelongsTo
    {
        return $this->belongsTo(GradebookScore::class, 'gradebook_score_id');
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(RubricCriterion::class, 'rubric_criterion_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class, 'graded_by');
    }
}
