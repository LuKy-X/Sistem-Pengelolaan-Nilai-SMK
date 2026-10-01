<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CareerCompany;
use App\Models\Department;
use App\Services\PublicSiteService;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function __construct(private readonly PublicSiteService $publicSite) {}

    public function index(): View
    {
        return view('public.departments.index', [
            'departments' => $this->publicSite->activeDepartments(),
        ]);
    }

    /**
     * Department detail page, bound by the unique `code` slug.
     */
    public function show(Department $department): View
    {
        abort_unless($department->is_active, 404);

        $department->load([
            'competencies' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
            'facilities' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
            'subjects' => fn ($query) => $query->where('is_active', true)->orderBy('code'),
            'studentProducts' => fn ($query) => $query
                ->where('status', 'AVAILABLE')
                ->with('category')
                ->orderByDesc('id')
                ->limit(4),
        ]);

        return view('public.departments.show', [
            'department' => $department,
            'partners' => $this->partnerCompanies(),
        ]);
    }

    /**
     * Partner companies shown as the "Mitra Industri" chips. There is no
     * department-to-company relation in the schema, so every company that has
     * placements is a valid partner for the program.
     */
    private function partnerCompanies(): mixed
    {
        return CareerCompany::query()
            ->orderBy('name')
            ->limit(8)
            ->get();
    }
}
