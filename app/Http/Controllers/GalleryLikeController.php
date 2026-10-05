<?php

namespace App\Http\Controllers;

use App\Models\SiteMediaItem;
use App\Services\GalleryLikeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GalleryLikeController extends Controller
{
    public function __construct(
        private readonly GalleryLikeService $likes,
    ) {}

    public function toggle(Request $request, string $uuid): JsonResponse
    {
        $media = SiteMediaItem::query()
            ->forPublicHomeGallery()
            ->where('uuid', $uuid)
            ->firstOrFail();

        $likerKey = $this->likes->resolveLikerKey($request);
        $liked = $this->likes->toggle($media, $likerKey);

        return response()->json([
            'liked' => $liked,
            'likes_count' => $media->fresh()->likes_count,
        ]);
    }
}
