<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AchievementController extends Controller
{
    private const PER_PAGE = 9;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::exists('achievement_categories', 'slug')],
        ]);
        $search = trim($filters['q'] ?? '');
        $category = $filters['category'] ?? '';

        $achievements = Achievement::query()
            ->with(['category', 'media'])
            ->when($category !== '', fn ($query) => $query->whereHas(
                'category',
                fn ($categoryQuery) => $categoryQuery->where('slug', $category)
            ))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('scope', 'like', "%{$search}%")
                        ->orWhere('level', 'like', "%{$search}%")
                        ->orWhere('rank', 'like', "%{$search}%")
                        ->orWhere('organizer', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('achievement_date')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('public.achievements.index', [
            'achievements' => $achievements,
            'search' => $search,
            'selectedCategory' => $category,
            'categories' => AchievementCategory::query()
                ->whereHas('achievements')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function show(Achievement $achievement): View
    {
        $achievement->load(['category', 'media', 'participants.student']);

        return view('public.achievements.show', [
            'achievement' => $achievement,
        ]);
    }
}
