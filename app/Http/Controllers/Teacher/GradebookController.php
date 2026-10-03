<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\GradebookCalculationType;
use App\Enums\GradebookColumnType;
use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\ClassEnrollment;
use App\Models\Gradebook;
use App\Models\GradebookColumn;
use App\Models\GradebookScore;
use App\Models\GradebookStudent;
use App\Models\SchoolProfile;
use App\Models\TeachingAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GradebookController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = Auth::user()->teacherProfile;

        $assignments = TeachingAssignment::with([
            'schoolClass.gradeLevel',
            'schoolClass.department',
            'subject',
            'semester.academicYear',
            'gradebooks.columns',
            'gradebooks.students',
        ])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->get();

        $selectedAssignmentId = $request->query('assignment_id');

        $gradebooksQuery = Gradebook::with([
            'teachingAssignment.schoolClass.department',
            'teachingAssignment.subject',
            'teachingAssignment.semester.academicYear',
            'columns' => fn ($q) => $q->orderBy('sort_order'),
            'students',
        ])
            ->whereIn('teaching_assignment_id', $assignments->pluck('id'));

        if ($selectedAssignmentId) {
            $gradebooksQuery->where('teaching_assignment_id', $selectedAssignmentId);
        }

        $gradebooks = $gradebooksQuery->latest()->get();

        return view('teacher.gradebooks.index', compact('assignments', 'gradebooks', 'selectedAssignmentId'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Gradebook::class);

        $teacher = Auth::user()->teacherProfile;

        $assignments = TeachingAssignment::with([
            'schoolClass.department',
            'subject',
            'semester.academicYear',
            'schoolClass.enrollments.student',
        ])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->get();

        $selectedAssignmentId = $request->query('assignment_id', $assignments->first()?->id);

        return view('teacher.gradebooks.create', compact('assignments', 'selectedAssignmentId'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Gradebook::class);

        $teacher = Auth::user()->teacherProfile;

        $validated = $request->validate([
            'teaching_assignment_id' => ['required', 'exists:teaching_assignments,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
            'columns' => ['nullable', 'array'],
            'columns.*.name' => ['required_with:columns', 'string', 'max:100'],
            'columns.*.code' => ['required_with:columns', 'string', 'max:50'],
            'columns.*.column_type' => ['required_with:columns', 'string', 'in:SCORE,SUMMARY'],
            'columns.*.calculation_type' => ['nullable', 'string', 'in:AVERAGE,SUM,WEIGHTED_AVERAGE'],
            'columns.*.max_score' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'columns.*.weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'columns.*.sources' => ['nullable'],
        ]);

        $assignment = TeachingAssignment::findOrFail($validated['teaching_assignment_id']);
        if ($assignment->teacher_id !== $teacher->id && ! Auth::user()->isAdmin()) {
            abort(403, 'Anda tidak berhak membuat buku nilai untuk kelas ini.');
        }

        $gradebook = DB::transaction(function () use ($validated, $assignment, $request) {
            $gradebook = Gradebook::create([
                'teaching_assignment_id' => $assignment->id,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Auto enroll active students in the class
            $enrollments = ClassEnrollment::where('class_id', $assignment->class_id)
                ->where('status', 'ACTIVE')
                ->get();

            foreach ($enrollments as $enrollment) {
                GradebookStudent::firstOrCreate(
                    [
                        'gradebook_id' => $gradebook->id,
                        'student_id' => $enrollment->student_id,
                    ],
                    [
                        'class_enrollment_id' => $enrollment->id,
                        'status' => 'ACTIVE',
                        'joined_at' => now(),
                    ]
                );
            }

            // Save configured columns
            if (! empty($validated['columns'])) {
                $columnsByCode = [];
                foreach ($validated['columns'] as $index => $col) {
                    $isSummary = ($col['column_type'] ?? 'SCORE') === 'SUMMARY';
                    $colType = $isSummary ? GradebookColumnType::Summary : GradebookColumnType::Score;
                    $calcType = null;
                    if ($isSummary && ! empty($col['calculation_type'])) {
                        $calcType = match (strtoupper($col['calculation_type'])) {
                            'SUM' => GradebookCalculationType::Sum,
                            'WEIGHTED_AVERAGE' => GradebookCalculationType::WeightedAverage,
                            default => GradebookCalculationType::Average,
                        };
                    }

                    $createdCol = GradebookColumn::create([
                        'gradebook_id' => $gradebook->id,
                        'name' => $col['name'],
                        'code' => strtoupper($col['code']),
                        'column_type' => $colType,
                        'calculation_type' => $calcType,
                        'max_score' => $col['max_score'] ?? 100.00,
                        'weight' => $col['weight'] ?? 10.00,
                        'sort_order' => $index + 1,
                        'is_visible' => true,
                        'is_included_in_average' => true,
                    ]);
                    $columnsByCode[strtoupper($col['code'])] = $createdCol;
                }

                // Link summary sources
                foreach ($validated['columns'] as $col) {
                    $isSummary = ($col['column_type'] ?? 'SCORE') === 'SUMMARY';
                    $codeUpper = strtoupper($col['code']);
                    if ($isSummary && isset($columnsByCode[$codeUpper])) {
                        $summaryCol = $columnsByCode[$codeUpper];
                        $sourcesRaw = $col['sources'] ?? [];
                        if (is_string($sourcesRaw)) {
                            $sourcesRaw = array_filter(array_map('trim', explode(',', $sourcesRaw)));
                        }
                        $sourceColIds = [];
                        foreach ($sourcesRaw as $srcCode) {
                            $srcCodeUpper = strtoupper($srcCode);
                            if (isset($columnsByCode[$srcCodeUpper]) && $columnsByCode[$srcCodeUpper]->id !== $summaryCol->id) {
                                $sourceColIds[] = $columnsByCode[$srcCodeUpper]->id;
                            }
                        }
                        if (! empty($sourceColIds)) {
                            $summaryCol->sourceColumns()->sync($sourceColIds);
                        }
                    }
                }
            }

            return $gradebook;
        });

        return redirect()->route('teacher.gradebooks.show', $gradebook)->with('success', 'Buku nilai berhasil dibuat dan siap digunakan.');
    }

    public function edit(Gradebook $gradebook): View
    {
        Gate::authorize('update', $gradebook);

        $gradebook->load([
            'teachingAssignment.schoolClass',
            'teachingAssignment.subject',
            'teachingAssignment.semester.academicYear',
            'columns.sourceColumns',
            'columns' => fn ($q) => $q->orderBy('sort_order'),
            'students.student.user',
        ]);

        return view('teacher.gradebooks.edit', compact('gradebook'));
    }

    public function update(Request $request, Gradebook $gradebook): RedirectResponse
    {
        Gate::authorize('update', $gradebook);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
            'columns' => ['nullable', 'array'],
            'columns.*.id' => ['nullable'],
            'columns.*.name' => ['required_with:columns', 'string', 'max:100'],
            'columns.*.code' => ['required_with:columns', 'string', 'max:20'],
            'columns.*.column_type' => ['nullable', 'string', 'in:SCORE,SUMMARY'],
            'columns.*.calculation_type' => ['nullable', 'string'],
            'columns.*.weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'columns.*.max_score' => ['nullable', 'numeric', 'min:1'],
            'columns.*.sources' => ['nullable'],
        ]);

        DB::transaction(function () use ($validated, $request, $gradebook) {
            $gradebook->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            if (isset($validated['columns'])) {
                $keptColumnIds = [];
                $columnsByCode = [];

                foreach ($validated['columns'] as $index => $col) {
                    $isSummary = ($col['column_type'] ?? 'SCORE') === 'SUMMARY';
                    $colType = $isSummary ? GradebookColumnType::Summary : GradebookColumnType::Score;
                    $calcType = null;
                    if ($isSummary && ! empty($col['calculation_type'])) {
                        $calcType = match (strtoupper($col['calculation_type'])) {
                            'SUM' => GradebookCalculationType::Sum,
                            'WEIGHTED_AVERAGE' => GradebookCalculationType::WeightedAverage,
                            default => GradebookCalculationType::Average,
                        };
                    }

                    $columnData = [
                        'name' => $col['name'],
                        'code' => strtoupper($col['code']),
                        'column_type' => $colType,
                        'calculation_type' => $calcType,
                        'max_score' => $col['max_score'] ?? 100.00,
                        'weight' => $col['weight'] ?? 10.00,
                        'sort_order' => $index + 1,
                        'is_visible' => true,
                        'is_included_in_average' => true,
                    ];

                    if (! empty($col['id']) && $existingCol = $gradebook->columns()->find($col['id'])) {
                        $existingCol->update($columnData);
                        $keptColumnIds[] = $existingCol->id;
                        $columnsByCode[strtoupper($col['code'])] = $existingCol;
                    } else {
                        $newCol = $gradebook->columns()->create($columnData);
                        $keptColumnIds[] = $newCol->id;
                        $columnsByCode[strtoupper($col['code'])] = $newCol;
                    }
                }

                // Delete columns that were removed in the builder
                $columnsToDelete = $gradebook->columns()->whereNotIn('id', $keptColumnIds)->get();
                foreach ($columnsToDelete as $delCol) {
                    Assessment::where('gradebook_column_id', $delCol->id)->update(['gradebook_column_id' => null]);
                    GradebookScore::where('gradebook_column_id', $delCol->id)->delete();
                    $delCol->delete();
                }

                // Sync summary column sources
                $allColumnsById = $gradebook->columns()->get()->keyBy('id');
                foreach ($validated['columns'] as $col) {
                    $codeUpper = strtoupper($col['code']);
                    if (! isset($columnsByCode[$codeUpper])) {
                        continue;
                    }

                    $summaryCol = $columnsByCode[$codeUpper];
                    $isSummary = ($col['column_type'] ?? 'SCORE') === 'SUMMARY';

                    if ($isSummary) {
                        $sourcesRaw = $col['sources'] ?? [];
                        if (is_string($sourcesRaw)) {
                            $sourcesRaw = array_filter(array_map('trim', explode(',', $sourcesRaw)));
                        }
                        $sourceColIds = [];
                        foreach ($sourcesRaw as $srcKey) {
                            $srcKeyUpper = strtoupper($srcKey);
                            if (isset($columnsByCode[$srcKeyUpper]) && $columnsByCode[$srcKeyUpper]->id !== $summaryCol->id) {
                                $sourceColIds[] = $columnsByCode[$srcKeyUpper]->id;
                            } elseif (is_numeric($srcKey) && $allColumnsById->has((int) $srcKey) && (int) $srcKey !== $summaryCol->id) {
                                $sourceColIds[] = (int) $srcKey;
                            }
                        }
                        $summaryCol->sourceColumns()->sync($sourceColIds);
                    } else {
                        $summaryCol->sourceColumns()->detach();
                    }
                }
            }
        });

        return redirect()->route('teacher.gradebooks.index')->with('success', 'Buku nilai dan struktur kolom berhasil diperbarui.');
    }

    public function destroy(Gradebook $gradebook): RedirectResponse
    {
        Gate::authorize('delete', $gradebook);

        DB::transaction(function () use ($gradebook) {
            $columnIds = $gradebook->columns()->pluck('id');
            Assessment::whereIn('gradebook_column_id', $columnIds)->update(['gradebook_column_id' => null]);
            GradebookScore::whereIn('gradebook_column_id', $columnIds)->delete();
            $gradebook->columns()->delete();
            $gradebook->students()->delete();
            $gradebook->categories()->delete();
            $gradebook->delete();
        });

        return redirect()->route('teacher.gradebooks.index')->with('success', 'Buku nilai berhasil dihapus.');
    }

    public function destroyColumn(Gradebook $gradebook, GradebookColumn $column): RedirectResponse
    {
        Gate::authorize('update', $gradebook);

        if ($column->gradebook_id !== $gradebook->id) {
            abort(403, 'Kolom bukan milik buku nilai ini.');
        }

        DB::transaction(function () use ($column) {
            Assessment::where('gradebook_column_id', $column->id)->update(['gradebook_column_id' => null]);
            GradebookScore::where('gradebook_column_id', $column->id)->delete();
            $column->delete();
        });

        return redirect()->back()->with('success', 'Kolom nilai berhasil dihapus.');
    }

    public function show(Gradebook $gradebook): View
    {
        Gate::authorize('view', $gradebook);

        $gradebook->load([
            'teachingAssignment.schoolClass',
            'teachingAssignment.subject',
            'teachingAssignment.semester.academicYear',
            'categories.columns' => fn ($q) => $q->orderBy('sort_order'),
            'columns.category',
            'columns.sourceColumns',
            'columns' => fn ($q) => $q->orderBy('sort_order'),
            'students.student.user',
        ]);

        $columns = $gradebook->columns;
        $categories = $gradebook->categories;
        $students = $gradebook->students->sortBy('student.full_name');

        // Fetch all scores for this gradebook mapped as [student_id][column_id] => score
        $scoresCollection = GradebookScore::whereIn('gradebook_column_id', $columns->pluck('id'))
            ->whereIn('student_id', $students->pluck('student_id'))
            ->get();

        $scoresMatrix = [];
        foreach ($scoresCollection as $score) {
            $scoresMatrix[$score->student_id][$score->gradebook_column_id] = $score->final_score;
        }

        // Dynamically compute scores for SUMMARY columns based on their sourceColumns
        foreach ($columns as $column) {
            if ($column->column_type === GradebookColumnType::Summary) {
                $sourceColIds = $column->sourceColumns->pluck('id')->all();
                if (empty($sourceColIds)) {
                    $sourceColIds = $columns->filter(fn ($c) => $c->sort_order < $column->sort_order && $c->column_type === GradebookColumnType::Score)->pluck('id')->all();
                }

                foreach ($students as $studentEntry) {
                    $stId = $studentEntry->student_id;
                    $sourceScores = [];
                    $weightedSum = 0;
                    $totalWeight = 0;

                    foreach ($sourceColIds as $sId) {
                        $val = $scoresMatrix[$stId][$sId] ?? null;
                        if ($val !== null && is_numeric($val)) {
                            $scoreFloat = (float) $val;
                            $sourceScores[] = $scoreFloat;

                            $srcCol = $columns->firstWhere('id', $sId);
                            $w = (float) ($srcCol?->weight ?? 1);
                            if ($w <= 0) {
                                $w = 1;
                            }

                            $weightedSum += ($scoreFloat * $w);
                            $totalWeight += $w;
                        }
                    }

                    if (! empty($sourceScores)) {
                        $calculated = match ($column->calculation_type) {
                            GradebookCalculationType::Sum => round(array_sum($sourceScores), 2),
                            GradebookCalculationType::WeightedAverage => $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : round(array_sum($sourceScores) / count($sourceScores), 2),
                            default => round(array_sum($sourceScores) / count($sourceScores), 2),
                        };
                        $scoresMatrix[$stId][$column->id] = $calculated;
                    }
                }
            }
        }

        // Summary calculations per column
        $columnAverages = [];
        foreach ($columns as $column) {
            $colScores = collect();
            foreach ($students as $st) {
                $val = $scoresMatrix[$st->student_id][$column->id] ?? null;
                if ($val !== null && is_numeric($val)) {
                    $colScores->push((float) $val);
                }
            }
            $columnAverages[$column->id] = $colScores->isNotEmpty() ? round($colScores->avg(), 1) : '-';
        }

        return view('teacher.gradebooks.show', compact(
            'gradebook',
            'categories',
            'columns',
            'students',
            'scoresMatrix',
            'columnAverages'
        ));
    }

    public function storeColumn(Request $request, Gradebook $gradebook): RedirectResponse
    {
        Gate::authorize('update', $gradebook);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20'],
            'category_id' => ['nullable', 'exists:gradebook_categories,id'],
            'column_type' => ['required', 'string', 'in:SCORE,SUMMARY,ATTENDANCE_SUMMARY,MANUAL'],
            'max_score' => ['required', 'numeric', 'min:1', 'max:100'],
            'weight' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $highestSortOrder = (int) $gradebook->columns()->max('sort_order');

        $gradebook->columns()->create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'category_id' => $validated['category_id'] ?? null,
            'column_type' => GradebookColumnType::from($validated['column_type']),
            'calculation_type' => $validated['column_type'] === 'SUMMARY' ? GradebookCalculationType::Average : null,
            'max_score' => $validated['max_score'],
            'weight' => $validated['weight'],
            'sort_order' => $highestSortOrder + 1,
            'is_visible' => true,
            'is_included_in_average' => true,
        ]);

        return redirect()->route('teacher.gradebooks.show', $gradebook)->with('success', 'Kolom nilai baru berhasil ditambahkan.');
    }

    public function updateScores(Request $request, Gradebook $gradebook): RedirectResponse
    {
        Gate::authorize('update', $gradebook);

        $scoresData = $request->input('scores', []);
        $teacher = Auth::user()->teacherProfile;

        DB::transaction(function () use ($scoresData, $gradebook, $teacher) {
            foreach ($scoresData as $studentId => $colValues) {
                foreach ($colValues as $columnId => $scoreVal) {
                    $column = GradebookColumn::where('id', $columnId)
                        ->where('gradebook_id', $gradebook->id)
                        ->first();

                    if (! $column) {
                        continue;
                    }

                    if ($scoreVal === null || $scoreVal === '') {
                        GradebookScore::where('gradebook_column_id', $columnId)
                            ->where('student_id', $studentId)
                            ->delete();

                        continue;
                    }

                    $scoreFloat = min((float) $scoreVal, (float) $column->max_score);
                    $scoreFloat = max(0, $scoreFloat);

                    GradebookScore::updateOrCreate(
                        [
                            'gradebook_column_id' => $columnId,
                            'student_id' => $studentId,
                        ],
                        [
                            'raw_score' => $scoreFloat,
                            'final_score' => $scoreFloat,
                            'max_score_snapshot' => $column->max_score,
                            'graded_by' => $teacher->id,
                            'graded_at' => now(),
                        ]
                    );
                }
            }
        });

        return redirect()->route('teacher.gradebooks.show', $gradebook)->with('success', 'Nilai siswa berhasil disimpan.');
    }

    public function export(Gradebook $gradebook): StreamedResponse
    {
        Gate::authorize('view', $gradebook);

        $gradebook->load([
            'teachingAssignment.schoolClass',
            'teachingAssignment.subject',
            'teachingAssignment.semester.academicYear',
            'categories',
            'columns.category',
            'columns.sourceColumns',
            'columns' => fn ($q) => $q->orderBy('sort_order'),
            'students.student',
        ]);

        $assignment = $gradebook->teachingAssignment;
        $schoolProfile = SchoolProfile::first();
        $schoolName = $schoolProfile?->school_name ?? 'SMK NEGERI 2 KARANGANYAR';
        $className = $assignment?->schoolClass?->name ?? '-';
        $semesterName = $assignment?->semester?->name ?? ($assignment?->semester?->semester_number ? 'Semester '.$assignment->semester->semester_number : '-');
        $academicYear = $assignment?->semester?->academicYear?->name ?? '2024/2025';
        $subjectName = $assignment?->subject?->name ?? '-';

        $columns = $gradebook->columns;
        $students = $gradebook->students->sortBy('student.full_name');

        $scoresCollection = GradebookScore::whereIn('gradebook_column_id', $columns->pluck('id'))
            ->whereIn('student_id', $students->pluck('student_id'))
            ->get();

        $scoresMatrix = [];
        foreach ($scoresCollection as $s) {
            $scoresMatrix[$s->student_id][$s->gradebook_column_id] = $s->final_score;
        }

        // Dynamically compute scores for SUMMARY columns
        foreach ($columns as $column) {
            if ($column->column_type === GradebookColumnType::Summary) {
                $sourceColIds = $column->sourceColumns->pluck('id')->all();
                if (empty($sourceColIds)) {
                    $sourceColIds = $columns->filter(fn ($c) => $c->sort_order < $column->sort_order && $c->column_type === GradebookColumnType::Score)->pluck('id')->all();
                }

                foreach ($students as $studentEntry) {
                    $stId = $studentEntry->student_id;
                    $sourceScores = [];
                    $weightedSum = 0;
                    $totalWeight = 0;

                    foreach ($sourceColIds as $sId) {
                        $val = $scoresMatrix[$stId][$sId] ?? null;
                        if ($val !== null && is_numeric($val)) {
                            $scoreFloat = (float) $val;
                            $sourceScores[] = $scoreFloat;

                            $srcCol = $columns->firstWhere('id', $sId);
                            $w = (float) ($srcCol?->weight ?? 1);
                            if ($w <= 0) {
                                $w = 1;
                            }

                            $weightedSum += ($scoreFloat * $w);
                            $totalWeight += $w;
                        }
                    }

                    if (! empty($sourceScores)) {
                        $calculated = match ($column->calculation_type) {
                            GradebookCalculationType::Sum => round(array_sum($sourceScores), 2),
                            GradebookCalculationType::WeightedAverage => $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : round(array_sum($sourceScores) / count($sourceScores), 2),
                            default => round(array_sum($sourceScores) / count($sourceScores), 2),
                        };
                        $scoresMatrix[$stId][$column->id] = $calculated;
                    }
                }
            }
        }

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Nilai');
        $sheet->setShowGridLines(true);

        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        // Base columns: A = No, B = Nama
        $baseColCount = 2;
        $totalCols = $baseColCount + max(1, $columns->count());
        $lastColLetter = Coordinate::stringFromColumnIndex($totalCols);

        // 1. Title Block (Row 2)
        $sheet->setCellValue('A2', 'REKAP NILAI SISWA');
        $sheet->mergeCells("A2:{$lastColLetter}2");
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(28);

        // 2. Metadata Block (Rows 4-7)
        $sheet->setCellValue('C4', 'Nama Sekolah');
        $sheet->setCellValue('D4', ': '.strtoupper($schoolName));
        $sheet->getStyle('C4:D4')->getFont()->setBold(true)->setSize(10);

        $sheet->setCellValue('A5', 'Kelas/ Semester');
        $sheet->setCellValue('C5', ': '.$className.' / '.$semesterName);
        $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('C5')->getFont()->setBold(true)->setSize(10);

        $sheet->setCellValue('A6', 'Tahun Ajaran');
        $sheet->setCellValue('C6', ': '.$academicYear);
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('C6')->getFont()->setBold(true)->setSize(10);

        $sheet->setCellValue('A7', 'Mata Pelajaran');
        $sheet->setCellValue('C7', ': '.$subjectName);
        $sheet->getStyle('A7')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('C7')->getFont()->setBold(true)->setSize(10);

        $sheet->getRowDimension(4)->setRowHeight(18);
        $sheet->getRowDimension(5)->setRowHeight(18);
        $sheet->getRowDimension(6)->setRowHeight(18);
        $sheet->getRowDimension(7)->setRowHeight(18);

        // 3. Table Header Structure (Rows 9-10)
        $headerRow1 = 9;
        $headerRow2 = 10;
        $sheet->getRowDimension($headerRow1)->setRowHeight(24);
        $sheet->getRowDimension($headerRow2)->setRowHeight(20);

        // Col A: No
        $sheet->setCellValue('A'.$headerRow1, 'No');
        $sheet->mergeCells("A{$headerRow1}:A{$headerRow2}");

        // Col B: Nama
        $sheet->setCellValue('B'.$headerRow1, 'Nama');
        $sheet->mergeCells("B{$headerRow1}:B{$headerRow2}");

        $columnGroups = [];

        if ($columns->isEmpty()) {
            $sheet->setCellValue('C'.$headerRow1, 'Nilai');
            $sheet->mergeCells("C{$headerRow1}:C{$headerRow2}");
        } else {
            foreach ($columns as $index => $col) {
                $groupName = null;
                if ($col->category) {
                    $groupName = $col->category->name;
                } else {
                    $codeUpper = strtoupper($col->code);
                    $nameUpper = strtoupper($col->name);
                    if ($col->column_type === GradebookColumnType::Summary) {
                        $groupName = null;
                    } elseif (preg_match('/^(TUGAS|T\d|TP)/i', $col->code) || str_starts_with($nameUpper, 'TUGAS')) {
                        $groupName = 'Nilai Harian';
                    } elseif (preg_match('/^(UH\d|ULANGAN|UH)/i', $col->code) || str_starts_with($nameUpper, 'ULANGAN')) {
                        $groupName = 'Ulangan Harian';
                    } else {
                        $groupName = null;
                    }
                }

                $colIndex = $baseColCount + 1 + $index;
                $colLetter = Coordinate::stringFromColumnIndex($colIndex);

                $columnGroups[] = [
                    'column' => $col,
                    'group_name' => $groupName,
                    'col_index' => $colIndex,
                    'col_letter' => $colLetter,
                ];
            }

            $i = 0;
            while ($i < count($columnGroups)) {
                $curr = $columnGroups[$i];
                $groupName = $curr['group_name'];

                if ($groupName !== null) {
                    $span = 1;
                    while ($i + $span < count($columnGroups) && $columnGroups[$i + $span]['group_name'] === $groupName) {
                        $span++;
                    }

                    $startLetter = $curr['col_letter'];
                    $endLetter = $columnGroups[$i + $span - 1]['col_letter'];

                    $sheet->setCellValue($startLetter.$headerRow1, $groupName);
                    if ($span > 1) {
                        $sheet->mergeCells("{$startLetter}{$headerRow1}:{$endLetter}{$headerRow1}");
                    }

                    for ($s = 0; $s < $span; $s++) {
                        $item = $columnGroups[$i + $s];
                        $subText = $item['column']->code ?: $item['column']->name;
                        $sheet->setCellValue($item['col_letter'].$headerRow2, $subText);
                    }

                    $i += $span;
                } else {
                    $colLetter = $curr['col_letter'];
                    $dispName = $curr['column']->name;
                    if (strlen($dispName) > 20 && ! empty($curr['column']->code)) {
                        $dispName = $curr['column']->code;
                    }
                    $sheet->setCellValue($colLetter.$headerRow1, $dispName);
                    $sheet->mergeCells("{$colLetter}{$headerRow1}:{$colLetter}{$headerRow2}");
                    $i++;
                }
            }
        }

        // 4. Student Data Rows (Rows 11+)
        $currentRow = 11;
        $no = 1;

        if ($students->isEmpty()) {
            $sheet->getRowDimension($currentRow)->setRowHeight(20);
            $sheet->setCellValue('A'.$currentRow, '-');
            $sheet->setCellValue('B'.$currentRow, 'Belum ada data siswa');
            $currentRow++;
        } else {
            foreach ($students as $studentEntry) {
                $student = $studentEntry->student;
                $studentId = $studentEntry->student_id;
                $studentName = $student?->full_name ?? 'Siswa';

                $sheet->getRowDimension($currentRow)->setRowHeight(20);
                $sheet->setCellValue('A'.$currentRow, $no++);
                $sheet->setCellValue('B'.$currentRow, $studentName);

                foreach ($columnGroups as $cg) {
                    $colId = $cg['column']->id;
                    $score = $scoresMatrix[$studentId][$colId] ?? null;

                    if ($score !== null && $score !== '') {
                        $numericScore = (float) $score;
                        $formattedScore = (floor($numericScore) == $numericScore) ? (int) $numericScore : round($numericScore, 1);
                        $sheet->setCellValue($cg['col_letter'].$currentRow, $formattedScore);
                    } else {
                        $sheet->setCellValue($cg['col_letter'].$currentRow, '');
                    }
                }

                $currentRow++;
            }
        }

        // 5. Footer Row (Rata-rata Kelas)
        $footerRow = $currentRow;
        $sheet->getRowDimension($footerRow)->setRowHeight(22);
        $sheet->setCellValue('A'.$footerRow, 'Rata- rata Kelas');
        $sheet->mergeCells("A{$footerRow}:B{$footerRow}");

        foreach ($columnGroups as $cg) {
            $colId = $cg['column']->id;
            $colScores = collect();
            foreach ($students as $studentEntry) {
                $val = $scoresMatrix[$studentEntry->student_id][$colId] ?? null;
                if ($val !== null && $val !== '') {
                    $colScores->push((float) $val);
                }
            }

            if ($colScores->isNotEmpty()) {
                $avg = round($colScores->avg(), 1);
                $formattedAvg = (floor($avg) == $avg) ? (int) $avg : $avg;
                $sheet->setCellValue($cg['col_letter'].$footerRow, $formattedAvg);
            } else {
                $sheet->setCellValue($cg['col_letter'].$footerRow, '');
            }
        }

        // 6. Styling & Borders
        $tableRange = "A{$headerRow1}:{$lastColLetter}{$footerRow}";

        $sheet->getStyle($tableRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $headerRange = "A{$headerRow1}:{$lastColLetter}{$headerRow2}";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        for ($r = 11; $r < $footerRow; $r++) {
            $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
            if ($columns->isNotEmpty()) {
                $sheet->getStyle("C{$r}:{$lastColLetter}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            }
        }

        $sheet->getStyle("A{$footerRow}:{$lastColLetter}{$footerRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$footerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        if ($columns->isNotEmpty()) {
            $sheet->getStyle("C{$footerRow}:{$lastColLetter}{$footerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        }

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(28);

        foreach ($columnGroups as $cg) {
            $colLetter = $cg['col_letter'];
            $codeLen = strlen($cg['column']->code ?: '');
            $nameLen = strlen($cg['column']->name ?: '');
            $calcWidth = max(7, min(15, max($codeLen + 3, (int) ($nameLen * 0.9))));
            $sheet->getColumnDimension($colLetter)->setWidth($calcWidth);
        }

        $cleanClass = preg_replace('/[^A-Za-z0-9_-]/', '_', $className);
        $cleanSubject = preg_replace('/[^A-Za-z0-9_-]/', '_', $subjectName);
        $fileName = 'Rekap_Nilai_'.$cleanClass.'_'.$cleanSubject.'_'.date('Ymd_His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
