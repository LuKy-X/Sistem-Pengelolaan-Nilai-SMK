<?php

namespace App\View\Composers;

use App\Services\PublicSiteService;
use Illuminate\Contracts\View\View;

/**
 * Shares the school identity with every public-facing view.
 *
 * The navbar, hero and footer all render the school identity, so it is injected
 * here once instead of being threaded through each public controller. Scoped to
 * the public layouts, the `public.*` view namespace, the public components and
 * the auth views so dashboard views are never affected.
 */
class PublicSiteComposer
{
    public function __construct(private readonly PublicSiteService $publicSite) {}

    public function compose(View $view): void
    {
        $view->with([
            'schoolProfile' => $this->publicSite->profile(),
            'schoolName' => $this->publicSite->schoolName(),
        ]);
    }
}
