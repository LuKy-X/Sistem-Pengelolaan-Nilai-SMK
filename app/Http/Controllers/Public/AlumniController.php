<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AlumniProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlumniController extends Controller
{
    private const PER_PAGE = 9;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($filters['q'] ?? '');

        $alumni = AlumniProfile::query()
            ->with([
                'student',
                'stories' => fn ($query) => $query->where('is_featured', true)->with('media')->orderByDesc('id'),
            ])
            ->whereHas('stories', fn ($query) => $query->where('is_featured', true))
            ->whereHas('student', fn ($query) => $query->where('status', 'GRADUATED'))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('current_occupation', 'like', "%{$search}%")
                        ->orWhere('current_company', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhereHas('student', fn ($studentQuery) => $studentQuery->where('full_name', 'like', "%{$search}%"))
                        ->orWhereHas('stories', fn ($storyQuery) => $storyQuery
                            ->where('is_featured', true)
                            ->where(function ($storyQuery) use ($search): void {
                                $storyQuery->where('title', 'like', "%{$search}%")
                                    ->orWhere('story', 'like', "%{$search}%")
                                    ->orWhere('career_story', 'like', "%{$search}%")
                                    ->orWhere('quote', 'like', "%{$search}%");
                            }));
                });
            })
            ->orderByDesc('graduation_year')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('public.alumni.index', [
            'alumni' => $alumni,
            'search' => $search,
        ]);
    }

    public function show(AlumniProfile $alumni): View
    {
        $alumni->load([
            'student',
            'stories' => fn ($query) => $query->where('is_featured', true)->with('media')->orderByDesc('id'),
        ]);

        abort_unless(
            $alumni->student?->status === 'GRADUATED' && $alumni->stories->isNotEmpty(),
            404,
        );

        return view('public.alumni.show', [
            'alumnus' => $alumni,
            'story' => $alumni->stories->first(),
        ]);
    }
}
