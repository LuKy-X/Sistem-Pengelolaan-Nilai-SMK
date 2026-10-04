<?php

namespace App\Http\Controllers\Public;

use App\Enums\CareerOpportunityStatus;
use App\Enums\CareerOpportunityType;
use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\AdmissionPeriod;
use App\Models\AlumniProfile;
use App\Models\Article;
use App\Models\CareerCompany;
use App\Models\CareerOpportunity;
use App\Models\StudentProduct;
use App\Services\PublicSiteService;
use Illuminate\Database\Eloquent\Collection;
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
            'departments' => $this->publicSite->activeDepartments(),
            'alumni' => $this->featuredAlumni(),
            'jobOpportunities' => $this->openOpportunities(CareerOpportunityType::Job),
            'internshipOpportunities' => $this->openOpportunities(CareerOpportunityType::Internship),
            'companies' => $this->partnerCompanies(),
            'products' => $this->featuredProducts(),
            'achievements' => $this->featuredAchievements(),
            'articles' => $this->latestArticles(),
            'admissionPeriod' => $this->activeAdmissionPeriod(),
        ]);
    }

    /**
     * Latest published news for the landing page.
     *
     * Articles have no pin column, so recency is the only ordering available.
     */
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

    /**
     * Only achievements selected by the school are shown on the landing page.
     */
    private function featuredAchievements(): mixed
    {
        return Achievement::query()
            ->with(['category', 'media'])
            ->where('is_featured', true)
            ->orderByDesc('achievement_date')
            ->orderByDesc('id')
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
        $featuredStory = fn ($query) => $query
            ->where('is_featured', true)
            ->whereNotNull('title')
            ->where('title', '!=', '')
            ->where(function ($query): void {
                $query->whereNotNull('career_story')->where('career_story', '!=', '')
                    ->orWhereNotNull('story')->where('story', '!=', '')
                    ->orWhereNotNull('quote')->where('quote', '!=', '');
            });

        return AlumniProfile::query()
            ->with([
                'student',
                'stories' => fn ($query) => $featuredStory($query)->with('media')->latest('id'),
            ])
            ->whereHas('stories', $featuredStory)
            ->whereHas('student', fn ($query) => $query
                ->where('status', 'GRADUATED')
                ->whereNotNull('full_name')
                ->where('full_name', '!=', ''))
            ->orderByDesc('is_featured')
            ->orderByDesc('graduation_year')
            ->orderByDesc('id')
            ->limit(1)
            ->get();
    }

    private function activeAdmissionPeriod(): ?AdmissionPeriod
    {
        return AdmissionPeriod::query()
            ->with(['academicYear', 'scheduleItems', 'paths', 'requirements'])
            ->where('status', 'OPEN')
            ->orderByDesc('registration_start')
            ->first();
    }

    private function openOpportunities(CareerOpportunityType $type): Collection
    {
        return CareerOpportunity::query()
            ->with(['company', 'media'])
            ->where('status', CareerOpportunityStatus::Open)
            ->where('type', $type)
            ->orderByDesc('open_date')
            ->orderByDesc('id')
            ->limit(config('public_site.landing_limits.opportunities'))
            ->get();
    }

    /**
     * Partner companies with published logos for the landing-page strip.
     *
     * `career_companies` has no pin column, so the newest partnerships lead the
     * marquee, which is the closest thing to a "pinned" order available here.
     */
    private function partnerCompanies(): mixed
    {
        return CareerCompany::query()
            ->whereNotNull('logo')
            ->where('logo', '!=', '')
            ->orderByDesc('id')
            ->limit(12)
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
