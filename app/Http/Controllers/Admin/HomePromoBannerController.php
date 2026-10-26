<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomePromoBanner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Bannières publicitaires de la page d'accueil : création, modification, activation, suppression. */
class HomePromoBannerController extends Controller
{
    public function index(): View
    {
        return view('admin.home-banners.index', [
            'banners' => HomePromoBanner::orderBy('placement')->orderBy('display_order')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.home-banners.form', ['banner' => new HomePromoBanner(['placement' => 'after_annuaire', 'cta_label' => 'Découvrir', 'is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $banner = new HomePromoBanner;
        $this->fill($banner, $request);

        return redirect()->route('admin.home-banners.index')->with('success', 'Bannière enregistrée.');
    }

    public function edit(HomePromoBanner $banner): View
    {
        return view('admin.home-banners.form', ['banner' => $banner]);
    }

    public function update(Request $request, HomePromoBanner $banner): RedirectResponse
    {
        $this->fill($banner, $request);

        return redirect()->route('admin.home-banners.index')->with('success', 'Bannière enregistrée.');
    }

    public function toggle(HomePromoBanner $banner): RedirectResponse
    {
        $banner->update(['is_active' => ! $banner->is_active]);

        return back()->with('success', $banner->is_active ? 'Bannière affichée sur l\'accueil.' : 'Bannière masquée.');
    }

    public function destroy(HomePromoBanner $banner): RedirectResponse
    {
        foreach ($banner->imageList() as $url) {
            $this->deleteStoredFile($url);
        }
        $banner->delete();

        return back()->with('success', 'Bannière supprimée.');
    }

    private function fill(HomePromoBanner $banner, Request $request): void
    {
        $data = $request->validate([
            'placement' => ['required', Rule::in(array_keys(HomePromoBanner::PLACEMENTS))],
            'kicker' => ['nullable', 'string', 'max:80'],
            'title_strong' => ['required', 'string', 'max:90'],
            'title_rest' => ['nullable', 'string', 'max:120'],
            'accent' => ['nullable', 'string', 'max:120'],
            'cta_label' => ['required', 'string', 'max:40'],
            'link_url' => ['required', 'string', 'max:500', 'regex:#^(https?://|/)#'],
            'icon' => ['nullable', 'string', 'max:60', 'regex:/^fa-[a-z0-9-]+$/'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'images_new' => ['nullable', 'array', 'max:'.HomePromoBanner::MAX_IMAGES],
            'images_new.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'images_remove' => ['nullable', 'array'],
            'images_remove.*' => ['string', 'max:255'],
        ], [
            'link_url.regex' => 'Le lien doit commencer par https:// ou par / (page du site).',
            'icon.regex' => 'Icône Font Awesome attendue, par exemple fa-store.',
        ], ['title_strong' => 'titre', 'cta_label' => 'texte du bouton', 'link_url' => 'lien', 'placement' => 'emplacement']);

        $banner->fill(collect($data)->except(['images_new', 'images_remove'])->all() + [
            'display_order' => (int) ($data['display_order'] ?? 0),
            'open_new_tab' => $request->boolean('open_new_tab'),
            'is_active' => $request->boolean('is_active'),
        ]);

        // Visuels qui défilent : on retire les visuels cochés, puis on ajoute les nouveaux (8 au plus).
        $remove = (array) ($data['images_remove'] ?? []);
        $list = [];
        foreach ($banner->imageList() as $url) {
            if (in_array($url, $remove, true)) {
                $this->deleteStoredFile($url);
            } else {
                $list[] = $url;
            }
        }
        foreach ((array) $request->file('images_new', []) as $file) {
            if (count($list) >= HomePromoBanner::MAX_IMAGES) {
                break;
            }
            if ($file instanceof UploadedFile && $file->isValid()) {
                \App\Support\ImageUploadGuard::assert($file, 'images_new');
                $list[] = $this->storeUploadedFile($file);
            }
        }
        $banner->images = $list;
        $banner->image_url = $list[0] ?? null; // compatibilité : premier visuel
        $banner->save();
    }

    /** Même méthode que les autres modules d'accueil : fopen() plutôt que store() (realpath sous Windows). */
    private function storeUploadedFile(UploadedFile $file): string
    {
        $handle = @fopen($file->getPathname(), 'r');
        abort_unless(is_resource($handle), 422, 'Le fichier téléversé est invalide. Veuillez le sélectionner à nouveau.');
        $relative = 'home-banners/banner_'.Str::random(40).'.'.(strtolower($file->getClientOriginalExtension()) ?: 'png');
        try {
            $ok = Storage::disk('public')->put($relative, $handle);
        } finally {
            is_resource($handle) && fclose($handle);
        }
        abort_unless($ok, 422, 'Le fichier téléversé est invalide. Veuillez le sélectionner à nouveau.');

        return '/storage/'.$relative;
    }

    private function deleteStoredFile(?string $url): void
    {
        if ($url && str_starts_with($url, '/storage/home-banners/')) {
            Storage::disk('public')->delete(ltrim(substr($url, strlen('/storage/')), '/'));
        }
    }
}
