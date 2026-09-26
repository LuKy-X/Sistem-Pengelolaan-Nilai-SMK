<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\PublicSiteService;
use Illuminate\View\View;

class SchoolProfileController extends Controller
{
    public function __construct(private readonly PublicSiteService $publicSite) {}

    public function show(): View
    {
        return view('public.profile', [
            'schoolProfile' => $this->publicSite->profile(),
            'departments' => $this->publicSite->activeDepartments(),
        ]);
    }
}
