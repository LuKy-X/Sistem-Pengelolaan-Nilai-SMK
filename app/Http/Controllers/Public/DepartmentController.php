<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
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
        ]);

        return view('public.departments.show', [
            'department' => $department,
        ]);
    }
}
