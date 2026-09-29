<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LessonPeriod;
use App\Models\SchoolClass;
use App\Models\TeachingAssignment;
use App\Models\TeachingSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $selectedClassId = $request->query('class_id');
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();
        $selectedClass = $selectedClassId ? SchoolClass::find($selectedClassId) : $classes->first();

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

        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
        ];

        return view('admin.academic.schedules.index', compact(
            'classes',
            'selectedClass',
            'periods',
            'schedules',
            'assignments',
            'days'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'teaching_assignment_id' => ['required', 'exists:teaching_assignments,id'],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'start_period_id' => ['required', 'exists:lesson_periods,id'],
            'end_period_id' => ['required', 'exists:lesson_periods,id'],
            'room' => ['nullable', 'string', 'max:50'],
        ]);

        TeachingSchedule::create($validated);

        $assignment = TeachingAssignment::find($validated['teaching_assignment_id']);

        return redirect()->route('admin.academic.schedules.index', ['class_id' => $assignment->class_id])
            ->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    public function destroy(TeachingSchedule $schedule): RedirectResponse
    {
        $classId = $schedule->teachingAssignment?->class_id;
        $schedule->delete();

        return redirect()->route('admin.academic.schedules.index', ['class_id' => $classId])
            ->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }

    public function storePeriod(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period_number' => ['required', 'integer', 'unique:lesson_periods,period_number'],
            'label' => ['required', 'string', 'max:50'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        LessonPeriod::create($validated);

        return redirect()->back()->with('success', 'Jam pelajaran berhasil ditambahkan.');
    }
}
