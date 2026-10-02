<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LessonPeriod;
use App\Models\SchoolClass;
use App\Models\TeachingAssignment;
use App\Models\TeachingSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Tampilkan grid jadwal mingguan (konsep spreadsheet / kolom Excel).
     */
    public function index(Request $request): View|JsonResponse
    {
        $selectedClassId = $request->query('class_id');
        $classes = SchoolClass::where('is_active', true)->with('department')->orderBy('name')->get();

        if ($classes->isEmpty()) {
            $classes = SchoolClass::with('department')->orderBy('name')->get();
        }

        $selectedClass = $selectedClassId ? SchoolClass::with('department')->find($selectedClassId) : $classes->first();

        $periods = LessonPeriod::orderBy('period_number')->get();

        $schedules = collect();
        $assignments = collect();

        if ($selectedClass) {
            $schedules = TeachingSchedule::with([
                'teachingAssignment.subject',
                'teachingAssignment.teacher',
                'startPeriod',
                'endPeriod',
            ])
                ->whereHas('teachingAssignment', function ($query) use ($selectedClass) {
                    $query->where('class_id', $selectedClass->id);
                })
                ->get();

            $assignments = TeachingAssignment::with(['subject', 'teacher'])
                ->where('class_id', $selectedClass->id)
                ->where('is_active', true)
                ->get();
        }

        // Master 6 hari (Senin s/d Sabtu)
        $allDays = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        // Default 5 hari (Senin - Jumat), bisa ditambah/diatur sampai 6 hari (Sabtu)
        $hasSaturdaySchedule = $schedules->where('day_of_week', 6)->isNotEmpty();
        $requestedDays = (int) $request->query('days_count', $hasSaturdaySchedule ? 6 : 5);
        $daysCount = max(5, min(6, $requestedDays));

        $days = array_slice($allDays, 0, $daysCount, true);

        if ($request->wantsJson()) {
            return response()->json([
                'selected_class' => $selectedClass,
                'periods' => $periods,
                'days' => $days,
                'schedules' => $schedules,
                'assignments' => $assignments,
            ]);
        }

        return view('admin.academic.schedules.index', compact(
            'classes',
            'selectedClass',
            'periods',
            'schedules',
            'assignments',
            'days',
            'allDays',
            'daysCount'
        ));
    }

    /**
     * Simpan jadwal pelajaran baru (single period atau multi period).
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'teaching_assignment_id' => ['required', 'exists:teaching_assignments,id'],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'start_period_id' => ['required', 'exists:lesson_periods,id'],
            'end_period_id' => ['required', 'exists:lesson_periods,id'],
            'room' => ['nullable', 'string', 'max:50'],
        ]);

        // Pastikan urutan start_period <= end_period berdasarkan period_number
        $startP = LessonPeriod::find($validated['start_period_id']);
        $endP = LessonPeriod::find($validated['end_period_id']);

        if ($startP && $endP && $startP->period_number > $endP->period_number) {
            $tmp = $validated['start_period_id'];
            $validated['start_period_id'] = $validated['end_period_id'];
            $validated['end_period_id'] = $tmp;
        }

        $schedule = TeachingSchedule::create($validated);
        $schedule->load(['teachingAssignment.subject', 'teachingAssignment.teacher', 'startPeriod', 'endPeriod']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal pelajaran berhasil ditambahkan.',
                'schedule' => $schedule,
            ]);
        }

        $assignment = TeachingAssignment::find($validated['teaching_assignment_id']);

        return redirect()->route('admin.academic.schedules.index', [
            'class_id' => $assignment?->class_id,
            'days_count' => $request->input('days_count', 5),
        ])->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    /**
     * Perbarui jadwal pelajaran yang sudah ada (melalui klik 2x / edit sel).
     */
    public function update(Request $request, TeachingSchedule $schedule): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'teaching_assignment_id' => ['required', 'exists:teaching_assignments,id'],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'start_period_id' => ['required', 'exists:lesson_periods,id'],
            'end_period_id' => ['required', 'exists:lesson_periods,id'],
            'room' => ['nullable', 'string', 'max:50'],
        ]);

        $startP = LessonPeriod::find($validated['start_period_id']);
        $endP = LessonPeriod::find($validated['end_period_id']);

        if ($startP && $endP && $startP->period_number > $endP->period_number) {
            $tmp = $validated['start_period_id'];
            $validated['start_period_id'] = $validated['end_period_id'];
            $validated['end_period_id'] = $tmp;
        }

        $schedule->update($validated);
        $schedule->load(['teachingAssignment.subject', 'teachingAssignment.teacher', 'startPeriod', 'endPeriod']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal pelajaran berhasil diperbarui.',
                'schedule' => $schedule,
            ]);
        }

        $assignment = TeachingAssignment::find($validated['teaching_assignment_id']);

        return redirect()->route('admin.academic.schedules.index', [
            'class_id' => $assignment?->class_id,
            'days_count' => $request->input('days_count', 5),
        ])->with('success', 'Jadwal pelajaran berhasil diperbarui.');
    }

    /**
     * Hapus jadwal pelajaran.
     */
    public function destroy(Request $request, TeachingSchedule $schedule): RedirectResponse|JsonResponse
    {
        $classId = $schedule->teachingAssignment?->class_id;
        $schedule->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal pelajaran berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.academic.schedules.index', [
            'class_id' => $classId,
            'days_count' => $request->input('days_count', 5),
        ])->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }

    /**
     * Tambah jam pelajaran baru (baris bawah pada konsep spreadsheet).
     */
    public function storePeriod(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'period_number' => ['required', 'integer', 'unique:lesson_periods,period_number'],
            'label' => ['required', 'string', 'max:50'],
            'start_time' => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'end_time' => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
        ]);

        // Standarisasi format jam ke HH:MM:00
        if (strlen($validated['start_time']) === 5) {
            $validated['start_time'] .= ':00';
        }
        if (strlen($validated['end_time']) === 5) {
            $validated['end_time'] .= ':00';
        }

        $period = LessonPeriod::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jam pelajaran baru berhasil ditambahkan.',
                'period' => $period,
            ]);
        }

        return redirect()->back()->with('success', 'Jam pelajaran berhasil ditambahkan.');
    }

    /**
     * Perbarui jam pelajaran.
     */
    public function updatePeriod(Request $request, LessonPeriod $period): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'period_number' => ['required', 'integer', 'unique:lesson_periods,period_number,'.$period->id],
            'label' => ['required', 'string', 'max:50'],
            'start_time' => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'end_time' => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
        ]);

        if (strlen($validated['start_time']) === 5) {
            $validated['start_time'] .= ':00';
        }
        if (strlen($validated['end_time']) === 5) {
            $validated['end_time'] .= ':00';
        }

        $period->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jam pelajaran berhasil diperbarui.',
                'period' => $period,
            ]);
        }

        return redirect()->back()->with('success', 'Jam pelajaran berhasil diperbarui.');
    }

    /**
     * Hapus jam pelajaran (hanya jika belum terpakai pada jadwal).
     */
    public function destroyPeriod(Request $request, LessonPeriod $period): RedirectResponse|JsonResponse
    {
        if ($period->schedulesAsStart()->exists() || $period->schedulesAsEnd()->exists()) {
            $msg = 'Jam pelajaran ini tidak dapat dihapus karena masih digunakan pada jadwal pelajaran.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }

            return redirect()->back()->with('error', $msg);
        }

        $period->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jam pelajaran berhasil dihapus.',
            ]);
        }

        return redirect()->back()->with('success', 'Jam pelajaran berhasil dihapus.');
    }
}
