<?php

namespace App\Models;

use App\Enums\AssessmentStatus;
use App\Enums\AssessmentType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'gradebook_column_id',
    'teaching_assignment_id',
    'type',
    'title',
    'description',
    'instructions',
    'due_at',
    'submission_required',
    'rubric_id',
    'created_by',
    'published_at',
    'status',
])]
class Assessment extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => AssessmentType::class,
            'status' => AssessmentStatus::class,
            'due_at' => 'datetime',
            'published_at' => 'datetime',
            'submission_required' => 'boolean',
        ];
    }

    public function gradebookColumn(): BelongsTo
    {
        return $this->belongsTo(GradebookColumn::class, 'gradebook_column_id');
    }

    public function teachingAssignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class, 'teaching_assignment_id');
    }

    public function rubric(): BelongsTo
    {
        return $this->belongsTo(Rubric::class, 'rubric_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class, 'created_by');
    }

    public function latePolicy(): HasOne
    {
        return $this->hasOne(AssessmentLatePolicy::class, 'assessment_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssessmentSubmission::class, 'assessment_id');
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }
}
