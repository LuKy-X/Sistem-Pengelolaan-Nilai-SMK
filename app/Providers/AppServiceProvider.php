<?php

namespace App\Providers;

use App\View\Composers\PublicSiteComposer;
use Illuminate\Pagination\Paginator;
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
        Paginator::useTailwind();
        $this->composePublicViews();
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
}
