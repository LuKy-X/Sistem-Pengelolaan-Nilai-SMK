<?php

namespace App\Ai\Tools;

use App\Enums\AttendanceStatus;
use App\Models\JournalAttendance;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Carbon;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Provides attendance summary statistics.
 *
 * Returns only aggregated counts, not individual student attendance records.
 */
class GetAttendanceSummary implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Mengambil ringkasan kehadiran siswa (hadir, sakit, izin, alfa). Bisa difilter berdasarkan kelas, tanggal, atau jurusan. Hanya menampilkan data agregat, bukan per siswa.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $classSearch = $request['class'] ?? null;
        $dateSearch = $request['date'] ?? null;
        $departmentSearch = $request['department'] ?? null;

        // Parse date
        $targetDate = null;
        if ($dateSearch) {
            $lower = strtolower(trim($dateSearch));
            if (in_array($lower, ['hari ini', 'today', 'sekarang'])) {
                $targetDate = Carbon::today();
            } else {
                try {
                    $targetDate = Carbon::parse($dateSearch);
                } catch (\Throwable) {
                    $targetDate = Carbon::today();
                }
            }
        }

        $query = JournalAttendance::query()
            ->join('class_journals', 'class_journals.id', '=', 'journal_attendances.journal_id')
            ->join('teaching_assignments', 'teaching_assignments.id', '=', 'class_journals.teaching_assignment_id')
            ->join('classes', 'classes.id', '=', 'teaching_assignments.class_id');

        if ($targetDate) {
            $query->whereDate('class_journals.date', $targetDate);
        }

        if ($classSearch) {
            $query->where(function ($q) use ($classSearch) {
                $q->where('classes.name', 'like', "%{$classSearch}%")
                    ->orWhere('classes.code', 'like', "%{$classSearch}%");
            });
        }

        if ($departmentSearch) {
            $query->join('departments', 'departments.id', '=', 'classes.department_id')
                ->where(function ($q) use ($departmentSearch) {
                    $q->where('departments.name', 'like', "%{$departmentSearch}%")
                        ->orWhere('departments.short_name', 'like', "%{$departmentSearch}%")
                        ->orWhere('departments.code', 'like', "%{$departmentSearch}%");
                });
        }

        $totals = (clone $query)
            ->selectRaw('
                COUNT(*) as total_records,
                SUM(CASE WHEN journal_attendances.status = ? THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN journal_attendances.status = ? THEN 1 ELSE 0 END) as sick,
                SUM(CASE WHEN journal_attendances.status = ? THEN 1 ELSE 0 END) as permitted,
                SUM(CASE WHEN journal_attendances.status = ? THEN 1 ELSE 0 END) as absent
            ', [
                AttendanceStatus::Present->value,
                AttendanceStatus::Sick->value,
                AttendanceStatus::Permitted->value,
                AttendanceStatus::Absent->value,
            ])
            ->first();

        if (! $totals || (int) $totals->total_records === 0) {
            $dateLabel = $targetDate ? $targetDate->translatedFormat('d F Y') : 'periode tersebut';

            return json_encode([
                'found' => false,
                'message' => "Tidak ada data kehadiran untuk {$dateLabel}."
                    .($classSearch ? " Kelas: {$classSearch}." : ''),
            ]);
        }

        $present = (int) $totals->present;
        $sick = (int) $totals->sick;
        $permitted = (int) $totals->permitted;
        $absent = (int) $totals->absent;
        $total = (int) $totals->total_records;
        $attendanceRate = $total > 0 ? round(($present / $total) * 100, 1) : 0;

        return json_encode([
            'found' => true,
            'date' => $targetDate?->format('d F Y') ?? 'semua periode',
            'class' => $classSearch,
            'summary' => [
                'total' => $total,
                'present' => $present,
                'sick' => $sick,
                'permitted' => $permitted,
                'absent' => $absent,
                'attendance_rate_percent' => $attendanceRate,
            ],
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'class' => $schema->string()
                ->description('Nama atau kode kelas. Contoh: "XII RPL C".'),
            'date' => $schema->string()
                ->description('Tanggal kehadiran. Contoh: "hari ini", "2025-01-15".'),
            'department' => $schema->string()
                ->description('Nama atau kode jurusan.'),
        ];
    }
}
