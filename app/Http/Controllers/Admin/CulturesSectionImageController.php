<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CulturesSectionImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CulturesSectionImageController extends Controller
{
    public function edit(): View
    {
        $culturesImage = CulturesSectionImage::singleton();

        return view('admin.system.cultures-image', compact('culturesImage'));
    }

    public function update(Request $request): RedirectResponse
    {
        $this->sanitizeEmptyUploads($request, ['image_file']);

        $culturesImage = CulturesSectionImage::singleton();

        if ($this->hasUsableUploadedFile($request, 'image_file')) {
            $this->assertValidImage($request->file('image_file'), 'image_file', 5120);
            $this->deleteStoredFile($culturesImage->image_url);
            $culturesImage->image_url = $this->storeUploadedFile($request->file('image_file'), 'cultures-section', 'cultures');
        }

        $culturesImage->is_active = $request->boolean('is_active', true);
        $culturesImage->save();

        return back()->with('success', 'Image de fond enregistrée.');
    }

    public function toggle(): RedirectResponse
    {
        $culturesImage = CulturesSectionImage::singleton();
        $culturesImage->update(['is_active' => ! $culturesImage->is_active]);

        return back()->with('success', $culturesImage->is_active ? 'Affichage activé.' : 'Affichage désactivé.');
    }

    public function destroy(): RedirectResponse
    {
        $culturesImage = CulturesSectionImage::singleton();
        $this->deleteStoredFile($culturesImage->image_url);
        $culturesImage->update(['image_url' => null]);

        return back()->with('success', 'Image de fond supprimée.');
    }

    // ── HELPERS UPLOAD (mêmes garde-fous que FooterImageController) ──────────

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
