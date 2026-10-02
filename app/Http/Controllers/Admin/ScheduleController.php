<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LessonPeriod;
use App\Models\SchoolClass;
use App\Models\SchoolProfile;
use App\Models\TeachingAssignment;
use App\Models\TeachingSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

        $periods = LessonPeriod::orderBy('sort_order')->orderBy('start_time')->get();

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

            $lessonSortOrders = $periods->where('is_break', false)->pluck('sort_order')->flip();

            $assignments = TeachingAssignment::with([
                'subject',
                'teacher',
                'schedules.startPeriod',
                'schedules.endPeriod',
            ])
                ->where('class_id', $selectedClass->id)
                ->where('is_active', true)
                ->get()
                ->map(function ($a) use ($lessonSortOrders) {
                    $used = $a->schedules->sum(function ($s) use ($lessonSortOrders) {
                        $startSort = $s->startPeriod?->sort_order ?? 1;
                        $endSort = $s->endPeriod?->sort_order ?? $startSort;
                        $minSort = min($startSort, $endSort);
                        $maxSort = max($startSort, $endSort);

                        $count = 0;
                        for ($i = $minSort; $i <= $maxSort; $i++) {
                            if (isset($lessonSortOrders[$i])) {
                                $count++;
                            }
                        }

                        return $count;
                    });
                    $a->scheduled_hours = $used;
                    $a->remaining_hours = max(0, $a->weekly_hours - $used);

                    return $a;
                });
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

        // Peta jam yang sudah terisi per hari untuk rombel aktif
        $occupiedByDay = [];
        foreach ($allDays as $dNum => $dName) {
            $occupiedByDay[$dNum] = [];
        }
        foreach ($schedules as $s) {
            $startSort = $s->startPeriod?->sort_order ?? 1;
            $endSort = $s->endPeriod?->sort_order ?? $startSort;
            $minSort = min($startSort, $endSort);
            $maxSort = max($startSort, $endSort);
            foreach ($periods as $p) {
                if ($p->sort_order >= $minSort && $p->sort_order <= $maxSort) {
                    $occupiedByDay[$s->day_of_week][$p->id] = [
                        'schedule_id' => $s->id,
                        'subject' => $s->teachingAssignment?->subject?->name,
                        'teacher' => $s->teachingAssignment?->teacher?->full_name,
                        'period_number' => $p->period_number,
                        'label' => $p->label,
                    ];
                }
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'selected_class' => $selectedClass,
                'periods' => $periods,
                'days' => $days,
                'schedules' => $schedules,
                'assignments' => $assignments,
                'occupied_by_day' => $occupiedByDay,
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
            'daysCount',
            'occupiedByDay'
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

        $startP = LessonPeriod::findOrFail($validated['start_period_id']);
        $endP = LessonPeriod::findOrFail($validated['end_period_id']);

        if ($startP->sort_order > $endP->sort_order) {
            $tmp = $validated['start_period_id'];
            $validated['start_period_id'] = $validated['end_period_id'];
            $validated['end_period_id'] = $tmp;
            $tmpP = $startP;
            $startP = $endP;
            $endP = $tmpP;
        }

        $minSort = min($startP->sort_order, $endP->sort_order);
        $maxSort = max($startP->sort_order, $endP->sort_order);

        // 1. Validasi: Jangan izinkan jadwal bertabrakan / melewati jam istirahat
        $hasBreak = LessonPeriod::where('is_break', true)
            ->whereBetween('sort_order', [$minSort, $maxSort])
            ->first();

        if ($hasBreak) {
            $msg = 'Tidak dapat menempatkan jadwal pada rentang jam istirahat.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->withInput()->with('error', $msg);
        }

        $assignment = TeachingAssignment::with(['teacher', 'subject', 'schoolClass'])->findOrFail($validated['teaching_assignment_id']);

        // 2. Validasi bentrok / tumpang tindih (overlap) dengan jadwal lain di kelas ini pada hari tersebut
        $conflict = TeachingSchedule::whereHas('teachingAssignment', function ($q) use ($assignment) {
            $q->where('class_id', $assignment->class_id);
        })
            ->where('day_of_week', $validated['day_of_week'])
            ->where(function ($q) use ($minSort, $maxSort) {
                $q->whereHas('startPeriod', fn ($sp) => $sp->where('sort_order', '<=', $maxSort))
                    ->whereHas('endPeriod', fn ($ep) => $ep->where('sort_order', '>=', $minSort));
            })
            ->with(['teachingAssignment.subject', 'teachingAssignment.teacher', 'startPeriod', 'endPeriod'])
            ->first();

        if ($conflict) {
            $cSub = $conflict->teachingAssignment?->subject?->name ?? 'Mata Pelajaran lain';
            $cTea = $conflict->teachingAssignment?->teacher?->full_name ?? 'Guru lain';
            $cStart = $conflict->startPeriod?->period_number ? "Jam ke-{$conflict->startPeriod->period_number}" : $conflict->startPeriod?->label;
            $cEnd = $conflict->endPeriod?->period_number ? "Jam ke-{$conflict->endPeriod->period_number}" : $conflict->endPeriod?->label;
            $cRange = $cStart == $cEnd ? $cStart : "{$cStart} s/d {$cEnd}";
            $msg = "Bentrok jadwal! Jam tersebut sudah terisi oleh {$cSub} ({$cTea}) pada {$cRange}. Silakan pilih jam lain yang masih kosong.";

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->withInput()->with('error', $msg);
        }

        // 3. Validasi batasan jam mengajar per minggu untuk rombel ini
        $slotHours = LessonPeriod::where('is_break', false)
            ->whereBetween('sort_order', [$minSort, $maxSort])
            ->count();

        $allPeriodsMap = LessonPeriod::where('is_break', false)->pluck('sort_order')->flip();
        $usedHours = TeachingSchedule::where('teaching_assignment_id', $assignment->id)
            ->with(['startPeriod', 'endPeriod'])
            ->get()
            ->sum(function ($s) use ($allPeriodsMap) {
                $sStart = $s->startPeriod?->sort_order ?? 1;
                $sEnd = $s->endPeriod?->sort_order ?? $sStart;
                $min = min($sStart, $sEnd);
                $max = max($sStart, $endSort ?? $sEnd);

                $cnt = 0;
                for ($i = $min; $i <= $max; $i++) {
                    if (isset($allPeriodsMap[$i])) {
                        $cnt++;
                    }
                }

                return $cnt;
            });

        if (($usedHours + $slotHours) > $assignment->weekly_hours) {
            $remaining = max(0, $assignment->weekly_hours - $usedHours);
            $msg = "Batas alokasi jam mengajar terlampaui! Guru {$assignment->teacher?->full_name} untuk mapel {$assignment->subject?->name} di kelas {$assignment->schoolClass?->name} hanya dialokasikan {$assignment->weekly_hours} jam/minggu. Saat ini telah terjadwal {$usedHours} jam (sisa kuota {$remaining} jam). Penambahan slot {$slotHours} jam ini akan membuat total menjadi ".($usedHours + $slotHours).' jam.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }

            return redirect()->back()->withInput()->with('error', $msg);
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

        return redirect()->route('admin.academic.schedules.index', [
            'class_id' => $assignment->class_id,
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

        $startP = LessonPeriod::findOrFail($validated['start_period_id']);
        $endP = LessonPeriod::findOrFail($validated['end_period_id']);

        if ($startP->sort_order > $endP->sort_order) {
            $tmp = $validated['start_period_id'];
            $validated['start_period_id'] = $validated['end_period_id'];
            $validated['end_period_id'] = $tmp;
            $tmpP = $startP;
            $startP = $endP;
            $endP = $tmpP;
        }

        $minSort = min($startP->sort_order, $endP->sort_order);
        $maxSort = max($startP->sort_order, $endP->sort_order);

        // 1. Validasi jam istirahat
        $hasBreak = LessonPeriod::where('is_break', true)
            ->whereBetween('sort_order', [$minSort, $maxSort])
            ->first();

        if ($hasBreak) {
            $msg = 'Tidak dapat menempatkan jadwal pada rentang jam istirahat.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->withInput()->with('error', $msg);
        }

        $assignment = TeachingAssignment::with(['teacher', 'subject', 'schoolClass'])->findOrFail($validated['teaching_assignment_id']);

        // 2. Validasi bentrok / tumpang tindih dengan jadwal lain di kelas ini pada hari tersebut (kecualikan slot jadwal ini)
        $conflict = TeachingSchedule::whereHas('teachingAssignment', function ($q) use ($assignment) {
            $q->where('class_id', $assignment->class_id);
        })
            ->where('id', '!=', $schedule->id)
            ->where('day_of_week', $validated['day_of_week'])
            ->where(function ($q) use ($minSort, $maxSort) {
                $q->whereHas('startPeriod', fn ($sp) => $sp->where('sort_order', '<=', $maxSort))
                    ->whereHas('endPeriod', fn ($ep) => $ep->where('sort_order', '>=', $minSort));
            })
            ->with(['teachingAssignment.subject', 'teachingAssignment.teacher', 'startPeriod', 'endPeriod'])
            ->first();

        if ($conflict) {
            $cSub = $conflict->teachingAssignment?->subject?->name ?? 'Mata Pelajaran lain';
            $cTea = $conflict->teachingAssignment?->teacher?->full_name ?? 'Guru lain';
            $cStart = $conflict->startPeriod?->period_number ? "Jam ke-{$conflict->startPeriod->period_number}" : $conflict->startPeriod?->label;
            $cEnd = $conflict->endPeriod?->period_number ? "Jam ke-{$conflict->endPeriod->period_number}" : $conflict->endPeriod?->label;
            $cRange = $cStart == $cEnd ? $cStart : "{$cStart} s/d {$cEnd}";
            $msg = "Bentrok jadwal! Jam tersebut sudah terisi oleh {$cSub} ({$cTea}) pada {$cRange}. Silakan pilih jam lain yang masih kosong.";

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->withInput()->with('error', $msg);
        }

        // 3. Validasi batasan jam mengajar per minggu untuk rombel ini (kecualikan slot jadwal ini)
        $slotHours = LessonPeriod::where('is_break', false)
            ->whereBetween('sort_order', [$minSort, $maxSort])
            ->count();

        $allPeriodsMap = LessonPeriod::where('is_break', false)->pluck('sort_order')->flip();
        $usedHours = TeachingSchedule::where('teaching_assignment_id', $assignment->id)
            ->where('id', '!=', $schedule->id)
            ->with(['startPeriod', 'endPeriod'])
            ->get()
            ->sum(function ($s) use ($allPeriodsMap) {
                $sStart = $s->startPeriod?->sort_order ?? 1;
                $sEnd = $s->endPeriod?->sort_order ?? $sStart;
                $min = min($sStart, $sEnd);
                $max = max($sStart, $sEnd);

                $cnt = 0;
                for ($i = $min; $i <= $max; $i++) {
                    if (isset($allPeriodsMap[$i])) {
                        $cnt++;
                    }
                }

                return $cnt;
            });

        if (($usedHours + $slotHours) > $assignment->weekly_hours) {
            $remaining = max(0, $assignment->weekly_hours - $usedHours);
            $msg = "Batas alokasi jam mengajar terlampaui! Guru {$assignment->teacher?->full_name} untuk mapel {$assignment->subject?->name} di kelas {$assignment->schoolClass?->name} hanya dialokasikan {$assignment->weekly_hours} jam/minggu. Jadwal lain di kelas ini telah menggunakan {$usedHours} jam (sisa kuota {$remaining} jam). Mengubah slot ini menjadi {$slotHours} jam akan membuat total menjadi ".($usedHours + $slotHours).' jam.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }

            return redirect()->back()->withInput()->with('error', $msg);
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

        return redirect()->route('admin.academic.schedules.index', [
            'class_id' => $assignment->class_id,
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
     * Tambah jam pelajaran / jam istirahat baru (bisa disisipkan di antara jam yang sudah ada).
     */
    public function storePeriod(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:50'],
            'start_time' => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'end_time' => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'is_break' => ['nullable', 'boolean'],
            'insert_position' => ['nullable', 'string', 'in:end,start,after,before'],
            'target_period_id' => ['nullable', 'exists:lesson_periods,id'],
        ]);

        $isBreak = (bool) $request->boolean('is_break');
        $startTime = $validated['start_time'];
        $endTime = $validated['end_time'];

        if (strlen($startTime) === 5) {
            $startTime .= ':00';
        }
        if (strlen($endTime) === 5) {
            $endTime .= ':00';
        }

        if ($startTime >= $endTime) {
            $msg = 'Jam selesai harus lebih besar daripada jam mulai.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->withInput()->with('error', $msg);
        }

        $collided = LessonPeriod::where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->first();

        if ($collided) {
            $cLabel = $collided->is_break ? 'Istirahat' : ($collided->label ?: "Jam Ke-{$collided->period_number}");
            $cStart = substr($collided->start_time, 0, 5);
            $cEnd = substr($collided->end_time, 0, 5);
            $msg = "Waktu jam bertabrakan dengan {$cLabel} ({$cStart} - {$cEnd}). Jam yang sudah digunakan tidak dapat dipakai kembali.";

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->withInput()->with('error', $msg);
        }

        $insertPosition = $validated['insert_position'] ?? 'end';
        $targetId = $validated['target_period_id'] ?? null;

        if ($insertPosition === 'after' && $targetId) {
            $targetPeriod = LessonPeriod::findOrFail($targetId);
            $newSortOrder = $targetPeriod->sort_order + 1;
            LessonPeriod::where('sort_order', '>=', $newSortOrder)->increment('sort_order');
        } elseif ($insertPosition === 'before' && $targetId) {
            $targetPeriod = LessonPeriod::findOrFail($targetId);
            $newSortOrder = $targetPeriod->sort_order;
            LessonPeriod::where('sort_order', '>=', $newSortOrder)->increment('sort_order');
        } elseif ($insertPosition === 'start') {
            $newSortOrder = 1;
            LessonPeriod::increment('sort_order');
        } else {
            $maxSort = LessonPeriod::max('sort_order') ?? 0;
            $newSortOrder = $maxSort + 1;
        }

        $label = $isBreak ? 'Istirahat' : ($validated['label'] ?? 'Jam Baru');

        $period = LessonPeriod::create([
            'sort_order' => $newSortOrder,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'label' => $label,
            'is_break' => $isBreak,
            'period_number' => null,
        ]);

        LessonPeriod::resequence();
        $period->refresh();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $isBreak ? 'Jam istirahat berhasil ditambahkan.' : 'Jam pelajaran baru berhasil ditambahkan.',
                'period' => $period,
            ]);
        }

        return redirect()->back()->with('success', $isBreak ? 'Jam istirahat berhasil ditambahkan.' : 'Jam pelajaran berhasil ditambahkan.');
    }

    /**
     * Perbarui jam pelajaran / jam istirahat.
     */
    public function updatePeriod(Request $request, LessonPeriod $period): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:50'],
            'start_time' => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'end_time' => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'is_break' => ['nullable', 'boolean'],
        ]);

        $isBreak = (bool) $request->boolean('is_break');
        if ($isBreak && ! $period->is_break) {
            if ($period->schedulesAsStart()->exists() || $period->schedulesAsEnd()->exists()) {
                $msg = 'Jam pelajaran ini tidak dapat diubah menjadi jam istirahat karena masih digunakan pada jadwal pelajaran aktif.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }

                return redirect()->back()->with('error', $msg);
            }
        }

        $startTime = $validated['start_time'];
        $endTime = $validated['end_time'];

        if (strlen($startTime) === 5) {
            $startTime .= ':00';
        }
        if (strlen($endTime) === 5) {
            $endTime .= ':00';
        }

        if ($startTime >= $endTime) {
            $msg = 'Jam selesai harus lebih besar daripada jam mulai.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->withInput()->with('error', $msg);
        }

        $collided = LessonPeriod::where('id', '!=', $period->id)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->first();

        if ($collided) {
            $cLabel = $collided->is_break ? 'Istirahat' : ($collided->label ?: "Jam Ke-{$collided->period_number}");
            $cStart = substr($collided->start_time, 0, 5);
            $cEnd = substr($collided->end_time, 0, 5);
            $msg = "Waktu jam bertabrakan dengan {$cLabel} ({$cStart} - {$cEnd}). Jam yang sudah digunakan tidak dapat dipakai kembali.";

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->withInput()->with('error', $msg);
        }

        $label = $isBreak ? 'Istirahat' : ($validated['label'] ?: $period->label);

        $period->update([
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_break' => $isBreak,
            'label' => $label,
        ]);

        LessonPeriod::resequence();
        $period->refresh();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jam berhasil diperbarui.',
                'period' => $period,
            ]);
        }

        return redirect()->back()->with('success', 'Jam berhasil diperbarui.');
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
        LessonPeriod::resequence();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jam berhasil dihapus dan urutan jam telah disesuaikan kembali.',
            ]);
        }

        return redirect()->back()->with('success', 'Jam berhasil dihapus.');
    }

    /**
     * Export jadwal pelajaran mingguan ke PDF (Print-ready document).
     */
    public function exportPdf(Request $request): View
    {
        $selectedClassId = $request->query('class_id');
        $classes = SchoolClass::where('is_active', true)->with('department')->orderBy('name')->get();
        if ($classes->isEmpty()) {
            $classes = SchoolClass::with('department')->orderBy('name')->get();
        }
        $selectedClass = $selectedClassId ? SchoolClass::with('department')->find($selectedClassId) : $classes->first();

        $daysCount = (int) $request->input('days_count', 5);
        $daysCount = in_array($daysCount, [5, 6], true) ? $daysCount : 5;

        $allDays = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
        $days = array_slice($allDays, 0, $daysCount, true);

        $periods = LessonPeriod::orderBy('sort_order')->orderBy('start_time')->get();
        $periodsSorted = $periods->sortBy('sort_order')->values();

        $schedules = collect();
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
        }

        $matrix = $this->buildTimetableMatrix($schedules, $periodsSorted);
        $schoolProfile = SchoolProfile::first();

        return view('admin.academic.schedules.pdf', compact(
            'selectedClass',
            'classes',
            'daysCount',
            'days',
            'periods',
            'periodsSorted',
            'matrix',
            'schoolProfile'
        ));
    }

    /**
     * Export jadwal pelajaran mingguan ke Excel (.xls Spreadsheet).
     */
    public function exportExcel(Request $request): Response
    {
        $selectedClassId = $request->query('class_id');
        $classes = SchoolClass::where('is_active', true)->with('department')->orderBy('name')->get();
        if ($classes->isEmpty()) {
            $classes = SchoolClass::with('department')->orderBy('name')->get();
        }
        $selectedClass = $selectedClassId ? SchoolClass::with('department')->find($selectedClassId) : $classes->first();

        $daysCount = (int) $request->input('days_count', 5);
        $daysCount = in_array($daysCount, [5, 6], true) ? $daysCount : 5;

        $allDays = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
        $days = array_slice($allDays, 0, $daysCount, true);

        $periods = LessonPeriod::orderBy('sort_order')->orderBy('start_time')->get();
        $periodsSorted = $periods->sortBy('sort_order')->values();

        $schedules = collect();
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
        }

        $matrix = $this->buildTimetableMatrix($schedules, $periodsSorted);
        $schoolProfile = SchoolProfile::first();
        $schoolName = $schoolProfile?->school_name ?? 'SMK Negeri 2 Karanganyar';
        $className = $selectedClass?->name ?? 'Semua_Kelas';

        $html = view('admin.academic.schedules.excel', compact(
            'selectedClass',
            'schoolName',
            'className',
            'daysCount',
            'days',
            'periodsSorted',
            'matrix',
            'schoolProfile'
        ))->render();

        $cleanClass = preg_replace('/[^A-Za-z0-9_-]/', '_', $className);
        $fileName = 'Jadwal_Pelajaran_'.$cleanClass.'_'.date('Ymd_His').'.xls';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Helper untuk menghitung matrix penjadwalan dengan dukungan multi-period (rowspan).
     */
    private function buildTimetableMatrix($schedules, $periodsSorted): array
    {
        $matrix = [];
        $periodIndexMap = [];
        foreach ($periodsSorted as $idx => $p) {
            $periodIndexMap[$p->id] = $idx;
        }

        foreach ($schedules as $sch) {
            $day = $sch->day_of_week;
            $startId = $sch->start_period_id;
            $endId = $sch->end_period_id;

            $startIdx = $periodIndexMap[$startId] ?? null;
            $endIdx = $periodIndexMap[$endId] ?? null;

            if ($startIdx !== null && $endIdx !== null && $endIdx >= $startIdx) {
                $span = $endIdx - $startIdx + 1;
                $matrix[$startId][$day] = [
                    'type' => 'start',
                    'schedule' => $sch,
                    'span' => $span,
                ];

                for ($i = $startIdx + 1; $i <= $endIdx; $i++) {
                    $pId = $periodsSorted[$i]->id;
                    $matrix[$pId][$day] = [
                        'type' => 'covered',
                        'schedule' => $sch,
                    ];
                }
            } elseif ($startIdx !== null) {
                $matrix[$startId][$day] = [
                    'type' => 'start',
                    'schedule' => $sch,
                    'span' => 1,
                ];
            }
        }

        return $matrix;
    }
}
