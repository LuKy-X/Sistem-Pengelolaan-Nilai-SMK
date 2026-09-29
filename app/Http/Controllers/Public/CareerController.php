<?php

namespace App\Http\Controllers\Public;

use App\Enums\CareerOpportunityStatus;
use App\Http\Controllers\Controller;
use App\Models\CareerCompany;
use App\Models\CareerOpportunity;
use App\Models\CareerService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CareerController extends Controller
{
    private const PER_PAGE = 9;

    public function index(Request $request): View
    {
        $selectedType = $request->string('tipe')->trim()->toString();

        $opportunities = CareerOpportunity::query()
            ->with('company')
            ->where('status', CareerOpportunityStatus::Open)
            ->when(
                in_array($selectedType, ['JOB', 'INTERNSHIP'], true),
                fn ($query) => $query->where('type', $selectedType)
            )
            ->orderByDesc('open_date')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('public.career.index', [
            'services' => CareerService::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
            'opportunities' => $opportunities,
            'companies' => CareerCompany::query()->orderBy('name')->get(),
            'selectedType' => $selectedType,
        ]);
    }
}
