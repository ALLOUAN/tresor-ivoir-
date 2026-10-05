<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageBubble;
use App\Models\HomepageBubbleImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HomepageBubbleManagementController extends Controller
{
    public function index(): View
    {
        $bubbles = HomepageBubble::query()->with('images')->ordered()->paginate(20);

        return view('admin.homepage-bubbles.index', compact('bubbles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->sanitizeEmptyUploads($request, ['image_file']);

        $data = $this->validateBubble($request);
        $data['is_active'] = $request->boolean('is_active', true);

        $bubble = HomepageBubble::create($data);

        if ($this->hasUsableUploadedFile($request, 'image_file')) {
            $this->assertValidImage($request->file('image_file'), 'image_file', 5120);
            HomepageBubbleImage::create([
                'homepage_bubble_id' => $bubble->id,
                'image_url' => $this->storeUploadedFile($request->file('image_file'), 'homepage-bubbles', 'bubble'),
                'display_order' => 0,
            ]);
        }

        return back()->with('success', 'Bulle créée avec succès.');
    }

    public function update(Request $request, HomepageBubble $bubble): RedirectResponse
    {
        $this->sanitizeEmptyUploads($request, ['image_file']);

        $data = $this->validateBubble($request);
        $data['is_active'] = $request->boolean('is_active');

        $bubble->update($data);

        if ($this->hasUsableUploadedFile($request, 'image_file')) {
            $this->assertValidImage($request->file('image_file'), 'image_file', 5120);
            HomepageBubbleImage::create([
                'homepage_bubble_id' => $bubble->id,
                'image_url' => $this->storeUploadedFile($request->file('image_file'), 'homepage-bubbles', 'bubble'),
                'display_order' => (int) ($bubble->images()->max('display_order') + 1),
            ]);
        }

        return back()->with('success', 'Bulle modifiée avec succès.');
    }

    public function toggle(HomepageBubble $bubble): RedirectResponse
    {
        $bubble->update(['is_active' => ! $bubble->is_active]);

        return back()->with('success', $bubble->is_active ? 'Bulle activée.' : 'Bulle désactivée.');
    }

    public function destroy(HomepageBubble $bubble): RedirectResponse
    {
        foreach ($bubble->images as $image) {
            $this->deleteStoredFile($image->image_url);
        }
        $bubble->delete();

        return back()->with('success', 'Bulle supprimée.');
    }

    private function validateBubble(Request $request): array
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'link_url' => ['nullable', 'string', 'max:500'],
            'link_label' => ['nullable', 'string', 'max:150'],
            'position_top' => ['required', 'numeric', 'min:0', 'max:100'],
            'position_left' => ['required', 'numeric', 'min:0', 'max:100'],
            'size' => ['required', Rule::in(['sm', 'md', 'lg'])],
            'color_hex' => ['nullable', 'string', 'max:7'],
            'icon' => ['nullable', 'string', 'max:100'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'pages' => ['nullable', 'array'],
            'pages.*' => ['string', Rule::in(array_keys(HomepageBubble::SELECTABLE_PAGES))],
        ]);

        $data['display_order'] = (int) ($data['display_order'] ?? 0);
        $data['pages'] = array_values($data['pages'] ?? []);

        return $data;
    }

    // ── IMAGES DE LA BULLE (plusieurs images par bulle) ──────────────────────

    public function storeImage(Request $request, HomepageBubble $bubble): RedirectResponse
    {
        $this->sanitizeEmptyUploads($request, ['image_file']);

        if (! $this->hasUsableUploadedFile($request, 'image_file')) {
            return back()->withErrors(['image_file' => 'Veuillez sélectionner une image valide.']);
        }

        $request->validate([
            'display_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $this->assertValidImage($request->file('image_file'), 'image_file', 5120);

        $nextOrder = $request->filled('display_order')
            ? (int) $request->input('display_order')
            : (int) ($bubble->images()->max('display_order') + 1);

        HomepageBubbleImage::create([
            'homepage_bubble_id' => $bubble->id,
            'image_url' => $this->storeUploadedFile($request->file('image_file'), 'homepage-bubbles', 'bubble'),
            'display_order' => $nextOrder,
        ]);

        return back()->with('success', 'Image ajoutée à la bulle.');
    }

    public function destroyImage(HomepageBubbleImage $image): RedirectResponse
    {
        $this->deleteStoredFile($image->image_url);
        $image->delete();

        return back()->with('success', 'Image supprimée.');
    }

    // ── HELPERS UPLOAD (mêmes garde-fous que PrestationManagementController) ─

    private function sanitizeEmptyUploads(Request $request, array $fields): void
    {
        foreach ($fields as $field) {
            $file = $request->file($field);
            if (! $file instanceof UploadedFile) {
                continue;
            }
            $pathname = '';
            try {
                $pathname = (string) $file->getPathname();
            } catch (\ValueError) {
                $pathname = '';
            }
            if ($file->getError() === \UPLOAD_ERR_NO_FILE || $pathname === '') {
                $request->files->remove($field);
            }
        }
    }

    private function hasUsableUploadedFile(Request $request, string $field): bool
    {
        $file = $request->file($field);

        return $file instanceof UploadedFile && $this->isUsableUploadedFile($file);
    }

    private function isUsableUploadedFile(UploadedFile $file): bool
    {
        try {
            return $file->isValid() && $file->getError() === \UPLOAD_ERR_OK;
        } catch (\ValueError) {
            return false;
        }
    }

    private function assertValidImage(UploadedFile $file, string $field, int $maxKb): void
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        if (! in_array($extension, ['jpeg', 'jpg', 'png', 'webp'], true)) {
            abort(422, "Format image non autorisé pour {$field} (jpeg, jpg, png, webp).");
        }
        $size = (int) ($file->getSize() ?? 0);
        if ($size <= 0 || $size > ($maxKb * 1024)) {
            abort(422, "Le fichier {$field} dépasse la taille autorisée.");
        }
    }

    /**
     * Stockage via fopen() plutôt que UploadedFile::store() : sous Windows, store()
     * appelle realpath() en interne, qui peut échouer (ValueError) quand les noms
     * courts 8.3 sont désactivés sur le volume.
     */
    private function storeUploadedFile(UploadedFile $file, string $folder, string $prefix): string
    {
        $pathname = $file->getPathname();
        $handle = (is_string($pathname) && $pathname !== '') ? @fopen($pathname, 'r') : false;

        if (! is_resource($handle)) {
            abort(422, 'Le fichier téléversé est invalide. Veuillez le sélectionner à nouveau.');
        }

        $extension = strtolower($file->getClientOriginalExtension()) ?: 'bin';
        $relativePath = $folder.'/'.$prefix.'_'.Str::random(40).'.'.$extension;

        try {
            $stored = Storage::disk('public')->put($relativePath, $handle);
        } finally {
            is_resource($handle) && fclose($handle);
        }

        if (! $stored) {
            abort(422, 'Le fichier téléversé est invalide. Veuillez le sélectionner à nouveau.');
        }

        return '/storage/'.$relativePath;
    }

    private function deleteStoredFile(?string $url): void
    {
        if (! $url || ! str_starts_with($url, '/storage/')) {
            return;
        }
        $relative = ltrim(substr($url, strlen('/storage/')), '/');
        if ($relative !== '') {
            Storage::disk('public')->delete($relative);
        }
    }
}
