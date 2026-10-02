<?php

namespace App\Http\Controllers\Public;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    private const PER_PAGE = 9;

    public function index(Request $request): View
    {
        $selectedCategory = $request->string('kategori')->trim()->toString();

        $articles = Article::query()
            ->where('status', ContentStatus::Published)
            ->with(['category', 'media'])
            ->when(
                $selectedCategory !== '',
                fn ($query) => $query->whereHas(
                    'category',
                    fn ($categoryQuery) => $categoryQuery->where('slug', $selectedCategory)
                )
            )
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('public.articles.index', [
            'articles' => $articles,
            'categories' => ArticleCategory::query()
                ->whereHas('articles', fn ($query) => $query->where('status', ContentStatus::Published))
                ->orderBy('name')
                ->get(),
            'selectedCategory' => $selectedCategory,
        ]);
    }

    /**
     * Article detail page, bound by the unique `slug`.
     *
     * Unpublished articles resolve to a 404 for anonymous visitors.
     */
    public function show(Article $article): View
    {
        abort_unless($article->status === ContentStatus::Published, 404);

        $article->load(['category', 'author', 'media']);

        $relatedArticles = Article::query()
            ->where('status', ContentStatus::Published)
            ->with(['category', 'media'])
            ->whereKeyNot($article->getKey())
            ->when(
                $article->category_id !== null,
                fn ($query) => $query->where('category_id', $article->category_id)
            )
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $article->incrementQuietly('views');

        return view('public.articles.show', [
            'article' => $article,
            'relatedArticles' => $relatedArticles,
        ]);
    }
}
