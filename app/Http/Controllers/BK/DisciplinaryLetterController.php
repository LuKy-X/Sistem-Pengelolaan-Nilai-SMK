<?php

namespace App\Http\Controllers\BK;

use App\Enums\DisciplinaryLetterType;
use App\Http\Controllers\BK\Concerns\HandlesDisciplinePoints;
use App\Http\Controllers\BK\Concerns\RecordsAuditTrail;
use App\Http\Controllers\Controller;
use App\Http\Requests\BK\StoreDisciplinaryLetterRequest;
use App\Models\DisciplinaryLetter;
use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DisciplinaryLetterController extends Controller
{
    use HandlesDisciplinePoints, RecordsAuditTrail;

    public const STATUSES = ['ACTIVE', 'RESOLVED', 'REVOKED'];

    public function index(Request $request): View
    {
        $academicYear = $this->activeAcademicYear();
        $academicYearId = $academicYear?->id;
        $setting = $this->disciplineSetting($academicYearId);

        $typeFilter = $request->query('type');
        $statusFilter = in_array($request->query('status'), self::STATUSES, true) ? $request->query('status') : null;
        $studentIdFilter = $request->integer('student_id') ?: null;
        $search = trim((string) $request->query('q', ''));

        $baseQuery = DisciplinaryLetter::query()
            ->with(['student.currentEnrollment.schoolClass', 'issuer', 'academicYear']);

        $typeCounts = (clone $baseQuery)
            ->when($academicYearId !== null, fn (Builder $query) => $query->where('academic_year_id', $academicYearId))
            ->reorder()
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $letters = $baseQuery
            ->when($academicYearId !== null, fn (Builder $query) => $query->where('academic_year_id', $academicYearId))
            ->when($studentIdFilter !== null, fn (Builder $query) => $query->where('student_id', $studentIdFilter))
            ->when(in_array($typeFilter, array_column(DisciplinaryLetterType::cases(), 'value'), true),
                fn (Builder $query) => $query->where('type', $typeFilter))
            ->when($statusFilter !== null, fn (Builder $query) => $query->where('status', $statusFilter))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->whereHas('student', fn (Builder $studentQuery) => $studentQuery
                    ->where('full_name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%"));
            })
            ->orderByDesc('issued_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $students = StudentProfile::query()
            ->with('currentEnrollment.schoolClass')
            ->where('status', 'ACTIVE')
            ->orderBy('full_name')
            ->get();

        $balances = $this->pointBalanceMap($students->pluck('id')->all(), $setting, $academicYearId);

        $pointHints = $students->mapWithKeys(fn (StudentProfile $student) => [
            $student->id => [
                'balance' => $balances[$student->id],
                'suggested' => $this->suggestedLetterType($balances[$student->id], $setting)?->value,
            ],
        ]);

        return view('bk.letters.index', [
            'letters' => $letters,
            'students' => $students,
            'pointHints' => $pointHints,
            'setting' => $setting,
            'academicYear' => $academicYear,
            'typeCounts' => $typeCounts,
            'typeFilter' => $typeFilter,
            'statusFilter' => $statusFilter,
            'statuses' => self::STATUSES,
            'studentIdFilter' => $studentIdFilter,
            'search' => $search,
        ]);
    }

    public function store(StoreDisciplinaryLetterRequest $request): RedirectResponse
    {
        $academicYear = $this->activeAcademicYear();

        if ($academicYear === null) {
            return back()->with('error', 'Belum ada tahun ajaran aktif. Hubungi admin untuk mengatur tahun ajaran.');
        }

        $validated = $request->validated();
        $type = DisciplinaryLetterType::from($validated['type']);

        $documentPath = $request->file('document')?->store('disciplinary-letters', 'public');

        $letter = DB::transaction(function () use ($validated, $type, $documentPath, $academicYear, $request) {
            $letter = DisciplinaryLetter::create([
                'student_id' => $validated['student_id'],
                'academic_year_id' => $academicYear->id,
                'type' => $type,
                'reason' => $validated['reason'],
                'issued_at' => $validated['issued_at'],
                'issued_by' => $request->user()->getAuthIdentifier(),
                'notes' => $validated['notes'] ?? null,
                'document_path' => $documentPath,
                'status' => 'ACTIVE',
            ]);

            $this->audit('DISCIPLINARY_LETTER_ISSUED', $letter, [
                'student_id' => $letter->student_id,
                'type' => $type->value,
                'issued_at' => $letter->issued_at?->toDateString(),
            ]);

            return $letter;
        });

        return redirect()
            ->route('counselor.disciplinary-letters.index', ['student_id' => $letter->student_id])
            ->with('success', "Surat Peringatan {$type->value} untuk {$letter->load('student')->student?->full_name} berhasil diterbitkan.");
    }

    public function show(DisciplinaryLetter $letter): View
    {
        $letter->load(['student.currentEnrollment.schoolClass', 'issuer', 'academicYear']);

        return view('bk.letters.show', [
            'letter' => $letter,
        ]);
    }

    public function destroy(DisciplinaryLetter $letter): RedirectResponse
    {
        $studentId = $letter->student_id;
        $type = $letter->type->value;

        $this->audit('DISCIPLINARY_LETTER_REVOKED', $letter, [], [
            'student_id' => $studentId,
            'type' => $type,
        ]);

        if ($letter->document_path !== null) {
            Storage::disk('public')->delete($letter->document_path);
        }

        $letter->delete();

        return redirect()
            ->route('counselor.disciplinary-letters.index', ['student_id' => $studentId])
            ->with('success', "Surat Peringatan {$type} dicabut dan dihapus dari riwayat.");
    }
}
