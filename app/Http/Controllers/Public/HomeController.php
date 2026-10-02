<?php

namespace App\Http\Controllers\Public;

use App\Enums\CareerOpportunityStatus;
use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\AdmissionPeriod;
use App\Models\AlumniProfile;
use App\Models\Article;
use App\Models\CareerCompany;
use App\Models\CareerOpportunity;
use App\Models\CareerService;
use App\Models\SiteStatistic;
use App\Models\StudentProduct;
use App\Services\PublicSiteService;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Section anchors shown in the public navbar.
     *
     * @var list<array{label: string, anchor: string, route: ?string}>
     */
    private const NAV_SECTIONS = [
        ['label' => 'Beranda', 'anchor' => 'beranda', 'route' => 'public.home'],
        ['label' => 'Jurusan', 'anchor' => 'jurusan', 'route' => 'public.departments.index'],
        ['label' => 'Prestasi', 'anchor' => 'prestasi', 'route' => 'public.achievements.index'],
        ['label' => 'Produk', 'anchor' => 'produk-unggulan', 'route' => 'public.products.index'],
        ['label' => 'Karier', 'anchor' => 'karier', 'route' => 'public.career.index'],
        ['label' => 'PPDB', 'anchor' => 'ppdb', 'route' => 'public.ppdb.index'],
        ['label' => 'Berita', 'anchor' => 'berita', 'route' => 'public.articles.index'],
    ];

    public function __construct(private readonly PublicSiteService $publicSite) {}

    public function index(): View
    {
        return view('public.home', [
            'navSections' => self::NAV_SECTIONS,
            'schoolProfile' => $this->publicSite->profile(),
            'statistics' => $this->statistics(),
            'departments' => $this->publicSite->activeDepartments(),
            'alumni' => $this->featuredAlumni(),
            'careerServices' => $this->careerServices(),
            'careerOpportunities' => $this->openOpportunities(),
            'companies' => $this->partnerCompanies(),
            'products' => $this->featuredProducts(),
            'achievements' => $this->latestAchievements(),
            'articles' => $this->latestArticles(),
            'admissionPeriod' => $this->activeAdmissionPeriod(),
        ]);
    }

    /**
     * Hero counters managed by the CMS (`site_statistics`, section = HERO).
     */
    private function statistics(): mixed
    {
        return SiteStatistic::query()
            ->where('section', 'HERO')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    private function latestArticles(): mixed
    {
        return Article::query()
            ->where('status', ContentStatus::Published)
            ->with(['category', 'media'])
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(config('public_site.landing_limits.articles'))
            ->get();
    }

    private function latestAchievements(): mixed
    {
        return Achievement::query()
            ->with(['category', 'media'])
            ->orderByDesc('is_featured')
            ->orderByDesc('achievement_date')
            ->orderByDesc('id')
            ->limit(config('public_site.landing_limits.achievements'))
            ->get();
    }

    private function featuredProducts(): mixed
    {
        return StudentProduct::query()
            ->with(['category', 'department', 'media'])
            ->where('status', 'AVAILABLE')
            ->orderByDesc('id')
            ->limit(config('public_site.landing_limits.products'))
            ->get();
    }

    private function featuredAlumni(): mixed
    {
        return AlumniProfile::query()
            ->with(['student', 'stories' => fn ($query) => $query->where('is_featured', true)])
            ->orderByDesc('is_featured')
            ->orderByDesc('graduation_year')
            ->orderByDesc('id')
            ->limit(3)
            ->get();
    }

    private function activeAdmissionPeriod(): ?AdmissionPeriod
    {
        return AdmissionPeriod::query()
            ->with(['academicYear', 'scheduleItems', 'paths', 'requirements'])
            ->whereIn('status', ['OPEN', 'CLOSED'])
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', ['OPEN'])
            ->orderByDesc('registration_start')
            ->first();
    }

    private function careerServices(): mixed
    {
        return CareerService::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(4)
            ->get();
    }

    private function openOpportunities(): mixed
    {
        return CareerOpportunity::query()
            ->with('company')
            ->where('status', CareerOpportunityStatus::Open)
            ->orderByDesc('open_date')
            ->orderByDesc('id')
            ->limit(config('public_site.landing_limits.opportunities'))
            ->get();
    }

    /**
     * Partner companies for the "Kerja Sama Industri" strip. The view falls
     * back to the company name badge when no logo is stored, so companies
     * without artwork still make the strip.
     */
    private function partnerCompanies(): mixed
    {
        return CareerCompany::query()
            ->orderBy('name')
            ->limit(7)
            ->get();
    }

    /**
     * Exposed for the public navigation partial.
     *
     * @return list<array{label: string, anchor: string, route: ?string}>
     */
    public static function navSections(): array
    {
        return self::NAV_SECTIONS;
    }
}
