<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\StudentProduct;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentProductController extends Controller
{
    private const PER_PAGE = 9;

    private const RELATED_LIMIT = 4;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::exists('product_categories', 'id')],
        ]);
        $search = trim($filters['q'] ?? '');
        $category = $filters['category'] ?? '';

        $products = StudentProduct::query()
            ->with(['category', 'department', 'media'])
            ->where('status', 'AVAILABLE')
            ->when($category !== '', fn ($query) => $query->where('category_id', $category))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('contact', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('department', fn ($departmentQuery) => $departmentQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('short_name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('public.products.index', [
            'products' => $products,
            'search' => $search,
            'selectedCategory' => $category,
            'categories' => ProductCategory::query()
                ->whereHas('products', fn ($query) => $query->where('status', 'AVAILABLE'))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function show(StudentProduct $studentProduct): View
    {
        abort_unless($studentProduct->status === 'AVAILABLE', 404);

        $studentProduct->load(['category', 'department', 'media']);

        $relatedProducts = StudentProduct::query()
            ->with(['category', 'department', 'media'])
            ->where('status', 'AVAILABLE')
            ->whereKeyNot($studentProduct->getKey())
            ->when(
                $studentProduct->category_id !== null,
                fn ($query) => $query->where('category_id', $studentProduct->category_id),
            )
            ->orderByDesc('id')
            ->limit(self::RELATED_LIMIT)
            ->get();

        if ($relatedProducts->count() < self::RELATED_LIMIT) {
            $relatedProducts = $relatedProducts->concat(
                StudentProduct::query()
                    ->with(['category', 'department', 'media'])
                    ->where('status', 'AVAILABLE')
                    ->whereNotIn('id', $relatedProducts->pluck('id')->push($studentProduct->getKey()))
                    ->orderByDesc('id')
                    ->limit(self::RELATED_LIMIT - $relatedProducts->count())
                    ->get(),
            );
        }

        return view('public.products.show', [
            'product' => $studentProduct,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
