<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AlumniProfile;
use Illuminate\View\View;

class AlumniController extends Controller
{
    private const PER_PAGE = 9;

    public function index(): View
    {
        $alumni = AlumniProfile::query()
            ->with([
                'student',
                'stories' => fn ($query) => $query->where('is_featured', true),
            ])
            ->orderByDesc('is_featured')
            ->orderByDesc('graduation_year')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE);

        return view('public.alumni.index', [
            'alumni' => $alumni,
        ]);
    }
}
