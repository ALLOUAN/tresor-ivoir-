<?php

namespace App\Http\Controllers;

use App\Models\MediaPurchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VisitorPurchaseController extends Controller
{
    public function index(): View
    {
        $purchases = MediaPurchase::with('media')
            ->where('user_id', Auth::id())
            ->where('status', 'completed')
            ->latest('paid_at')
            ->paginate(20);

        return view('visitor.purchases', compact('purchases'));
    }

    public function download(MediaPurchase $purchase): BinaryFileResponse
    {
        abort_if(
            $purchase->user_id !== Auth::id() || $purchase->status !== 'completed',
            403
        );

        $media = $purchase->media;
        abort_if(! $media || ! $media->file_path, 404);
        abort_unless(Storage::disk('public')->exists($media->file_path), 404);

        $filename = ($media->title ?? pathinfo($media->original_name, PATHINFO_FILENAME))
            . '.' . pathinfo($media->file_path, PATHINFO_EXTENSION);

        return response()->download(Storage::disk('public')->path($media->file_path), $filename);
    }
}
