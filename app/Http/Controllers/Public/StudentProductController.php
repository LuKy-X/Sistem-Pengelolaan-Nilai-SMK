<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\StudentProduct;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentProductController extends Controller
{
    private const PER_PAGE = 12;

    private const RELATED_LIMIT = 4;

    public function index(Request $request): View
    {
        $selectedCategory = $request->integer('kategori') ?: null;

        $products = StudentProduct::query()
            ->with(['category', 'department'])
            ->where('status', 'AVAILABLE')
            ->when($selectedCategory, fn ($query) => $query->where('category_id', $selectedCategory))
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('public.products.index', [
            'products' => $products,
            'categories' => ProductCategory::query()
                ->whereHas('products')
                ->orderBy('name')
                ->get(),
            'selectedCategory' => $selectedCategory,
        ]);
    }

    public function show(StudentProduct $studentProduct): View
    {
        abort_unless($studentProduct->status === 'AVAILABLE', 404);

        $studentProduct->load(['category', 'department', 'media']);

        $relatedProducts = StudentProduct::query()
            ->with(['category', 'department'])
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
                    ->with(['category', 'department'])
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
