<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\AdmissionPeriod;
use App\Models\Article;
use App\Models\CareerCompany;
use App\Models\CareerOpportunity;
use App\Models\Department;
use App\Models\ProductCategory;
use App\Models\SchoolProfile;
use App\Models\StudentProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CmsController extends Controller
{
    public function profile(): View
    {
        $profile = SchoolProfile::first() ?? new SchoolProfile([
            'school_name' => 'SMK Negeri 2 Karanganyar',
            'npsn' => '20312345',
            'principal_name' => 'Drs. H. Sukardi, M.Pd.',
            'phone' => '(0271) 495123',
            'email' => 'info@smkn2-kra.sch.id',
            'website' => 'https://smkn2-kra.sch.id',
            'address' => 'Jl. Yos Sudarso, Karanganyar, Jawa Tengah',
            'vision' => 'Menjadi Sekolah Menengah Kejuruan Unggul, Berkarakter, dan Berdaya Saing Global.',
            'mission' => "1. Menyelenggarakan pendidikan vokasi berkualitas.\n2. Mengembangkan kerja sama erat dengan dunia usaha dan industri.",
        ]);

        return view('admin.cms.profile', compact('profile'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:150'],
            'npsn' => ['nullable', 'string', 'max:20'],
            'principal_name' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'website' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string'],
            'vision' => ['nullable', 'string'],
            'mission' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $profile = SchoolProfile::first();
        if ($profile) {
            $profile->update($validated);
        } else {
            SchoolProfile::create($validated);
        }

        return redirect()->back()->with('success', 'Profil dan konfigurasi sekolah berhasil diperbarui.');
    }

    public function articles(): View
    {
        $articles = Article::with('category')->latest()->paginate(10);

        return view('admin.cms.articles', compact('articles'));
    }

    public function ppdb(): View
    {
        $periods = AdmissionPeriod::with(['academicYear', 'paths', 'scheduleItems', 'requirements', 'feeItems'])
            ->latest()
            ->get();

        $academicYears = AcademicYear::latest('start_date')->get();

        return view('admin.cms.ppdb', compact('periods', 'academicYears'));
    }

    public function storeAdmissionPeriod(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'title' => ['required', 'string', 'max:150'],
            'registration_start' => ['required', 'date'],
            'registration_end' => ['required', 'date', 'after_or_equal:registration_start'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:DRAFT,OPEN,CLOSED'],
        ]);

        AdmissionPeriod::create($validated);

        return redirect()->back()->with('success', 'Gelombang / Periode PPDB berhasil dibuat.');
    }

    public function achievements(): View
    {
        $achievements = Achievement::with(['category', 'participants.student'])
            ->latest('achievement_date')
            ->paginate(15);

        $categories = AchievementCategory::all();

        return view('admin.cms.achievements', compact('achievements', 'categories'));
    }

    public function storeAchievement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'achievement_category_id' => ['required', 'exists:achievement_categories,id'],
            'title' => ['required', 'string', 'max:200'],
            'scope' => ['required', 'string', 'max:50'],
            'level' => ['required', 'string', 'max:50'],
            'achievement_date' => ['required', 'date'],
            'organizer' => ['nullable', 'string', 'max:150'],
            'rank' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $validated['is_featured'] = (bool) ($validated['is_featured'] ?? false);

        Achievement::create($validated);

        return redirect()->back()->with('success', 'Prestasi baru berhasil ditambahkan.');
    }

    public function products(): View
    {
        $products = StudentProduct::with(['category', 'department', 'students'])
            ->latest()
            ->paginate(15);

        $categories = ProductCategory::all();
        $departments = Department::where('is_active', true)->get();

        return view('admin.cms.products', compact('products', 'categories', 'departments'));
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:product_categories,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:150'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'contact' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:AVAILABLE,OUT_OF_STOCK,PRE_ORDER'],
        ]);

        $validated['slug'] = Str::slug($validated['name']).'-'.Str::random(4);

        StudentProduct::create($validated);

        return redirect()->back()->with('success', 'Produk kreatif siswa berhasil didaftarkan.');
    }

    public function career(): View
    {
        $opportunities = CareerOpportunity::with(['company', 'applications.student'])
            ->latest()
            ->paginate(15);

        $companies = CareerCompany::orderBy('name')->get();

        return view('admin.cms.career', compact('opportunities', 'companies'));
    }

    public function storeCareer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'exists:career_companies,id'],
            'title' => ['required', 'string', 'max:200'],
            'type' => ['required', 'in:JOB,INTERNSHIP'],
            'location' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'open_date' => ['nullable', 'date'],
            'close_date' => ['nullable', 'date', 'after_or_equal:open_date'],
            'status' => ['required', 'in:OPEN,CLOSED'],
        ]);

        CareerOpportunity::create($validated);

        return redirect()->back()->with('success', 'Lowongan pekerjaan/magang BKK berhasil dipublikasikan.');
    }
}
