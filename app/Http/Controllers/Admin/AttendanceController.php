<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\JournalAttendance;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->query('date', today()->toDateString());
        $classId = $request->query('class_id');
        $status = $request->query('status');

        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();

        $query = JournalAttendance::with([
            'student',
            'journal.teachingAssignment.schoolClass',
            'journal.teachingAssignment.subject',
            'journal.teachingAssignment.teacher',
        ])
            ->whereHas('journal', function ($q) use ($date, $classId) {
                $q->whereDate('journal_date', $date);
                if ($classId) {
                    $q->whereHas('teachingAssignment', function ($ta) use ($classId) {
                        $ta->where('class_id', $classId);
                    });
                }
            });

        if ($status) {
            $query->where('status', $status);
        }

        $attendances = $query->latest()->paginate(20)->withQueryString();

        // Rekapitulasi status hari ini
        $baseStatQuery = JournalAttendance::whereHas('journal', function ($q) use ($date, $classId) {
            $q->whereDate('journal_date', $date);
            if ($classId) {
                $q->whereHas('teachingAssignment', function ($ta) use ($classId) {
                    $ta->where('class_id', $classId);
                });
            }
        });

        $stats = [
            'total' => (clone $baseStatQuery)->count(),
            'hadir' => (clone $baseStatQuery)->where('status', 'PRESENT')->count(),
            'sakit' => (clone $baseStatQuery)->where('status', 'SICK')->count(),
            'izin' => (clone $baseStatQuery)->where('status', 'PERMIT')->count(),
            'alpha' => (clone $baseStatQuery)->where('status', 'ABSENT')->count(),
        ];

        return view('admin.attendance.index', compact(
            'attendances',
            'classes',
            'date',
            'classId',
            'status',
            'stats'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $date = $request->query('date', today()->toDateString());
        $classId = $request->query('class_id');

        $fileName = 'rekap_presensi_'.$date.'.csv';

        $attendances = JournalAttendance::with([
            'student',
            'journal.teachingAssignment.schoolClass',
            'journal.teachingAssignment.subject',
            'journal.teachingAssignment.teacher',
        ])
            ->whereHas('journal', function ($q) use ($date, $classId) {
                $q->whereDate('journal_date', $date);
                if ($classId) {
                    $q->whereHas('teachingAssignment', function ($ta) use ($classId) {
                        $ta->where('class_id', $classId);
                    });
                }
            })
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        return response()->stream(function () use ($attendances) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Tanggal', 'Kelas', 'NIS', 'Nama Siswa', 'Status', 'Mata Pelajaran', 'Guru', 'Catatan']);

            foreach ($attendances as $index => $row) {
                $statusVal = $row->status instanceof AttendanceStatus ? $row->status->value : $row->status;
                fputcsv($file, [
                    $index + 1,
                    $row->journal?->journal_date?->format('Y-m-d') ?? '-',
                    $row->journal?->teachingAssignment?->schoolClass?->name ?? '-',
                    $row->student?->nis ?? '-',
                    $row->student?->full_name ?? '-',
                    $statusVal,
                    $row->journal?->teachingAssignment?->subject?->name ?? '-',
                    $row->journal?->teachingAssignment?->teacher?->full_name ?? '-',
                    $row->note ?? '-',
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }
}
