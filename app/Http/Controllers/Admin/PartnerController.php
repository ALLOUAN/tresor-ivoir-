<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PartnershipType;
use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PartnerController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $type = (string) $request->get('type', '');
        $status = (string) $request->get('status', '');
        $featured = (string) $request->get('featured', '');

        $query = Partner::query()->orderBy('sort_order')->orderBy('name');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $like = '%'.$q.'%';
                $sub->where('name', 'like', $like)
                    ->orWhere('contact_email', 'like', $like)
                    ->orWhere('contact_person', 'like', $like)
                    ->orWhere('website_url', 'like', $like);
            });
        }

        if ($type !== '' && array_key_exists($type, PartnershipType::options())) {
            $query->where('partnership_type', $type);
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        if ($featured === 'yes') {
            $query->where('is_featured', true);
        } elseif ($featured === 'no') {
            $query->where('is_featured', false);
        }

        $partners = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Partner::count(),
            'active' => Partner::where('is_active', true)->count(),
            'featured' => Partner::where('is_featured', true)->count(),
            'types' => Partner::query()->pluck('partnership_type')->unique()->count(),
        ];

        $typeOptions = PartnershipType::options();

        return view('admin.system.partners.index', compact(
            'partners', 'stats', 'typeOptions', 'q', 'type', 'status', 'featured'
        ));
    }

    public function create(): View
    {
        return view('admin.system.partners.create', [
            'typeOptions' => PartnershipType::options(),
            'partner' => new Partner([
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedPartnerPayload($request);

        if ($request->hasFile('logo')) {
            $validated['logo_url'] = $this->storePartnerLogo($request->file('logo'));
        }

        $partner = Partner::create($validated);

        return redirect()
            ->route('admin.administration.partners.edit', $partner)
            ->with('success', 'Partenaire créé avec succès.');
    }

    public function edit(Partner $partner): View
    {
        return view('admin.system.partners.edit', [
            'partner' => $partner,
            'typeOptions' => PartnershipType::options(),
        ]);
    }

    public function update(Request $request, Partner $partner): RedirectResponse
    {
        $validated = $this->validatedPartnerPayload($request);

        if ($request->hasFile('logo')) {
            $this->deleteStoredPublicFile($partner->logo_url);
            $validated['logo_url'] = $this->storePartnerLogo($request->file('logo'));
        }

        $partner->update($validated);

        return back()->with('success', 'Partenaire mis à jour.');
    }

    public function destroy(Partner $partner): RedirectResponse
    {
        $this->deleteStoredPublicFile($partner->logo_url);
        $partner->delete();

        return redirect()
            ->route('admin.administration.partners')
            ->with('success', 'Partenaire supprimé.');
    }

    public function toggleFeatured(Partner $partner): RedirectResponse
    {
        $partner->update(['is_featured' => ! $partner->is_featured]);

        return back()->with('success', 'Mise en vedette mise à jour.');
    }

    public function toggleActive(Partner $partner): RedirectResponse
    {
        $partner->update(['is_active' => ! $partner->is_active]);

        return back()->with('success', 'Statut mis à jour.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedPartnerPayload(Request $request): array
    {
        $request->merge([
            'website_url' => $request->filled('website_url') ? $request->input('website_url') : null,
            'partnership_start_date' => $request->filled('partnership_start_date') ? $request->input('partnership_start_date') : null,
        ]);

        $typeKeys = array_keys(PartnershipType::options());

        $rules = [
            'name' => ['required', 'string', 'max:200'],
            'partnership_type' => ['required', 'string', Rule::in($typeKeys)],
            'website_url' => ['nullable', 'string', 'max:500', 'url'],
            'partnership_start_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'contact_email' => ['nullable', 'string', 'max:255', 'email'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:99999'],
        ];

        $validated = $request->validate($rules);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        foreach (['website_url', 'description', 'contact_person', 'contact_email', 'contact_phone', 'partnership_start_date'] as $nullable) {
            if (array_key_exists($nullable, $validated) && $validated[$nullable] === '') {
                $validated[$nullable] = null;
            }
        }

        unset($validated['logo']);

        return $validated;
    }

    private function storePartnerLogo(UploadedFile $file): string
    {
        $path = $file->store('site/partners', 'public');

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
}
