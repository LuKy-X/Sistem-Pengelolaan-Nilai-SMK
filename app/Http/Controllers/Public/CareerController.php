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

    /**
     * Partner companies per page in the sidebar list.
     */
    private const COMPANIES_PER_PAGE = 6;

    /**
     * Query string key for the partner list, kept separate from `page` so the
     * opportunities paginator on the same page keeps its own position.
     */
    private const COMPANIES_PAGE_NAME = 'mitra';

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'in:JOB,INTERNSHIP'],
            'company_q' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($filters['q'] ?? '');
        $type = $filters['type'] ?? '';
        $companySearch = trim($filters['company_q'] ?? '');

        $opportunities = CareerOpportunity::query()
            ->with(['company', 'media'])
            ->where('status', CareerOpportunityStatus::Open)
            ->when($type !== '', fn ($query) => $query->where('type', $type))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('requirements', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhereHas('company', fn ($companyQuery) => $companyQuery
                            ->where('name', 'like', "%{$search}%"));
                });
            })
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
            'search' => $search,
            'selectedType' => $type,
            'companySearch' => $companySearch,
            'hasCompanies' => CareerCompany::query()->exists(),
            'companies' => CareerCompany::query()
                ->when($companySearch !== '', fn ($query) => $query->where(function ($query) use ($companySearch): void {
                    $query->where('name', 'like', "%{$companySearch}%")
                        ->orWhere('industry', 'like', "%{$companySearch}%")
                        ->orWhere('address', 'like', "%{$companySearch}%");
                }))
                ->orderBy('name')
                ->paginate(self::COMPANIES_PER_PAGE, ['*'], self::COMPANIES_PAGE_NAME)
                ->withQueryString(),
        ]);
    }

    public function show(CareerOpportunity $opportunity): View
    {
        abort_unless($opportunity->status === CareerOpportunityStatus::Open, 404);

        $opportunity->load(['company', 'media']);

        return view('public.career.show', [
            'opportunity' => $opportunity,
        ]);
    }
}
