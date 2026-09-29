<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AdmissionPeriod;
use Illuminate\View\View;

class AdmissionController extends Controller
{
    public function index(): View
    {
        $periods = AdmissionPeriod::query()
            ->with([
                'academicYear',
                'scheduleItems',
                'paths' => fn ($query) => $query->where('is_active', true),
                'requirements',
                'feeItems',
            ])
            ->whereIn('status', ['OPEN', 'CLOSED'])
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', ['OPEN'])
            ->orderByDesc('registration_start')
            ->get();

        return view('public.ppdb.index', [
            'periods' => $periods,
            'primaryPeriod' => $periods->first(),
        ]);
    }
}
