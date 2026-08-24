<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrestationBanner;
use App\Models\PrestationItem;
use App\Models\PrestationSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PrestationManagementController extends Controller
{
    public function index(): View
    {
        $settings = PrestationSetting::singleton();
        $banners = PrestationBanner::query()->ordered()->paginate(20, ['*'], 'banners_page');
        $items = PrestationItem::query()->ordered()->paginate(20, ['*'], 'items_page');

        return view('admin.prestations.index', compact('settings', 'banners', 'items'));
    }

    // ── BANNIÈRES (liste répétable) ─────────────────────────────────────────

    public function storeBanner(Request $request): RedirectResponse
    {
        $this->sanitizeEmptyUploads($request, ['image_file']);

        $data = $this->validateBanner($request);

        if ($this->hasUsableUploadedFile($request, 'image_file')) {
            $this->assertValidImage($request->file('image_file'), 'image_file', 8192);
            $data['image_url'] = $this->storeUploadedFile($request->file('image_file'), 'prestations/banner', 'banner');
        }

        $data['is_active'] = $request->boolean('is_active', true);

        PrestationBanner::create($data);

        return back()->with('success', 'Bannière ajoutée avec succès.');
    }

    public function updateBanner(Request $request, PrestationBanner $banner): RedirectResponse
    {
        $this->sanitizeEmptyUploads($request, ['image_file']);

        $data = $this->validateBanner($request);

        if ($this->hasUsableUploadedFile($request, 'image_file')) {
            $this->assertValidImage($request->file('image_file'), 'image_file', 8192);
            $this->deleteStoredFile($banner->image_url);
            $data['image_url'] = $this->storeUploadedFile($request->file('image_file'), 'prestations/banner', 'banner');
        }

        $data['is_active'] = $request->boolean('is_active');

        $banner->update($data);

        return back()->with('success', 'Bannière modifiée avec succès.');
    }

    public function toggleBanner(PrestationBanner $banner): RedirectResponse
    {
        $banner->update(['is_active' => ! $banner->is_active]);

        return back()->with('success', $banner->is_active ? 'Bannière activée.' : 'Bannière désactivée.');
    }

    public function destroyBanner(PrestationBanner $banner): RedirectResponse
    {
        $this->deleteStoredFile($banner->image_url);
        $banner->delete();

        return back()->with('success', 'Bannière supprimée.');
    }

    private function validateBanner(Request $request): array
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'link_url' => ['nullable', 'url', 'max:500'],
            'link_label' => ['nullable', 'string', 'max:150'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $data['display_order'] = (int) ($data['display_order'] ?? 0);

        return $data;
    }

    // ── PARAMÈTRES (vidéo, catalogue) ────────────────────────────────────────

    public function updateSettings(Request $request): RedirectResponse
    {
        $this->sanitizeEmptyUploads($request, ['video_file', 'video_poster_file', 'catalog_file']);

        $validated = $request->validate([
            'video_source' => ['required', Rule::in(['upload', 'embed'])],
            'video_embed_url' => ['nullable', 'url', 'max:500'],
            'catalog_title' => ['nullable', 'string', 'max:255'],
            'catalog_enabled' => ['nullable', 'boolean'],
        ]);

        $settings = PrestationSetting::singleton();

        $data = [
            'video_source' => $validated['video_source'],
            'video_embed_url' => $validated['video_embed_url'] ?? null,
            'catalog_title' => $validated['catalog_title'] ?? null,
            'catalog_enabled' => $request->boolean('catalog_enabled'),
        ];

        if ($this->hasUsableUploadedFile($request, 'video_file')) {
            $this->assertValidVideo($request->file('video_file'), 'video_file', 102400);
            $this->deleteStoredFile($settings->video_url);
            $data['video_url'] = $this->storeUploadedFile($request->file('video_file'), 'prestations/video', 'video');
        }

        if ($this->hasUsableUploadedFile($request, 'video_poster_file')) {
            $this->assertValidImage($request->file('video_poster_file'), 'video_poster_file', 4096);
            $this->deleteStoredFile($settings->video_poster_url);
            $data['video_poster_url'] = $this->storeUploadedFile($request->file('video_poster_file'), 'prestations/video', 'poster');
        }

        if ($this->hasUsableUploadedFile($request, 'catalog_file')) {
            $this->assertValidCatalog($request->file('catalog_file'));
            $this->deleteStoredFile($settings->catalog_file_url);
            $data['catalog_file_url'] = $this->storeUploadedFile($request->file('catalog_file'), 'prestations/catalog', 'catalog');
        }

        $settings->update($data);

        return back()->with('success', 'Paramètres de la page « Nos Prestations » enregistrés.');
    }

    // ── PRESTATIONS (liste répétable) ───────────────────────────────────────

    public function storeItem(Request $request): RedirectResponse
    {
        $this->sanitizeEmptyUploads($request, ['image_file']);

        $data = $this->validateItem($request);

        if ($this->hasUsableUploadedFile($request, 'image_file')) {
            $this->assertValidImage($request->file('image_file'), 'image_file', 5120);
            $data['image_url'] = $this->storeUploadedFile($request->file('image_file'), 'prestations/items', 'item');
        }

        $data['is_active'] = $request->boolean('is_active', true);

        PrestationItem::create($data);

        return back()->with('success', 'Prestation ajoutée avec succès.');
    }

    public function updateItem(Request $request, PrestationItem $item): RedirectResponse
    {
        $this->sanitizeEmptyUploads($request, ['image_file']);

        $data = $this->validateItem($request);

        if ($this->hasUsableUploadedFile($request, 'image_file')) {
            $this->assertValidImage($request->file('image_file'), 'image_file', 5120);
            $this->deleteStoredFile($item->image_url);
            $data['image_url'] = $this->storeUploadedFile($request->file('image_file'), 'prestations/items', 'item');
        }

        $data['is_active'] = $request->boolean('is_active');

        $item->update($data);

        return back()->with('success', 'Prestation modifiée avec succès.');
    }

    public function toggleItem(PrestationItem $item): RedirectResponse
    {
        $item->update(['is_active' => ! $item->is_active]);

        return back()->with('success', $item->is_active ? 'Prestation activée.' : 'Prestation désactivée.');
    }

    public function destroyItem(PrestationItem $item): RedirectResponse
    {
        $this->deleteStoredFile($item->image_url);
        $item->delete();

        return back()->with('success', 'Prestation supprimée.');
    }

    private function validateItem(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'link_url' => ['nullable', 'url', 'max:500'],
            'link_label' => ['nullable', 'string', 'max:150'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $data['display_order'] = (int) ($data['display_order'] ?? 0);

        return $data;
    }

    // ── HELPERS UPLOAD (mêmes garde-fous que AdministrationController) ──────

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

    private function assertValidVideo(UploadedFile $file, string $field, int $maxKb): void
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        if (! in_array($extension, ['mp4', 'webm'], true)) {
            abort(422, "Format vidéo non autorisé pour {$field} (mp4, webm).");
        }
        $size = (int) ($file->getSize() ?? 0);
        if ($size <= 0 || $size > ($maxKb * 1024)) {
            abort(422, "Le fichier {$field} dépasse la taille autorisée.");
        }
    }

    private function assertValidCatalog(UploadedFile $file): void
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        if ($extension !== 'pdf') {
            abort(422, 'Le catalogue doit être un fichier PDF.');
        }
        $size = (int) ($file->getSize() ?? 0);
        if ($size <= 0 || $size > (51200 * 1024)) {
            abort(422, 'Le fichier catalogue dépasse la taille autorisée (50 Mo max).');
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
