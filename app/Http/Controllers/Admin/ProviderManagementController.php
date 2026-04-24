<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProviderManagementController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $search = $request->get('q');
        $category = $request->get('category');

        $query = Provider::query()
            ->with(['category', 'user'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($category) {
            $query->where('category_id', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $providers = $query->paginate(20)->withQueryString();
        $categories = ProviderCategory::query()->orderBy('name_fr')->get();

        $counts = [
            'all' => Provider::count(),
            'active' => Provider::where('status', 'active')->count(),
            'pending' => Provider::where('status', 'pending')->count(),
            'suspended' => Provider::where('status', 'suspended')->count(),
            'featured' => Provider::where('is_featured', true)->count(),
        ];

        return view('admin.providers.index', compact('providers', 'categories', 'counts', 'status', 'search', 'category'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'user_email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:provider_categories,id',
            'provider_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:150',
            'address' => 'nullable|string|max:500',
            'description_fr' => 'nullable|string',
            'status' => 'required|in:pending,active,suspended',
            'is_featured' => 'nullable|boolean',
            'is_verified' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'email' => $data['user_email'],
                'password_hash' => $data['password'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'role' => 'provider',
                'is_active' => true,
            ]);

            $baseSlug = Str::slug($data['name']);
            $slugRoot = $baseSlug ?: Str::lower(Str::random(8));
            $slug = $slugRoot;
            $i = 2;

            while (Provider::where('slug', $slug)->exists()) {
                $slug = "{$slugRoot}-{$i}";
                $i++;
            }

            Provider::create([
                'user_id' => $user->id,
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'slug' => $slug,
                'description_fr' => $data['description_fr'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['provider_email'] ?? $data['user_email'],
                'status' => $data['status'],
                'is_featured' => ! empty($data['is_featured']),
                'is_verified' => ! empty($data['is_verified']),
            ]);
        });

        return redirect()->route('admin.providers.index')->with('success', 'Prestataire créé avec succès.');
    }

    public function update(Request $request, Provider $provider)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'user_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($provider->user_id)],
            'password' => 'nullable|string|min:8',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:provider_categories,id',
            'provider_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:150',
            'address' => 'nullable|string|max:500',
            'description_fr' => 'nullable|string',
            'status' => 'required|in:pending,active,suspended',
            'is_featured' => 'nullable|boolean',
            'is_verified' => 'nullable|boolean',
            'edit_provider_id' => 'nullable|integer',
        ]);

        DB::transaction(function () use ($provider, $data) {
            $userPayload = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['user_email'],
                'role' => 'provider',
            ];

            if (! empty($data['password'])) {
                $userPayload['password_hash'] = $data['password'];
            }

            $provider->user->update($userPayload);

            $provider->update([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'description_fr' => $data['description_fr'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['provider_email'] ?? $data['user_email'],
                'status' => $data['status'],
                'is_featured' => ! empty($data['is_featured']),
                'is_verified' => ! empty($data['is_verified']),
            ]);
        });

        return redirect()->route('admin.providers.index')->with('success', 'Prestataire modifié avec succès.');
    }

    public function validateProvider(Provider $provider)
    {
        $provider->update(['status' => 'active']);

        return back()->with('success', 'Prestataire validé avec succès.');
    }

    public function suspend(Provider $provider)
    {
        $provider->update(['status' => 'suspended']);

        return back()->with('success', 'Prestataire suspendu avec succès.');
    }
}
