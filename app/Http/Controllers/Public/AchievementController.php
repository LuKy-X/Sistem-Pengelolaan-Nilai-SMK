<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AchievementController extends Controller
{
    private const PER_PAGE = 12;

    public function index(Request $request): View
    {
        $selectedCategory = $request->string('kategori')->trim()->toString();

        $achievements = Achievement::query()
            ->with(['category', 'media'])
            ->when(
                $selectedCategory !== '',
                fn ($query) => $query->whereHas(
                    'category',
                    fn ($categoryQuery) => $categoryQuery->where('slug', $selectedCategory)
                )
            )
            ->orderByDesc('achievement_date')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('public.achievements.index', [
            'achievements' => $achievements,
            'categories' => AchievementCategory::query()
                ->whereHas('achievements')
                ->orderBy('name')
                ->get(),
            'selectedCategory' => $selectedCategory,
        ]);
    }
}
