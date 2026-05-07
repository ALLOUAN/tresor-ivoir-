<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppearanceSlide;
use App\Models\Article;
use App\Models\SiteContactSetting;
use App\Models\SiteMediaItem;
use App\Models\SiteSetting;
use App\Models\SiteSocialSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdministrationController extends Controller
{
    public function maintenance(): View
    {
        $settings = SiteSetting::singleton();

        return view('admin.system.maintenance', compact('settings'));
    }

    public function maintenancePreview(): View
    {
        $settings = SiteSetting::singleton();
        $message = trim((string) ($settings->maintenance_message ?? ''));

        return view('errors.maintenance-site', [
            'maintenanceMessage' => $message !== ''
                ? $message
                : 'Nous effectuons une mise à jour. Merci de revenir un peu plus tard.',
        ]);
    }

    public function updateMaintenance(Request $request): RedirectResponse
    {
        $settings = SiteSetting::singleton();

        $request->merge([
            'maintenance_progress' => $request->input('maintenance_progress') === '' || $request->input('maintenance_progress') === null
                ? null
                : $request->input('maintenance_progress'),
            'maintenance_eta' => (($eta = trim((string) $request->input('maintenance_eta', ''))) !== '') ? $eta : null,
        ]);

        $validated = $request->validate([
            'maintenance_mode' => ['nullable', 'boolean'],
            'maintenance_message' => ['nullable', 'string', 'max:2000'],
            'maintenance_allowed_ips' => ['nullable', 'string', 'max:2000'],
            'maintenance_progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'maintenance_eta' => ['nullable', 'string', 'max:120'],
        ]);

        $settings->maintenance_mode = $request->boolean('maintenance_mode');
        $settings->maintenance_message = $validated['maintenance_message'] ?? null;
        $settings->maintenance_allowed_ips = $validated['maintenance_allowed_ips'] ?? null;
        $settings->maintenance_progress = $validated['maintenance_progress'] ?? null;
        $settings->maintenance_eta = $validated['maintenance_eta'] ?? null;
        $settings->save();

        SiteSetting::forgetBrandCache();

        return back()->with('success', 'Paramètres de maintenance enregistrés.');
    }

    public function toggleMaintenance(): RedirectResponse
    {
        $settings = SiteSetting::singleton();
        $settings->maintenance_mode = ! $settings->maintenance_mode;
        $settings->save();

        SiteSetting::forgetBrandCache();

        return back()->with(
            'success',
            $settings->maintenance_mode
                ? 'Mode maintenance activé.'
                : 'Mode maintenance désactivé.'
        );
    }

    public function appearance(): View
    {
        $slides = AppearanceSlide::query()
            ->orderBy('display_order')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.system.appearance', compact('slides'));
    }

    public function storeSlide(Request $request): RedirectResponse
    {
        $this->sanitizeEmptySlideUploads($request);

        $isVideo = $request->input('media_type') === 'video';

        // Avoid framework ValueError on empty/invalid UploadedFile objects.
        // We check required media upfront and only run file validators when present.
        if (! $isVideo && ! $this->hasUsableUploadedFile($request, 'desktop_image')) {
            return back()
                ->withErrors(['desktop_image' => 'L’image desktop est obligatoire pour un slide image.'])
                ->withInput();
        }

        if ($isVideo && ! $this->hasUsableUploadedFile($request, 'video_desktop')) {
            return back()
                ->withErrors(['video_desktop' => 'La vidéo desktop est obligatoire pour un slide vidéo.'])
                ->withInput();
        }

        $request->validate([
            'media_type'     => ['required', Rule::in(['image', 'video'])],
            'title'          => 'required|string|max:255',
            'subtitle'       => 'nullable|string|max:255',
            'description'    => 'nullable|string',
            'is_active'      => 'nullable|boolean',
            'display_order'  => 'required|integer|min:1|max:9999',
            // File fields are validated separately below (only when present)
            // to avoid framework ValueError on empty temporary upload paths.
            'desktop_image'  => ['nullable'],
            'tablet_image'   => ['nullable'],
            'mobile_image'   => ['nullable'],
            'video_desktop'  => ['nullable'],
            'video_tablet'   => ['nullable'],
            'video_mobile'   => ['nullable'],
        ]);

        $this->validateSlideFiles($request);

        $data = [
            'media_type'   => $isVideo ? 'video' : 'image',
            'title'        => $request->input('title'),
            'subtitle'     => $request->input('subtitle'),
            'description'  => $request->input('description'),
            'is_active'    => $request->boolean('is_active'),
            'display_order' => (int) $request->input('display_order'),
        ];

        if ($isVideo) {
            $data['video_desktop_url'] = $this->storeSlideVideoFile($request->file('video_desktop'), 'video_desktop');
            $data['video_tablet_url']  = $this->hasUsableUploadedFile($request, 'video_tablet')
                ? $this->storeSlideVideoFile($request->file('video_tablet'), 'video_tablet')
                : null;
            $data['video_mobile_url']  = $this->hasUsableUploadedFile($request, 'video_mobile')
                ? $this->storeSlideVideoFile($request->file('video_mobile'), 'video_mobile')
                : null;
        } else {
            $data['desktop_image_url'] = $this->storeSlideImageFile($request->file('desktop_image'), 'desktop_image');
            $data['tablet_image_url']  = $this->hasUsableUploadedFile($request, 'tablet_image')
                ? $this->storeSlideImageFile($request->file('tablet_image'), 'tablet_image')
                : null;
            $data['mobile_image_url']  = $this->hasUsableUploadedFile($request, 'mobile_image')
                ? $this->storeSlideImageFile($request->file('mobile_image'), 'mobile_image')
                : null;
        }

        AppearanceSlide::create($data);

        return back()->with('success', 'Slide ajouté avec succès.');
    }

    public function updateSlide(Request $request, AppearanceSlide $slide): RedirectResponse
    {
        $this->sanitizeEmptySlideUploads($request);

        $isVideo = $request->input('media_type', $slide->media_type) === 'video';

        $request->validate([
            'media_type'    => ['required', Rule::in(['image', 'video'])],
            'title'         => 'required|string|max:255',
            'subtitle'      => 'nullable|string|max:255',
            'description'   => 'nullable|string',
            'is_active'     => 'nullable|boolean',
            'display_order' => 'required|integer|min:1|max:9999',
            // File fields are validated separately below (only when present).
            'desktop_image' => ['nullable'],
            'tablet_image'  => ['nullable'],
            'mobile_image'  => ['nullable'],
            'video_desktop' => ['nullable'],
            'video_tablet'  => ['nullable'],
            'video_mobile'  => ['nullable'],
        ]);

        $this->validateSlideFiles($request);

        $data = [
            'media_type'    => $isVideo ? 'video' : 'image',
            'title'         => $request->input('title'),
            'subtitle'      => $request->input('subtitle'),
            'description'   => $request->input('description'),
            'is_active'     => $request->boolean('is_active'),
            'display_order' => (int) $request->input('display_order'),
        ];

        // Images
        $data['desktop_image_url'] = $slide->desktop_image_url;
        if ($this->hasUsableUploadedFile($request, 'desktop_image')) {
            $this->deleteStoredPublicFile($slide->desktop_image_url);
            $data['desktop_image_url'] = $this->storeSlideImageFile($request->file('desktop_image'), 'desktop_image');
        }
        $data['tablet_image_url'] = $slide->tablet_image_url;
        if ($this->hasUsableUploadedFile($request, 'tablet_image')) {
            $this->deleteStoredPublicFile($slide->tablet_image_url);
            $data['tablet_image_url'] = $this->storeSlideImageFile($request->file('tablet_image'), 'tablet_image');
        }
        $data['mobile_image_url'] = $slide->mobile_image_url;
        if ($this->hasUsableUploadedFile($request, 'mobile_image')) {
            $this->deleteStoredPublicFile($slide->mobile_image_url);
            $data['mobile_image_url'] = $this->storeSlideImageFile($request->file('mobile_image'), 'mobile_image');
        }

        // Vidéos
        $data['video_desktop_url'] = $slide->video_desktop_url;
        if ($this->hasUsableUploadedFile($request, 'video_desktop')) {
            $this->deleteStoredPublicFile($slide->video_desktop_url);
            $data['video_desktop_url'] = $this->storeSlideVideoFile($request->file('video_desktop'), 'video_desktop');
        }
        $data['video_tablet_url'] = $slide->video_tablet_url;
        if ($this->hasUsableUploadedFile($request, 'video_tablet')) {
            $this->deleteStoredPublicFile($slide->video_tablet_url);
            $data['video_tablet_url'] = $this->storeSlideVideoFile($request->file('video_tablet'), 'video_tablet');
        }
        $data['video_mobile_url'] = $slide->video_mobile_url;
        if ($this->hasUsableUploadedFile($request, 'video_mobile')) {
            $this->deleteStoredPublicFile($slide->video_mobile_url);
            $data['video_mobile_url'] = $this->storeSlideVideoFile($request->file('video_mobile'), 'video_mobile');
        }

        $slide->update($data);

        return back()->with('success', 'Slide modifié avec succès.');
    }

    public function toggleSlide(AppearanceSlide $slide): RedirectResponse
    {
        $slide->update(['is_active' => ! $slide->is_active]);

        return back()->with('success', 'Statut du slide mis à jour.');
    }

    public function destroySlide(AppearanceSlide $slide): RedirectResponse
    {
        $this->deleteStoredPublicFile($slide->desktop_image_url);
        $this->deleteStoredPublicFile($slide->tablet_image_url);
        $this->deleteStoredPublicFile($slide->mobile_image_url);
        $this->deleteStoredPublicFile($slide->video_desktop_url);
        $this->deleteStoredPublicFile($slide->video_tablet_url);
        $this->deleteStoredPublicFile($slide->video_mobile_url);

        $slide->delete();

        return back()->with('success', 'Slide supprimé avec succès.');
    }

    private function storeSlideImageFile(UploadedFile $file, string $field): string
    {
        $this->assertUploadedFileIsUsable($field, $file);

        // On Windows, UploadedFile::store() calls getRealPath() (via realpath()) which can
        // return false when the PHP temp dir uses 8.3 short names that are disabled on the
        // volume, causing fopen("", 'r') → ValueError. We bypass this by opening the file
        // directly via getPathname(), which always holds the raw path PHP used for the upload.
        $pathname = $file->getPathname();
        $handle = (is_string($pathname) && $pathname !== '') ? @fopen($pathname, 'r') : false;

        if (! is_resource($handle)) {
            throw ValidationException::withMessages([
                $field => 'Le fichier image téléversé est invalide. Veuillez le sélectionner à nouveau.',
            ]);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $relativePath = 'appearance/slides/' . Str::random(40) . '.' . $extension;

        try {
            $stored = Storage::disk('public')->put($relativePath, $handle);
        } finally {
            is_resource($handle) && fclose($handle);
        }

        if (! $stored) {
            throw ValidationException::withMessages([
                $field => 'Le fichier image téléversé est invalide. Veuillez le sélectionner à nouveau.',
            ]);
        }

        return '/storage/' . $relativePath;
    }

    private function storeSlideVideoFile(UploadedFile $file, string $field): string
    {
        $this->assertUploadedFileIsUsable($field, $file);

        // Same Windows/short-name workaround as storeSlideImageFile above.
        $pathname = $file->getPathname();
        $handle = (is_string($pathname) && $pathname !== '') ? @fopen($pathname, 'r') : false;

        if (! is_resource($handle)) {
            throw ValidationException::withMessages([
                $field => 'Le fichier vidéo téléversé est invalide. Veuillez le sélectionner à nouveau.',
            ]);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $relativePath = 'appearance/slides/videos/' . Str::random(40) . '.' . $extension;

        try {
            $stored = Storage::disk('public')->put($relativePath, $handle);
        } finally {
            is_resource($handle) && fclose($handle);
        }

        if (! $stored) {
            throw ValidationException::withMessages([
                $field => 'Le fichier vidéo téléversé est invalide. Veuillez le sélectionner à nouveau.',
            ]);
        }

        return '/storage/' . $relativePath;
    }

    private function deleteStoredPublicFile(?string $storedUrl): void
    {
        if (! $storedUrl || ! str_starts_with($storedUrl, '/storage/')) {
            return;
        }

        $relative = ltrim(substr($storedUrl, strlen('/storage/')), '/');
        if ($relative !== '') {
            Storage::disk('public')->delete($relative);
        }
    }

    /**
     * Some browsers/framework edge cases can send an "empty" UploadedFile object
     * (no real temp path). Validation image/file rules then trigger ValueError
     * when reading file path. Remove those entries before validating.
     */
    private function sanitizeEmptySlideUploads(Request $request): void
    {
        foreach ([
            'desktop_image',
            'tablet_image',
            'mobile_image',
            'video_desktop',
            'video_tablet',
            'video_mobile',
        ] as $field) {
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

    private function validateSlideFiles(Request $request): void
    {
        $imageRules = [
            'desktop_image' => ['maxKb' => 8192, 'minW' => 1600, 'minH' => 600, 'maxW' => 4096, 'maxH' => 2000],
            'tablet_image' => ['maxKb' => 6144, 'minW' => 900, 'minH' => 500, 'maxW' => 2560, 'maxH' => 1600],
            'mobile_image' => ['maxKb' => 4096, 'minW' => 640, 'minH' => 400, 'maxW' => 1536, 'maxH' => 1200],
        ];

        foreach ($imageRules as $field => $rules) {
            $file = $request->file($field);

            if (! $file instanceof UploadedFile) {
                continue;
            }

            // Optional file inputs can still appear in request payloads
            // with empty temp paths on some clients. Ignore them here.
            if (! $this->hasUsableUploadedFile($request, $field)) {
                continue;
            }

            $this->assertValidImageUpload($field, $file, $rules);
        }

        foreach (['video_desktop', 'video_tablet', 'video_mobile'] as $field) {
            $file = $request->file($field);

            if (! $file instanceof UploadedFile) {
                continue;
            }

            if (! $this->hasUsableUploadedFile($request, $field)) {
                continue;
            }

            $this->assertValidVideoUpload($field, $file, 102400);
        }
    }

    private function assertValidImageUpload(string $field, UploadedFile $file, array $rules): void
    {
        $this->assertUploadedFileIsUsable($field, $file);

        $extension = strtolower((string) $file->getClientOriginalExtension());
        if (! in_array($extension, ['jpeg', 'jpg', 'png', 'webp'], true)) {
            throw ValidationException::withMessages([$field => 'Format image non autorisé (jpeg, jpg, png, webp).']);
        }

        $size = (int) ($file->getSize() ?? 0);
        if ($size <= 0 || $size > ($rules['maxKb'] * 1024)) {
            throw ValidationException::withMessages([$field => 'Le fichier image dépasse la taille autorisée.']);
        }

        $path = $this->resolveUploadedFilePath($file);
        if ($path === null) {
            return;
        }

        $imageInfo = @getimagesize($path);
        if (! is_array($imageInfo) || ! isset($imageInfo[0], $imageInfo[1])) {
            return;
        }

        [$width, $height] = [$imageInfo[0], $imageInfo[1]];
        if (
            $width < $rules['minW'] || $height < $rules['minH'] ||
            $width > $rules['maxW'] || $height > $rules['maxH']
        ) {
            throw ValidationException::withMessages([
                $field => "Dimensions invalides: minimum {$rules['minW']}x{$rules['minH']} et maximum {$rules['maxW']}x{$rules['maxH']}.",
            ]);
        }
    }

    private function assertValidVideoUpload(string $field, UploadedFile $file, int $maxKb): void
    {
        $this->assertUploadedFileIsUsable($field, $file);

        $extension = strtolower((string) $file->getClientOriginalExtension());
        if (! in_array($extension, ['mp4', 'webm'], true)) {
            throw ValidationException::withMessages([$field => 'Format vidéo non autorisé (mp4, webm).']);
        }

        $size = (int) ($file->getSize() ?? 0);
        if ($size <= 0 || $size > ($maxKb * 1024)) {
            throw ValidationException::withMessages([$field => 'Le fichier vidéo dépasse la taille autorisée.']);
        }
    }

    private function assertUploadedFileIsUsable(string $field, UploadedFile $file): void
    {
        if (! $this->isUsableUploadedFile($file)) {
            throw ValidationException::withMessages([
                $field => 'Le fichier téléversé est invalide ou incomplet. Veuillez le sélectionner à nouveau.',
            ]);
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

    private function resolveUploadedFilePath(UploadedFile $file): ?string
    {
        try {
            $pathname = (string) ($file->getRealPath() ?: $file->getPathname());
        } catch (\ValueError) {
            return null;
        }

        if ($pathname === '' || ! is_file($pathname)) {
            return null;
        }

        return $pathname;
    }

    private function storeSiteBrandFile(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'public');

        return '/storage/'.$path;
    }

    public function contacts(): View
    {
        $contact = SiteContactSetting::singleton();

        return view('admin.system.contacts', compact('contact'));
    }

    public function updateContactSettings(Request $request): RedirectResponse
    {
        $contact = SiteContactSetting::singleton();

        $request->merge($this->emptyStringsToNull($request->only([
            'phone_1', 'phone_2', 'email_primary', 'email_secondary', 'contact_form_email',
            'opening_hours', 'address', 'latitude', 'longitude',
        ])));

        $validated = $request->validate([
            'phone_1' => ['nullable', 'string', 'max:64'],
            'phone_2' => ['nullable', 'string', 'max:64'],
            'email_primary' => ['nullable', 'email', 'max:255'],
            'email_secondary' => ['nullable', 'email', 'max:255'],
            'contact_form_email' => ['nullable', 'email', 'max:255'],
            'opening_hours' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'string', 'max:32', 'regex:/^-?[0-9]+(\.[0-9]+)?$/'],
            'longitude' => ['nullable', 'string', 'max:32', 'regex:/^-?[0-9]+(\.[0-9]+)?$/'],
        ]);

        $contact->fill($validated);
        $contact->save();

        SiteSetting::forgetBrandCache();

        return back()->with('success', 'Informations de contact enregistrées.');
    }

    public function social(): View
    {
        $social = SiteSocialSetting::singleton();

        return view('admin.system.social', compact('social'));
    }

    public function updateSocialSettings(Request $request): RedirectResponse
    {
        $social = SiteSocialSetting::singleton();

        $request->merge($this->emptyStringsToNull($request->only([
            'facebook_url', 'twitter_url', 'linkedin_url', 'instagram_url', 'youtube_url', 'whatsapp_phone',
        ])));

        $validated = $request->validate([
            'facebook_url' => ['nullable', 'string', 'max:500', 'url'],
            'twitter_url' => ['nullable', 'string', 'max:500', 'url'],
            'linkedin_url' => ['nullable', 'string', 'max:500', 'url'],
            'instagram_url' => ['nullable', 'string', 'max:500', 'url'],
            'youtube_url' => ['nullable', 'string', 'max:500', 'url'],
            'whatsapp_phone' => ['nullable', 'string', 'max:64', 'regex:/^\+?[0-9][0-9\s\-]{5,62}$/'],
        ]);

        $social->fill($validated);
        $social->save();

        SiteSetting::forgetBrandCache();

        return back()->with('success', 'Réseaux sociaux enregistrés.');
    }

    public function media(): View
    {
        $items = SiteMediaItem::query()
            ->with('uploader:id,first_name,last_name,email')
            ->orderByDesc('id')
            ->paginate(18);

        return view('admin.system.media', compact('items'));
    }

    public function storeSiteMedia(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['nullable', 'file', 'max:51200'],
            'files' => ['nullable', 'array', 'max:30'],
            'files.*' => ['file', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'title' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:300'],
            'caption' => ['nullable', 'string', 'max:500'],
            'credit' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'section' => ['nullable', 'string', 'max:80'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'published_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $files = [];
        if ($request->hasFile('file')) {
            $files[] = $request->file('file');
        }
        if ($request->hasFile('files')) {
            foreach ((array) $request->file('files') as $multiFile) {
                $files[] = $multiFile;
            }
        }

        if ($files === []) {
            return back()->withErrors(['files' => 'Veuillez sélectionner au moins un fichier.'])->withInput();
        }

        $title = trim((string) $request->input('title', ''));
        $altText = trim((string) $request->input('alt_text', ''));
        $caption = trim((string) $request->input('caption', ''));
        $credit = trim((string) $request->input('credit', ''));
        $priceRaw = $request->input('price');
        $price = $priceRaw === null || $priceRaw === '' ? null : (float) $priceRaw;
        $section = trim((string) $request->input('section', 'home_gallery'));
        $displayOrder = (int) $request->input('display_order', 0);
        $isActive = $request->boolean('is_active', true);
        $isFeatured = $request->boolean('is_featured', false);
        $publishedAt = $request->filled('published_at') ? $request->date('published_at') : now();

        foreach ($files as $idx => $file) {
            $mime = (string) $file->getMimeType();
            $type = $this->resolveSiteMediaType($mime);

            $extension = $file->guessExtension() ?: $file->getClientOriginalExtension();
            $filename  = Str::random(40) . ($extension ? '.' . $extension : '');
            $path      = 'site/media-library/' . $filename;
            Storage::disk('public')->put($path, fopen($file->getPathname(), 'r'));
            $url = '/storage/' . $path;

            SiteMediaItem::query()->create([
                'type' => $type,
                'mime_type' => $mime,
                'original_name' => $file->getClientOriginalName(),
                'title' => $title !== '' ? $title : null,
                'alt_text' => $altText !== '' ? $altText : null,
                'caption' => $caption !== '' ? $caption : null,
                'credit' => $credit !== '' ? $credit : null,
                'price' => $price,
                'section' => $section !== '' ? $section : 'home_gallery',
                'is_active' => $isActive,
                'is_featured' => $isFeatured,
                'display_order' => $displayOrder + $idx,
                'published_at' => $publishedAt,
                'file_path' => $path,
                'url' => $url,
                'size_bytes' => $file->getSize(),
                'uploaded_by' => (int) Auth::id(),
            ]);
        }

        $count = count($files);

        return back()->with('success', $count > 1 ? "{$count} médias téléversés." : 'Média téléversé.');
    }

    public function destroySiteMedia(SiteMediaItem $siteMediaItem): RedirectResponse
    {
        $this->deleteStoredPublicFile($siteMediaItem->url);
        $siteMediaItem->delete();

        return back()->with('success', 'Média supprimé.');
    }

    private function resolveSiteMediaType(string $mime): string
    {
        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }
        if (str_starts_with($mime, 'video/')) {
            return 'video';
        }

        return 'document';
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function emptyStringsToNull(array $input): array
    {
        foreach ($input as $key => $value) {
            if ($value === '') {
                $input[$key] = null;
            }
        }

        return $input;
    }

    public function settings(): View
    {
        $settings = SiteSetting::singleton();

        return view('admin.system.general-settings', compact('settings'));
    }

    public function homepage(): View
    {
        $settings = SiteSetting::singleton();
        $publishedArticles = Article::query()
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->limit(100)
            ->get(['id', 'title_fr', 'published_at']);

        $hasHomeDestinationColumn = Schema::hasTable('site_settings')
            && Schema::hasColumn('site_settings', 'home_destination_article_id');

        return view('admin.system.homepage-settings', compact('settings', 'publishedArticles', 'hasHomeDestinationColumn'));
    }

    public function updateHomepage(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('site_settings') || ! Schema::hasColumn('site_settings', 'home_destination_article_id')) {
            return back()->with('error', "Configuration indisponible: lancez la migration pour activer ce réglage d'accueil.");
        }

        $settings = SiteSetting::singleton();

        $validated = $request->validate([
            'home_destination_article_id' => ['nullable', 'integer'],
        ]);

        $selectedHomeValue = $validated['home_destination_article_id'] ?? null;
        if ($selectedHomeValue !== null && (int) $selectedHomeValue !== 0) {
            $exists = Article::query()->whereKey((int) $selectedHomeValue)->exists();
            if (! $exists) {
                return back()
                    ->withErrors(['home_destination_article_id' => 'L’article sélectionné est introuvable.'])
                    ->withInput();
            }
        }

        $settings->home_destination_article_id = $selectedHomeValue !== null
            ? (int) $selectedHomeValue
            : null;
        $settings->save();

        return back()->with('success', "Paramètres d'accueil enregistrés.");
    }

    public function updateSiteSettings(Request $request): RedirectResponse
    {
        $settings = SiteSetting::singleton();

        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'site_slogan' => ['nullable', 'string', 'max:255'],
            'site_description' => ['nullable', 'string'],
            'site_logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:ico,jpg,jpeg,png,webp', 'max:512'],
            'primary_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'secondary_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'timezone' => ['required', 'timezone:all'],
            'default_language' => ['required', 'string', Rule::in(['fr', 'en'])],
        ]);

        $settings->fill([
            'site_name' => $validated['site_name'],
            'site_slogan' => $validated['site_slogan'] ?? null,
            'site_description' => $validated['site_description'] ?? null,
            'primary_color' => $validated['primary_color'],
            'secondary_color' => $validated['secondary_color'],
            'timezone' => $validated['timezone'],
            'default_language' => $validated['default_language'],
            'maintenance_mode' => $request->has('maintenance_mode'),
        ]);

        if ($request->hasFile('site_logo')) {
            $this->deleteStoredPublicFile($settings->logo_url);
            $settings->logo_url = $this->storeSiteBrandFile($request->file('site_logo'), 'site/brand');
        }

        if ($request->hasFile('favicon')) {
            $this->deleteStoredPublicFile($settings->favicon_url);
            $settings->favicon_url = $this->storeSiteBrandFile($request->file('favicon'), 'site/brand');
        }

        $settings->save();

        SiteSetting::forgetBrandCache();

        return back()->with('success', 'Paramètres enregistrés.');
    }
}
