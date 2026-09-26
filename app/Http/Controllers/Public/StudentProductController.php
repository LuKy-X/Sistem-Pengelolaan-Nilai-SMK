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
}
