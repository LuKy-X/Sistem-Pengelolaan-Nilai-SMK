<?php

namespace App\Providers;

use App\Models\Assessment;
use App\Models\ExitPermit;
use App\Models\GradebookScore;
use App\Observers\AssessmentObserver;
use App\Observers\ExitPermitObserver;
use App\Observers\GradebookScoreObserver;
use App\View\Composers\PublicSiteComposer;
use App\View\Composers\StudentNotificationCount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
        Model::automaticallyEagerLoadRelationships();

        Model::handleLazyLoadingViolationUsing(function (Model $model, string $relation) {
            Log::warning(sprintf(
                'N+1 Lazy loading detected on model [%s] relation [%s]',
                get_class($model),
                $relation
            ));
        });

        Paginator::useTailwind();
        $this->composePublicViews();
        $this->composeStudentViews();
        $this->registerNotificationObservers();
    }

    /**
     * Share the school profile with the public website views, so the branding
     * in the layout shells always matches the `school_profiles` record. The
     * authenticated dashboards keep their own data scope.
     *
     * Component view names must be listed explicitly because a `public.*`
     * wildcard only matches views whose dotted name starts with `public.`.
     * The error views (`errors.*`) are deliberately absent: Laravel renders
     * them outside the composer pipeline, so they fall back to `config('app.name')`
     * instead of `$schoolName`.
     */
    private function composePublicViews(): void
    {
        View::composer([
            'layouts.public',
            'layouts.guest',
            'layouts.teacher',
            'public.*',
            'components.public.*',
            'auth.*',
        ], PublicSiteComposer::class);
    }

    /**
     * Lencana jumlah notifikasi hanya dibutuhkan oleh layout portal siswa, jadi
     * composer ini sengaja tidak dipasang pada layout Guru atau Guru BK.
     */
    private function composeStudentViews(): void
    {
        View::composer('layouts.student', StudentNotificationCount::class);
    }

    /**
     * Notifikasi siswa dipicu dari model, bukan dari controller Guru atau Guru BK.
     * Cara ini membuat notifikasi tetap bekerja tanpa mengubah alur kedua modul
     * tersebut sama sekali.
     */
    private function registerNotificationObservers(): void
    {
        Assessment::observe(AssessmentObserver::class);
        ExitPermit::observe(ExitPermitObserver::class);
        GradebookScore::observe(GradebookScoreObserver::class);
    }
}
