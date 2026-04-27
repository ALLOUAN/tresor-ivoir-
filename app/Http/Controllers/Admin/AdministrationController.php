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
use Illuminate\Validation\Rule;
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
        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'desktop_image' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:8192',
                Rule::dimensions()->minWidth(1600)->minHeight(600)->maxWidth(4096)->maxHeight(2000),
            ],
            'tablet_image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:6144',
                Rule::dimensions()->minWidth(900)->minHeight(500)->maxWidth(2560)->maxHeight(1600),
            ],
            'mobile_image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:4096',
                Rule::dimensions()->minWidth(640)->minHeight(400)->maxWidth(1536)->maxHeight(1200),
            ],
            'is_active' => 'nullable|boolean',
            'display_order' => 'required|integer|min:1|max:9999',
        ]);

        AppearanceSlide::create([
            'title' => $request->input('title'),
            'subtitle' => $request->input('subtitle'),
            'description' => $request->input('description'),
            'desktop_image_url' => $this->storeSlideImageFile($request->file('desktop_image')),
            'tablet_image_url' => $request->hasFile('tablet_image')
                ? $this->storeSlideImageFile($request->file('tablet_image'))
                : null,
            'mobile_image_url' => $request->hasFile('mobile_image')
                ? $this->storeSlideImageFile($request->file('mobile_image'))
                : null,
            'is_active' => $request->boolean('is_active'),
            'display_order' => (int) $request->input('display_order'),
        ]);

        return back()->with('success', 'Slide ajouté avec succès.');
    }

    public function updateSlide(Request $request, AppearanceSlide $slide): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'desktop_image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:8192',
                Rule::dimensions()->minWidth(1600)->minHeight(600)->maxWidth(4096)->maxHeight(2000),
            ],
            'tablet_image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:6144',
                Rule::dimensions()->minWidth(900)->minHeight(500)->maxWidth(2560)->maxHeight(1600),
            ],
            'mobile_image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:4096',
                Rule::dimensions()->minWidth(640)->minHeight(400)->maxWidth(1536)->maxHeight(1200),
            ],
            'is_active' => 'nullable|boolean',
            'display_order' => 'required|integer|min:1|max:9999',
        ]);

        $desktopUrl = $slide->desktop_image_url;
        if ($request->hasFile('desktop_image')) {
            $this->deleteStoredPublicFile($slide->desktop_image_url);
            $desktopUrl = $this->storeSlideImageFile($request->file('desktop_image'));
        }

        $tabletUrl = $slide->tablet_image_url;
        if ($request->hasFile('tablet_image')) {
            $this->deleteStoredPublicFile($slide->tablet_image_url);
            $tabletUrl = $this->storeSlideImageFile($request->file('tablet_image'));
        }

        $mobileUrl = $slide->mobile_image_url;
        if ($request->hasFile('mobile_image')) {
            $this->deleteStoredPublicFile($slide->mobile_image_url);
            $mobileUrl = $this->storeSlideImageFile($request->file('mobile_image'));
        }

        $slide->update([
            'title' => $request->input('title'),
            'subtitle' => $request->input('subtitle'),
            'description' => $request->input('description'),
            'desktop_image_url' => $desktopUrl,
            'tablet_image_url' => $tabletUrl,
            'mobile_image_url' => $mobileUrl,
            'is_active' => $request->boolean('is_active'),
            'display_order' => (int) $request->input('display_order'),
        ]);

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

        $slide->delete();

        return back()->with('success', 'Slide supprimé avec succès.');
    }

    private function storeSlideImageFile(UploadedFile $file): string
    {
        $path = $file->store('appearance/slides', 'public');

        return '/storage/'.$path;
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

            $path = $file->store('site/media-library', 'public');
            $url = '/storage/'.$path;

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
