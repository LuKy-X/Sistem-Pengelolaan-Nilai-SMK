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
            ->where('status', 'OPEN')
            ->orderByDesc('registration_start')
            ->get();

        return view('public.ppdb.index', [
            'periods' => $periods,
            'primaryPeriod' => $periods->first(),
        ]);
    }
}
