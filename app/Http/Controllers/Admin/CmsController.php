<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CareerOpportunityStatus;
use App\Enums\CareerOpportunityType;
use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Achievement;
use App\Models\AchievementCategory;
use App\Models\AdmissionFeeItem;
use App\Models\AdmissionPath;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionRequirement;
use App\Models\AdmissionScheduleItem;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\CareerCompany;
use App\Models\CareerOpportunity;
use App\Models\CareerService;
use App\Models\Department;
use App\Models\ProductCategory;
use App\Models\SchoolProfile;
use App\Models\StudentProduct;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\PublicMediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CmsController extends Controller
{
    public function profile(): View
    {
        $profile = SchoolProfile::first() ?? new SchoolProfile([
            'school_name' => 'SMK Negeri 2 Karanganyar',
            'npsn' => '20312345',
            'principal_name' => 'Sukidi, S.Pd., M.Pd.',
            'phone' => '(0271) 495123',
            'email' => 'info@smkn2-kra.sch.id',
            'website' => 'https://smkn2-kra.sch.id',
            'address' => 'Jl. Yos Sudarso, Karanganyar, Jawa Tengah',
            'description' => 'Pusat keunggulan pendidikan vokasi teknologi dan rekayasa di Karanganyar yang mencetak lulusan kompeten, berkarakter, dan berdaya saing global.',
            'vision' => 'Menjadi Sekolah Menengah Kejuruan Unggul, Berkarakter, dan Berdaya Saing Global.',
            'mission' => "1. Menyelenggarakan pendidikan vokasi berkualitas berbasis industri.\n2. Mengembangkan karakter akhlak mulia dan budaya kerja profesional.\n3. Memperluas jejaring kemitraan strategis dengan dunia usaha dan dunia industri.",
            'history' => 'SMK Negeri 2 Karanganyar didirikan untuk menjawab kebutuhan tenaga kerja terampil dan profesional. Dengan fasilitas modern dan kurikulum yang selaras dengan industri, sekolah terus berinovasi mencetak talenta unggul di kancah nasional maupun global.',
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
            'description' => ['nullable', 'string'],
            'vision' => ['nullable', 'string'],
            'mission' => ['nullable', 'string'],
            'history' => ['nullable', 'string'],
        ]);

        $profile = SchoolProfile::first();

        if ($profile) {
            $profile->update($validated);
        } else {
            SchoolProfile::create($validated);
        }

        return redirect()->back()->with('success', 'Profil dan konfigurasi identitas sekolah berhasil diperbarui.');
    }

    public function articles(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $categoryId = $request->input('category_id');
        $status = $request->input('status');

        $articlesQuery = Article::with(['category', 'author'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->when(! empty($categoryId), function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when(! empty($status) && in_array($status, ['DRAFT', 'PUBLISHED'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest('id');

        $articles = $articlesQuery->paginate(10)->withQueryString();

        $categories = ArticleCategory::withCount('articles')->orderBy('name')->get();

        $stats = [
            'total' => Article::count(),
            'published' => Article::where('status', ContentStatus::Published)->count(),
            'draft' => Article::where('status', ContentStatus::Draft)->count(),
            'views' => (int) Article::sum('views'),
        ];

        return view('admin.cms.articles.index', compact('articles', 'categories', 'stats', 'search', 'categoryId', 'status'));
    }

    public function createArticle(): View
    {
        $categories = ArticleCategory::orderBy('name')->get();

        return view('admin.cms.articles.create', compact('categories'));
    }

    public function storeArticle(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:article_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:250'],
            'content' => ['required', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'original_thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:8192'],
            'status' => ['required', 'in:DRAFT,PUBLISHED'],
        ]);

        // Penulis otomatis dari sesi user yang login
        $validated['author_id'] = $request->user()?->id ?? auth()->id() ?? User::first()?->id;

        // Slug otomatis dari judul
        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug ?: 'artikel-'.Str::random(5);
        $counter = 1;
        while (Article::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }
        $validated['slug'] = $slug;

        // Ringkasan manual dari inputan pengguna (maksimal 250 karakter)
        $validated['excerpt'] = filled($validated['excerpt'] ?? null) ? trim((string) $validated['excerpt']) : null;

        // Upload Thumbnail
        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('articles', 'public');
        }

        // Waktu terbit otomatis now() saat pertama di-publish
        if ($validated['status'] === ContentStatus::Published->value) {
            $validated['published_at'] = now();
        } else {
            $validated['published_at'] = null;
        }

        // Jumlah views default 0
        $validated['views'] = 0;

        $article = Article::create($validated);

        // Simpan foto asli (full resolution tanpa crop) ke koleksi media agar bisa diubah frame-nya di kemudian hari
        if ($request->hasFile('original_thumbnail')) {
            $origFile = $request->file('original_thumbnail');
            $origPath = $origFile->store('articles/originals', 'public');
            $article->media()->create([
                'collection' => 'original_thumbnail',
                'disk' => 'public',
                'path' => $origPath,
                'original_name' => $origFile->getClientOriginalName(),
                'mime_type' => $origFile->getClientMimeType(),
                'size' => $origFile->getSize(),
                'uploaded_by' => auth()->id(),
            ]);
        }

        return redirect()->route('admin.cms.articles')->with('success', 'Artikel baru berhasil dibuat dan disimpan.');
    }

    public function editArticle(Article $article): View
    {
        $categories = ArticleCategory::orderBy('name')->get();

        return view('admin.cms.articles.edit', compact('article', 'categories'));
    }

    public function updateArticle(Request $request, Article $article): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:article_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:250'],
            'content' => ['required', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'original_thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:8192'],
            'remove_thumbnail' => ['nullable', 'boolean'],
            'status' => ['required', 'in:DRAFT,PUBLISHED'],
        ]);

        // Update slug otomatis jika judul berubah
        if ($article->title !== $validated['title']) {
            $baseSlug = Str::slug($validated['title']);
            $slug = $baseSlug ?: 'artikel-'.Str::random(5);
            $counter = 1;
            while (Article::where('slug', $slug)->where('id', '!=', $article->id)->exists()) {
                $slug = $baseSlug.'-'.$counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        // Ringkasan manual dari inputan pengguna (maksimal 250 karakter)
        $validated['excerpt'] = filled($validated['excerpt'] ?? null) ? trim((string) $validated['excerpt']) : null;

        // Thumbnail handling
        if ($request->boolean('remove_thumbnail')) {
            if ($article->thumbnail) {
                Storage::disk('public')->delete($article->thumbnail);
            }
            $validated['thumbnail'] = null;

            // Hapus juga media original jika ada
            $origMedia = $article->media()->where('collection', 'original_thumbnail')->get();
            foreach ($origMedia as $m) {
                Storage::disk($m->disk)->delete($m->path);
                $m->delete();
            }
        } elseif ($request->hasFile('thumbnail')) {
            if ($article->thumbnail) {
                Storage::disk('public')->delete($article->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('articles', 'public');

            // Simpan foto asli baru jika diunggah
            if ($request->hasFile('original_thumbnail')) {
                $origMedia = $article->media()->where('collection', 'original_thumbnail')->get();
                foreach ($origMedia as $m) {
                    Storage::disk($m->disk)->delete($m->path);
                    $m->delete();
                }
                $origFile = $request->file('original_thumbnail');
                $origPath = $origFile->store('articles/originals', 'public');
                $article->media()->create([
                    'collection' => 'original_thumbnail',
                    'disk' => 'public',
                    'path' => $origPath,
                    'original_name' => $origFile->getClientOriginalName(),
                    'mime_type' => $origFile->getClientMimeType(),
                    'size' => $origFile->getSize(),
                    'uploaded_by' => auth()->id(),
                ]);
            }
        } else {
            unset($validated['thumbnail']);
        }

        // Waktu terbit otomatis saat pertama kali di-publish
        if ($validated['status'] === ContentStatus::Published->value && empty($article->published_at)) {
            $validated['published_at'] = now();
        }

        $article->update($validated);

        return redirect()->route('admin.cms.articles')->with('success', 'Artikel "'.$article->title.'" berhasil diperbarui.');
    }

    public function destroyArticle(Article $article): RedirectResponse
    {
        $title = $article->title;
        if ($article->thumbnail) {
            Storage::disk('public')->delete($article->thumbnail);
        }
        $origMedia = $article->media()->where('collection', 'original_thumbnail')->get();
        foreach ($origMedia as $m) {
            Storage::disk($m->disk)->delete($m->path);
            $m->delete();
        }
        $article->delete();

        return redirect()->route('admin.cms.articles')->with('success', 'Artikel "'.$title.'" berhasil dihapus.');
    }

    public function toggleArticleStatus(Request $request, Article $article): JsonResponse|RedirectResponse
    {
        $isCurrentlyPublished = $article->status === ContentStatus::Published;
        $newStatus = $isCurrentlyPublished ? ContentStatus::Draft : ContentStatus::Published;

        $article->status = $newStatus;
        if ($newStatus === ContentStatus::Published && empty($article->published_at)) {
            $article->published_at = now();
        }
        $article->save();

        $statusLabel = $newStatus === ContentStatus::Published ? 'dipublikasikan' : 'diubah menjadi draf';
        $message = "Status artikel \"{$article->title}\" berhasil {$statusLabel}.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $newStatus->value,
                'is_published' => $newStatus === ContentStatus::Published,
                'published_at' => $article->published_at?->translatedFormat('d M Y, H:i') ?? '-',
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function previewArticle(Article $article, PublicMediaService $mediaService): JsonResponse
    {
        $article->load(['category', 'author']);

        return response()->json([
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'category_id' => $article->category_id,
            'category_name' => $article->category?->name ?? 'Informasi',
            'category_slug' => $article->category?->slug ?? 'informasi',
            'author_id' => $article->author_id,
            'author_name' => $article->author?->name ?? 'Admin Sekolah',
            'excerpt' => $article->excerpt ?? '',
            'content' => $article->content,
            'status' => $article->status->value,
            'is_published' => $article->status === ContentStatus::Published,
            'published_at' => $article->published_at?->translatedFormat('d F Y') ?? 'Belum dipublikasikan',
            'published_at_raw' => $article->published_at?->format('Y-m-d\TH:i') ?? '',
            'views' => (int) $article->views,
            'thumbnail' => $article->thumbnail,
            'thumbnail_url' => $mediaService->forModel($article, 'thumbnail'),
        ]);
    }

    public function storeArticleCategory(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:article_categories,name'],
        ]);

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug ?: 'kategori-'.Str::random(5);
        $counter = 1;
        while (ArticleCategory::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $category = ArticleCategory::create([
            'name' => $validated['name'],
            'slug' => $slug,
        ]);
        $category->loadCount('articles');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kategori "'.$category->name.'" berhasil ditambahkan.',
                'category' => $category,
            ]);
        }

        return redirect()->route('admin.cms.articles')->with('success', 'Kategori "'.$category->name.'" berhasil ditambahkan.');
    }

    public function updateArticleCategory(Request $request, ArticleCategory $category): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:article_categories,name,'.$category->id],
        ]);

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug ?: 'kategori-'.Str::random(5);
        $counter = 1;
        while (ArticleCategory::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        $category->update([
            'name' => $validated['name'],
            'slug' => $slug,
        ]);
        $category->loadCount('articles');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kategori "'.$category->name.'" berhasil diperbarui.',
                'category' => $category,
            ]);
        }

        return redirect()->route('admin.cms.articles')->with('success', 'Kategori "'.$category->name.'" berhasil diperbarui.');
    }

    public function destroyArticleCategory(Request $request, ArticleCategory $category): JsonResponse|RedirectResponse
    {
        if ($category->articles()->exists()) {
            $count = $category->articles()->count();
            $message = "Kategori \"{$category->name}\" tidak dapat dihapus karena masih digunakan oleh {$count} artikel. Silakan pindahkan atau hapus artikel terkait terlebih dahulu.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->route('admin.cms.articles')->with('error', $message);
        }

        $name = $category->name;
        $category->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Kategori \"{$name}\" berhasil dihapus.",
            ]);
        }

        return redirect()->route('admin.cms.articles')->with('success', "Kategori \"{$name}\" berhasil dihapus.");
    }

    public function ppdb(): View
    {
        $stats = [
            'total_periods' => AdmissionPeriod::count(),
            'open_periods' => AdmissionPeriod::where('status', 'OPEN')->count(),
            'total_schedules' => AdmissionScheduleItem::count(),
            'total_paths' => AdmissionPath::count(),
        ];

        $periods = AdmissionPeriod::with(['academicYear', 'paths', 'scheduleItems', 'requirements', 'feeItems'])
            ->withCount(['paths', 'scheduleItems', 'requirements', 'feeItems'])
            ->orderByRaw('CASE WHEN status = ? THEN 0 WHEN status = ? THEN 1 ELSE 2 END', ['OPEN', 'DRAFT'])
            ->orderByDesc('registration_start')
            ->paginate(10)
            ->withQueryString();

        $academicYears = AcademicYear::latest('start_date')->get();

        return view('admin.cms.ppdb', compact('periods', 'academicYears', 'stats'));
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

        $period = AdmissionPeriod::create($validated);

        return redirect()->route('admin.cms.ppdb.periods.manage', $period)
            ->with('success', "Gelombang '{$period->title}' berhasil dibuat. Silakan atur jadwal, jalur seleksi, dan persyaratan berkas di bawah ini.");
    }

    public function manageAdmissionPeriod(AdmissionPeriod $period, Request $request): View
    {
        $period->load([
            'academicYear',
            'scheduleItems' => fn ($q) => $q->orderBy('step_number')->orderBy('sort_order'),
            'paths' => fn ($q) => $q->orderBy('sort_order'),
            'requirements' => fn ($q) => $q->orderBy('sort_order'),
            'feeItems' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        $academicYears = AcademicYear::latest('start_date')->get();
        $tab = $request->query('tab', 'period');
        if (! in_array($tab, ['period', 'schedules', 'paths', 'requirements'])) {
            $tab = 'period';
        }

        return view('admin.cms.ppdb.manage', compact('period', 'academicYears', 'tab'));
    }

    public function updateAdmissionPeriod(Request $request, AdmissionPeriod $period): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'title' => ['required', 'string', 'max:150'],
            'registration_start' => ['required', 'date'],
            'registration_end' => ['required', 'date', 'after_or_equal:registration_start'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:DRAFT,OPEN,CLOSED'],
        ]);

        $period->update($validated);

        return redirect()->route('admin.cms.ppdb.periods.manage', ['period' => $period, 'tab' => 'period'])
            ->with('success', 'Informasi utama gelombang PPDB berhasil diperbarui.');
    }

    public function destroyAdmissionPeriod(AdmissionPeriod $period): RedirectResponse
    {
        $title = $period->title;
        $period->delete();

        return redirect()->route('admin.cms.ppdb')
            ->with('success', "Gelombang '{$title}' beserta seluruh data jadwal, jalur, dan persyaratan berkas berhasil dihapus.");
    }

    public function togglePeriodStatus(Request $request, AdmissionPeriod $period): JsonResponse|RedirectResponse
    {
        $newStatus = $period->status === 'OPEN' ? 'CLOSED' : 'OPEN';
        $period->update(['status' => $newStatus]);

        $statusLabel = $newStatus === 'OPEN' ? 'dibuka (publik)' : 'ditutup';
        $message = "Status gelombang \"{$period->title}\" berhasil {$statusLabel}.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $newStatus,
                'is_open' => $newStatus === 'OPEN',
                'label' => $newStatus === 'OPEN' ? 'Dibuka' : 'Ditutup',
                'badge_class' => $newStatus === 'OPEN' ? 'badge-green' : 'badge-gray',
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function updateAdmissionSchedules(Request $request, AdmissionPeriod $period): RedirectResponse
    {
        $request->validate([
            'schedules' => ['nullable', 'array'],
            'schedules.*.id' => ['nullable', 'integer'],
            'schedules.*.title' => ['required_with:schedules', 'string', 'max:150'],
            'schedules.*.start_date' => ['required_with:schedules', 'date'],
            'schedules.*.end_date' => ['required_with:schedules', 'date'],
            'schedules.*.description' => ['nullable', 'string'],
        ]);

        $schedules = $request->input('schedules', []);

        DB::transaction(function () use ($period, $schedules) {
            $submittedIds = [];

            foreach ($schedules as $index => $itemData) {
                if (blank($itemData['title'] ?? null)) {
                    continue;
                }

                $stepNumber = $index + 1;
                $sortOrder = $index + 1;

                $data = [
                    'admission_period_id' => $period->id,
                    'title' => trim($itemData['title']),
                    'start_date' => $itemData['start_date'],
                    'end_date' => $itemData['end_date'],
                    'description' => filled($itemData['description'] ?? null) ? trim($itemData['description']) : null,
                    'step_number' => $stepNumber,
                    'sort_order' => $sortOrder,
                ];

                if (! empty($itemData['id'])) {
                    $item = AdmissionScheduleItem::where('admission_period_id', $period->id)->find($itemData['id']);
                    if ($item) {
                        $item->update($data);
                        $submittedIds[] = $item->id;

                        continue;
                    }
                }

                $created = AdmissionScheduleItem::create($data);
                $submittedIds[] = $created->id;
            }

            AdmissionScheduleItem::where('admission_period_id', $period->id)
                ->whereNotIn('id', $submittedIds)
                ->delete();
        });

        return redirect()->route('admin.cms.ppdb.periods.manage', ['period' => $period, 'tab' => 'schedules'])
            ->with('success', 'Jadwal pendaftaran berhasil disimpan dan urutan posisi telah diperbarui.');
    }

    public function updateAdmissionPaths(Request $request, AdmissionPeriod $period): RedirectResponse
    {
        $request->validate([
            'paths' => ['nullable', 'array'],
            'paths.*.id' => ['nullable', 'integer'],
            'paths.*.name' => ['required_with:paths', 'string', 'max:100'],
            'paths.*.quota' => ['nullable', 'integer', 'min:0'],
            'paths.*.description' => ['nullable', 'string'],
            'paths.*.is_active' => ['nullable'],
        ]);

        $paths = $request->input('paths', []);

        DB::transaction(function () use ($period, $paths) {
            $submittedIds = [];

            foreach ($paths as $index => $pathData) {
                if (blank($pathData['name'] ?? null)) {
                    continue;
                }

                $name = trim($pathData['name']);
                $pathId = ! empty($pathData['id']) ? (int) $pathData['id'] : null;

                $baseSlug = Str::slug($name) ?: 'jalur-'.Str::random(6);
                $slug = $baseSlug;
                $counter = 1;
                while (
                    AdmissionPath::where('admission_period_id', $period->id)
                        ->where('slug', $slug)
                        ->when($pathId, fn ($q) => $q->where('id', '!=', $pathId))
                        ->exists()
                ) {
                    $slug = $baseSlug.'-'.$counter;
                    $counter++;
                }

                $data = [
                    'admission_period_id' => $period->id,
                    'name' => $name,
                    'slug' => $slug,
                    'quota' => filled($pathData['quota'] ?? null) ? (int) $pathData['quota'] : null,
                    'description' => filled($pathData['description'] ?? null) ? trim($pathData['description']) : null,
                    'is_active' => isset($pathData['is_active']) && (bool) $pathData['is_active'],
                    'sort_order' => $index + 1,
                ];

                if ($pathId) {
                    $item = AdmissionPath::where('admission_period_id', $period->id)->find($pathId);
                    if ($item) {
                        $item->update($data);
                        $submittedIds[] = $item->id;

                        continue;
                    }
                }

                $created = AdmissionPath::create($data);
                $submittedIds[] = $created->id;
            }

            AdmissionPath::where('admission_period_id', $period->id)
                ->whereNotIn('id', $submittedIds)
                ->delete();
        });

        return redirect()->route('admin.cms.ppdb.periods.manage', ['period' => $period, 'tab' => 'paths'])
            ->with('success', 'Jalur seleksi PPDB berhasil disimpan.');
    }

    public function updateAdmissionRequirements(Request $request, AdmissionPeriod $period): RedirectResponse
    {
        $request->validate([
            'requirements' => ['nullable', 'array'],
            'requirements.*.id' => ['nullable', 'integer'],
            'requirements.*.title' => ['required_with:requirements', 'string', 'max:200'],
            'requirements.*.description' => ['nullable', 'string'],
        ]);

        $requirements = $request->input('requirements', []);

        DB::transaction(function () use ($period, $requirements) {
            $submittedIds = [];

            foreach ($requirements as $index => $reqData) {
                if (blank($reqData['title'] ?? null)) {
                    continue;
                }

                $data = [
                    'admission_period_id' => $period->id,
                    'title' => trim($reqData['title']),
                    'description' => filled($reqData['description'] ?? null) ? trim($reqData['description']) : null,
                    'sort_order' => $index + 1,
                ];

                if (! empty($reqData['id'])) {
                    $item = AdmissionRequirement::where('admission_period_id', $period->id)->find($reqData['id']);
                    if ($item) {
                        $item->update($data);
                        $submittedIds[] = $item->id;

                        continue;
                    }
                }

                $created = AdmissionRequirement::create($data);
                $submittedIds[] = $created->id;
            }

            AdmissionRequirement::where('admission_period_id', $period->id)
                ->whereNotIn('id', $submittedIds)
                ->delete();
        });

        return redirect()->route('admin.cms.ppdb.periods.manage', ['period' => $period, 'tab' => 'requirements'])
            ->with('success', 'Persyaratan pendaftaran PPDB berhasil disimpan.');
    }

    public function updateAdmissionFees(Request $request, AdmissionPeriod $period): RedirectResponse
    {
        $request->validate([
            'fees' => ['nullable', 'array'],
            'fees.*.id' => ['nullable', 'integer'],
            'fees.*.name' => ['required_with:fees', 'string', 'max:150'],
            'fees.*.amount' => ['nullable', 'numeric', 'min:0'],
            'fees.*.is_free' => ['nullable'],
            'fees.*.description' => ['nullable', 'string'],
        ]);

        $fees = $request->input('fees', []);

        DB::transaction(function () use ($period, $fees) {
            $submittedIds = [];

            foreach ($fees as $index => $feeData) {
                if (blank($feeData['name'] ?? null)) {
                    continue;
                }

                $isFree = isset($feeData['is_free']) && (bool) $feeData['is_free'];
                $amount = $isFree ? 0.00 : (float) ($feeData['amount'] ?? 0);

                $data = [
                    'admission_period_id' => $period->id,
                    'name' => trim($feeData['name']),
                    'amount' => $amount,
                    'is_free' => $isFree,
                    'description' => filled($feeData['description'] ?? null) ? trim($feeData['description']) : null,
                    'sort_order' => $index + 1,
                ];

                if (! empty($feeData['id'])) {
                    $item = AdmissionFeeItem::where('admission_period_id', $period->id)->find($feeData['id']);
                    if ($item) {
                        $item->update($data);
                        $submittedIds[] = $item->id;

                        continue;
                    }
                }

                $created = AdmissionFeeItem::create($data);
                $submittedIds[] = $created->id;
            }

            AdmissionFeeItem::where('admission_period_id', $period->id)
                ->whereNotIn('id', $submittedIds)
                ->delete();
        });

        return redirect()->route('admin.cms.ppdb.periods.manage', ['period' => $period, 'tab' => 'fees'])
            ->with('success', 'Rincian biaya pendaftaran PPDB berhasil disimpan.');
    }

    public function achievements(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $categoryId = $request->input('category_id');
        $level = $request->input('level');
        $isFeatured = $request->input('is_featured');

        $query = Achievement::with(['category', 'participants.student', 'media'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('organizer', 'like', "%{$search}%")
                        ->orWhere('rank', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('participants.student', function ($sq) use ($search) {
                            $sq->where('full_name', 'like', "%{$search}%")
                                ->orWhere('nis', 'like', "%{$search}%");
                        });
                });
            })
            ->when(! empty($categoryId), function ($q) use ($categoryId) {
                $q->where('achievement_category_id', $categoryId);
            })
            ->when(! empty($level), function ($q) use ($level) {
                $q->where('level', $level);
            })
            ->when($isFeatured !== null && $isFeatured !== '', function ($q) use ($isFeatured) {
                $q->where('is_featured', (bool) $isFeatured);
            })
            ->orderByDesc('is_featured')
            ->orderByDesc('achievement_date')
            ->orderByDesc('id');

        $achievements = $query->paginate(10)->withQueryString();

        $categories = AchievementCategory::orderBy('name')->get();
        $students = StudentProfile::orderBy('full_name')->get(['id', 'full_name', 'nis']);

        $stats = [
            'total' => Achievement::count(),
            'featured' => Achievement::where('is_featured', true)->count(),
            'national_intl' => Achievement::whereIn('level', ['Nasional', 'Internasional', 'NASIONAL', 'INTERNASIONAL'])->count(),
            'categories' => AchievementCategory::count(),
        ];

        return view('admin.cms.achievements', compact(
            'achievements',
            'categories',
            'students',
            'stats',
            'search',
            'categoryId',
            'level',
            'isFeatured'
        ));
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
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['exists:student_profiles,id'],
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');

        $achievement = Achievement::create($validated);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $path = $file->store('achievements', 'public');

            $achievement->media()->create([
                'collection' => 'photo',
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $request->user()?->id ?? auth()->id(),
            ]);
        }

        if (! empty($validated['student_ids'])) {
            foreach ($validated['student_ids'] as $studentId) {
                $achievement->participants()->create([
                    'student_id' => $studentId,
                    'role' => 'Peserta',
                ]);
            }
        }

        return redirect()->route('admin.cms.achievements')->with('success', 'Prestasi baru berhasil ditambahkan.');
    }

    public function updateAchievement(Request $request, Achievement $achievement): RedirectResponse
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
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_photo' => ['nullable', 'boolean'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['exists:student_profiles,id'],
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');

        $achievement->update($validated);

        if ($request->hasFile('photo')) {
            foreach ($achievement->media as $oldMedia) {
                Storage::disk($oldMedia->disk ?: 'public')->delete($oldMedia->path);
                $oldMedia->delete();
            }

            $file = $request->file('photo');
            $path = $file->store('achievements', 'public');

            $achievement->media()->create([
                'collection' => 'photo',
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $request->user()?->id ?? auth()->id(),
            ]);
        } elseif ($request->boolean('remove_photo')) {
            foreach ($achievement->media as $oldMedia) {
                Storage::disk($oldMedia->disk ?: 'public')->delete($oldMedia->path);
                $oldMedia->delete();
            }
        }

        if ($request->has('student_ids') || $request->boolean('sync_students')) {
            $achievement->participants()->delete();
            if (! empty($validated['student_ids'])) {
                foreach ($validated['student_ids'] as $studentId) {
                    $achievement->participants()->create([
                        'student_id' => $studentId,
                        'role' => 'Peserta',
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Data prestasi berhasil diperbarui.');
    }

    public function destroyAchievement(Achievement $achievement): RedirectResponse
    {
        foreach ($achievement->media as $media) {
            Storage::disk($media->disk ?: 'public')->delete($media->path);
            $media->delete();
        }

        $achievement->participants()->delete();
        $achievement->delete();

        return redirect()->back()->with('success', 'Data prestasi berhasil dihapus.');
    }

    public function togglePinAchievement(Request $request, Achievement $achievement): JsonResponse|RedirectResponse
    {
        $achievement->update([
            'is_featured' => ! $achievement->is_featured,
        ]);

        $message = $achievement->is_featured
            ? 'Prestasi "'.$achievement->title.'" disematkan sebagai unggulan di Beranda.'
            : 'Sematkan prestasi "'.$achievement->title.'" telah dilepas.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_featured' => (bool) $achievement->is_featured,
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function previewAchievement(Achievement $achievement, PublicMediaService $mediaService): JsonResponse
    {
        $achievement->load(['category', 'participants.student']);

        return response()->json([
            'id' => $achievement->id,
            'title' => $achievement->title,
            'category_id' => $achievement->achievement_category_id,
            'category_name' => $achievement->category?->name ?? 'Prestasi',
            'scope' => $achievement->scope,
            'level' => $achievement->level,
            'rank' => $achievement->rank ?? '-',
            'organizer' => $achievement->organizer ?? '-',
            'achievement_date' => $achievement->achievement_date?->format('Y-m-d'),
            'achievement_date_formatted' => $achievement->achievement_date?->translatedFormat('d F Y'),
            'description' => $achievement->description ?? '',
            'is_featured' => (bool) $achievement->is_featured,
            'photo_url' => $mediaService->forModel($achievement),
            'participants' => $achievement->participants->map(fn ($p) => [
                'id' => $p->student_id,
                'name' => $p->student?->full_name ?? 'Siswa',
                'nis' => $p->student?->nis ?? '-',
                'role' => $p->role ?? 'Peserta',
            ]),
        ]);
    }

    public function products(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $categoryId = $request->input('category_id');
        $departmentId = $request->input('department_id');
        $status = $request->input('status');

        $query = StudentProduct::with(['category', 'department', 'students', 'media'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('contact', 'like', "%{$search}%")
                        ->orWhereHas('students', function ($sq) use ($search) {
                            $sq->where('full_name', 'like', "%{$search}%")
                                ->orWhere('nis', 'like', "%{$search}%");
                        });
                });
            })
            ->when(! empty($categoryId), fn ($q) => $q->where('category_id', $categoryId))
            ->when(! empty($departmentId), fn ($q) => $q->where('department_id', $departmentId))
            ->when(! empty($status), fn ($q) => $q->where('status', $status))
            ->orderByDesc('id');

        $products = $query->paginate(10)->withQueryString();

        $categories = ProductCategory::orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $students = StudentProfile::orderBy('full_name')->get(['id', 'full_name', 'nis']);

        $stats = [
            'total' => StudentProduct::count(),
            'available' => StudentProduct::where('status', 'AVAILABLE')->count(),
            'pre_order' => StudentProduct::where('status', 'PRE_ORDER')->count(),
            'out_of_stock' => StudentProduct::where('status', 'OUT_OF_STOCK')->count(),
            'categories' => ProductCategory::count(),
        ];

        return view('admin.cms.products', compact(
            'products',
            'categories',
            'departments',
            'students',
            'stats',
            'search',
            'categoryId',
            'departmentId',
            'status'
        ));
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
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['exists:student_profiles,id'],
        ]);

        $baseSlug = Str::slug($validated['name']) ?: 'produk';
        $slug = $baseSlug;
        $counter = 1;
        while (StudentProduct::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }
        $validated['slug'] = $slug;

        $product = StudentProduct::create($validated);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $path = $file->store('products', 'public');

            $product->media()->create([
                'collection' => 'photo',
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $request->user()?->id ?? auth()->id(),
            ]);
        }

        if (! empty($validated['student_ids'])) {
            $product->students()->sync($validated['student_ids']);
        }

        return redirect()->route('admin.cms.products')->with('success', 'Produk kreatif siswa berhasil didaftarkan.');
    }

    public function updateProduct(Request $request, StudentProduct $product): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:product_categories,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:150'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'contact' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:AVAILABLE,OUT_OF_STOCK,PRE_ORDER'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_photo' => ['nullable', 'boolean'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['exists:student_profiles,id'],
        ]);

        if ($product->name !== $validated['name']) {
            $baseSlug = Str::slug($validated['name']) ?: 'produk';
            $slug = $baseSlug;
            $counter = 1;
            while (StudentProduct::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $baseSlug.'-'.$counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        $product->update($validated);

        if ($request->hasFile('photo')) {
            foreach ($product->media as $oldMedia) {
                Storage::disk($oldMedia->disk ?: 'public')->delete($oldMedia->path);
                $oldMedia->delete();
            }

            $file = $request->file('photo');
            $path = $file->store('products', 'public');

            $product->media()->create([
                'collection' => 'photo',
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => $request->user()?->id ?? auth()->id(),
            ]);
        } elseif ($request->boolean('remove_photo')) {
            foreach ($product->media as $oldMedia) {
                Storage::disk($oldMedia->disk ?: 'public')->delete($oldMedia->path);
                $oldMedia->delete();
            }
        }

        if ($request->has('student_ids') || $request->boolean('sync_students')) {
            $product->students()->sync($validated['student_ids'] ?? []);
        }

        return redirect()->back()->with('success', 'Data produk siswa berhasil diperbarui.');
    }

    public function destroyProduct(StudentProduct $product): RedirectResponse
    {
        foreach ($product->media as $media) {
            Storage::disk($media->disk ?: 'public')->delete($media->path);
            $media->delete();
        }

        $product->students()->detach();
        $product->delete();

        return redirect()->back()->with('success', 'Produk siswa berhasil dihapus.');
    }

    public function previewProduct(StudentProduct $product, PublicMediaService $mediaService): JsonResponse
    {
        $product->load(['category', 'department', 'students', 'media']);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'category_id' => $product->category_id,
            'category_name' => $product->category?->name ?? 'Produk',
            'department_id' => $product->department_id,
            'department_name' => $product->department?->name ?? 'Lintas Jurusan',
            'department_code' => $product->department?->short_name ?: $product->department?->code,
            'price' => $product->price ? (float) $product->price : null,
            'formatted_price' => $product->price ? 'Rp '.number_format((float) $product->price, 0, ',', '.') : 'Tanyakan ke penjual',
            'status' => $product->status,
            'status_label' => match ($product->status) {
                'AVAILABLE' => 'Tersedia (Ready)',
                'PRE_ORDER' => 'Pre-Order',
                'OUT_OF_STOCK' => 'Stok Habis',
                default => $product->status,
            },
            'contact' => $product->contact ?? '',
            'description' => $product->description ?? '',
            'photo_url' => $mediaService->forModel($product),
            'students' => $product->students->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->full_name,
                'nis' => $s->nis,
            ]),
        ]);
    }

    public function career(Request $request): View
    {
        $activeTab = $request->input('tab', 'opportunities');
        if (! in_array($activeTab, ['opportunities', 'companies', 'services'], true)) {
            $activeTab = 'opportunities';
        }

        // Search & Filters for Opportunities
        $searchOpp = $request->string('search_opp')->trim()->toString();
        $oppType = $request->input('type');
        $oppStatus = $request->input('status');
        $oppCompanyId = $request->input('company_id');

        $oppQuery = CareerOpportunity::with(['company', 'applications.student'])
            ->when($searchOpp !== '', function ($q) use ($searchOpp) {
                $q->where(function ($sub) use ($searchOpp) {
                    $sub->where('title', 'like', "%{$searchOpp}%")
                        ->orWhere('description', 'like', "%{$searchOpp}%")
                        ->orWhere('requirements', 'like', "%{$searchOpp}%")
                        ->orWhere('location', 'like', "%{$searchOpp}%")
                        ->orWhereHas('company', fn ($cq) => $cq->where('name', 'like', "%{$searchOpp}%"));
                });
            })
            ->when(! empty($oppType), fn ($q) => $q->where('type', $oppType))
            ->when(! empty($oppStatus), fn ($q) => $q->where('status', $oppStatus))
            ->when(! empty($oppCompanyId), fn ($q) => $q->where('company_id', $oppCompanyId))
            ->orderByDesc('id');

        $opportunities = $oppQuery->paginate(10, ['*'], 'opp_page')->withQueryString();

        // Search & List for Companies
        $searchComp = $request->string('search_comp')->trim()->toString();
        $compQuery = CareerCompany::withCount('opportunities')
            ->when($searchComp !== '', function ($q) use ($searchComp) {
                $q->where(function ($sub) use ($searchComp) {
                    $sub->where('name', 'like', "%{$searchComp}%")
                        ->orWhere('industry', 'like', "%{$searchComp}%")
                        ->orWhere('address', 'like', "%{$searchComp}%")
                        ->orWhere('email', 'like', "%{$searchComp}%")
                        ->orWhere('phone', 'like', "%{$searchComp}%");
                });
            })
            ->orderBy('name');

        $companies = $compQuery->paginate(10, ['*'], 'comp_page')->withQueryString();
        $allCompanies = CareerCompany::orderBy('name')->get();

        // Career Services
        $services = CareerService::orderBy('sort_order')->orderBy('id')->get();

        $stats = [
            'total_opportunities' => CareerOpportunity::count(),
            'job_count' => CareerOpportunity::where('type', CareerOpportunityType::Job->value)->count(),
            'internship_count' => CareerOpportunity::where('type', CareerOpportunityType::Internship->value)->count(),
            'open_opportunities' => CareerOpportunity::where('status', CareerOpportunityStatus::Open->value)->count(),
            'total_companies' => CareerCompany::count(),
            'total_services' => CareerService::count(),
        ];

        return view('admin.cms.career', compact(
            'opportunities',
            'companies',
            'allCompanies',
            'services',
            'stats',
            'activeTab',
            'searchOpp',
            'oppType',
            'oppStatus',
            'oppCompanyId',
            'searchComp'
        ));
    }

    public function storeCareerOpportunity(Request $request): RedirectResponse
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
            'application_link' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'in:OPEN,CLOSED'],
        ]);

        CareerOpportunity::create($validated);

        return redirect()->route('admin.cms.career', ['tab' => 'opportunities'])
            ->with('success', 'Lowongan pekerjaan/magang BKK berhasil dipublikasikan.');
    }

    public function storeCareer(Request $request): RedirectResponse
    {
        return $this->storeCareerOpportunity($request);
    }

    public function updateCareerOpportunity(Request $request, CareerOpportunity $opportunity): RedirectResponse
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
            'application_link' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'in:OPEN,CLOSED'],
        ]);

        $opportunity->update($validated);

        return redirect()->back()->with('success', 'Data lowongan karir berhasil diperbarui.');
    }

    public function destroyCareerOpportunity(CareerOpportunity $opportunity): RedirectResponse
    {
        $opportunity->delete();

        return redirect()->back()->with('success', 'Lowongan karir berhasil dihapus.');
    }

    public function toggleCareerOpportunityStatus(Request $request, CareerOpportunity $opportunity): JsonResponse|RedirectResponse
    {
        $currentValue = $opportunity->status instanceof CareerOpportunityStatus ? $opportunity->status->value : $opportunity->status;
        $newStatus = ($currentValue === CareerOpportunityStatus::Open->value || $currentValue === 'OPEN')
            ? CareerOpportunityStatus::Closed
            : CareerOpportunityStatus::Open;

        $opportunity->update(['status' => $newStatus]);

        $message = $newStatus === CareerOpportunityStatus::Open
            ? 'Lowongan "'.$opportunity->title.'" dibuka kembali.'
            : 'Lowongan "'.$opportunity->title.'" telah ditutup.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $newStatus->value,
                'status_label' => $newStatus === CareerOpportunityStatus::Open ? 'Dibuka (Open)' : 'Ditutup',
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function previewCareerOpportunity(CareerOpportunity $opportunity, PublicMediaService $mediaService): JsonResponse
    {
        $opportunity->load(['company', 'applications.student']);

        $typeValue = $opportunity->type instanceof CareerOpportunityType ? $opportunity->type->value : $opportunity->type;
        $typeLabel = $opportunity->type instanceof CareerOpportunityType ? $opportunity->type->label() : ($typeValue === 'JOB' ? 'Lowongan Kerja' : 'Magang PKL');

        $statusValue = $opportunity->status instanceof CareerOpportunityStatus ? $opportunity->status->value : $opportunity->status;

        return response()->json([
            'id' => $opportunity->id,
            'title' => $opportunity->title,
            'company_id' => $opportunity->company_id,
            'company_name' => $opportunity->company?->name ?? 'Mitra Industri',
            'company_industry' => $opportunity->company?->industry ?? '',
            'company_logo' => $opportunity->company ? $mediaService->forModel($opportunity->company, 'logo') : null,
            'type' => $typeValue,
            'type_label' => $typeLabel,
            'location' => $opportunity->location ?? '',
            'open_date' => $opportunity->open_date?->format('Y-m-d'),
            'open_date_formatted' => $opportunity->open_date?->translatedFormat('d M Y'),
            'close_date' => $opportunity->close_date?->format('Y-m-d'),
            'close_date_formatted' => $opportunity->close_date?->translatedFormat('d M Y'),
            'application_link' => $opportunity->application_link ?? '',
            'status' => $statusValue,
            'status_label' => $statusValue === 'OPEN' ? 'Dibuka (Open)' : 'Ditutup',
            'description' => $opportunity->description ?? '',
            'requirements' => $opportunity->requirements ?? '',
            'applications_count' => $opportunity->applications->count(),
        ]);
    }

    public function storeCareerCompany(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'industry' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'website' => ['nullable', 'url', 'max:150'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('career/companies', 'public');
            $validated['logo'] = $path;
        }

        CareerCompany::create($validated);

        return redirect()->route('admin.cms.career', ['tab' => 'companies'])
            ->with('success', 'Mitra perusahaan baru berhasil ditambahkan.');
    }

    public function updateCareerCompany(Request $request, CareerCompany $company): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'industry' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'website' => ['nullable', 'url', 'max:150'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('logo')) {
            if (filled($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }
            $path = $request->file('logo')->store('career/companies', 'public');
            $validated['logo'] = $path;
        } elseif ($request->boolean('remove_logo')) {
            if (filled($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }
            $validated['logo'] = null;
        }

        $company->update($validated);

        return redirect()->route('admin.cms.career', ['tab' => 'companies'])
            ->with('success', 'Data mitra perusahaan berhasil diperbarui.');
    }

    public function destroyCareerCompany(CareerCompany $company): RedirectResponse
    {
        if (filled($company->logo)) {
            Storage::disk('public')->delete($company->logo);
        }

        $company->delete();

        return redirect()->route('admin.cms.career', ['tab' => 'companies'])
            ->with('success', 'Data mitra perusahaan berhasil dihapus.');
    }

    public function storeCareerService(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'content' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $baseSlug = Str::slug($validated['title']) ?: 'layanan';
        $slug = $baseSlug;
        $counter = 1;
        while (CareerService::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }
        $validated['slug'] = $slug;
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        CareerService::create($validated);

        return redirect()->route('admin.cms.career', ['tab' => 'services'])
            ->with('success', 'Layanan BKK baru berhasil ditambahkan.');
    }

    public function updateCareerService(Request $request, CareerService $service): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'content' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($service->title !== $validated['title']) {
            $baseSlug = Str::slug($validated['title']) ?: 'layanan';
            $slug = $baseSlug;
            $counter = 1;
            while (CareerService::where('slug', $slug)->where('id', '!=', $service->id)->exists()) {
                $slug = $baseSlug.'-'.$counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        $service->update($validated);

        return redirect()->route('admin.cms.career', ['tab' => 'services'])
            ->with('success', 'Data layanan BKK berhasil diperbarui.');
    }

    public function destroyCareerService(CareerService $service): RedirectResponse
    {
        $service->delete();

        return redirect()->route('admin.cms.career', ['tab' => 'services'])
            ->with('success', 'Layanan BKK berhasil dihapus.');
    }

    public function toggleCareerServiceStatus(Request $request, CareerService $service): JsonResponse|RedirectResponse
    {
        $service->update([
            'is_active' => ! $service->is_active,
        ]);

        $message = $service->is_active
            ? 'Layanan "'.$service->title.'" diaktifkan.'
            : 'Layanan "'.$service->title.'" dinonaktifkan.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => (bool) $service->is_active,
                'message' => $message,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }
}
