<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Resolves stored media paths into publicly reachable URLs for the public site.
 *
 * The CMS stores images either as a plain path column (for example
 * `articles.thumbnail`, `school_profile.logo`) or inside the polymorphic
 * `media` table. Nothing in the CMS is required to have an image, so every
 * resolver returns `null` when there is nothing to show and the view is
 * expected to render a branded placeholder instead.
 */
class PublicMediaService
{
    /**
     * Turn a stored path into a public URL.
     *
     * Absolute URLs, protocol-relative URLs, and root-relative paths are
     * passed through untouched.
     */
    public function url(?string $path, ?string $disk = null): ?string
    {
        if (blank($path)) {
            return null;
        }

        $path = str_replace('\\', '/', trim($path));

        if (Str::startsWith($path, ['http://', 'https://', '//', '/', 'data:'])) {
            return $path;
        }

        $path = preg_replace('#^(?:storage/app/public|storage|public)/+#i', '', $path) ?? $path;
        $disk ??= 'public';

        try {
            $url = Storage::disk($disk)->url($path);
        } catch (Throwable) {
            return null;
        }

        return filled($url) ? $url : null;
    }

    /**
     * Resolve the best available image for a model.
     *
     * A dedicated path column wins over the polymorphic `media` table, and a
     * named media collection wins over an arbitrary one.
     */
    public function forModel(Model $model, ?string $column = null, ?string $collection = null): ?string
    {
        if ($column !== null && filled($model->getAttribute($column))) {
            return $this->url((string) $model->getAttribute($column));
        }

        if (! method_exists($model, 'media')) {
            return null;
        }

        if ($model->relationLoaded('media')) {
            $media = $model->getRelation('media')
                ->sortByDesc('id')
                ->first(fn ($item): bool => $collection === null || $item->collection === $collection);

            if ($media === null && $collection !== null) {
                $media = $model->getRelation('media')->sortByDesc('id')->first();
            }
        } else {
            $query = $model->media();

            if ($collection !== null) {
                $query->where('collection', $collection);
            }

            $media = $query->orderByDesc('id')->first();

            if ($media === null && $collection !== null) {
                $media = $model->media()->orderByDesc('id')->first();
            }
        }

        return $media === null ? null : $this->url($media->path, $media->disk);
    }
}
