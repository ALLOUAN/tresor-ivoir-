<?php

namespace App\Http\Controllers;

use App\Models\SiteMediaItem;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PublicHomeGalleryController extends Controller
{
    public function index(): View
    {
        return view('gallery.public', [
            'galleryImages' => SiteMediaItem::publicHomeGalleryImages(60),
        ]);
    }

    public function show(string $uuid): View
    {
        if (! Schema::hasTable('site_media_items')) {
            abort(404);
        }

        $media = SiteMediaItem::query()
            ->forPublicHomeGallery()
            ->where('uuid', $uuid)
            ->with('uploader:id,first_name,last_name')
            ->firstOrFail();

        $t = trim((string) ($media->title ?? ''));
        $pageTitle = $t !== '' ? $t : trim((string) ($media->original_name ?? 'Visuel'));

        return view('gallery.show', [
            'media' => $media,
            'pageTitle' => $pageTitle,
        ]);
    }
}
