<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\ProviderHour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    private function getProvider(): Provider
    {
        $provider = Auth::user()->providers()->first();
        if (! $provider) {
            abort(404, 'Aucune fiche prestataire trouvée.');
        }

        return $provider;
    }

    public function edit()
    {
        $provider = $this->getProvider();
        $categories = ProviderCategory::where('is_active', true)->orderBy('sort_order')->get();
        $days = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

        $hours = [];
        foreach (range(0, 6) as $day) {
            $hours[$day] = $provider->hours->firstWhere('day_of_week', $day)
                ?? new ProviderHour(['day_of_week' => $day, 'is_closed' => true]);
        }

        return view('provider.profile.edit', compact('provider', 'categories', 'hours', 'days'));
    }

    public function update(Request $request)
    {
        $provider = $this->getProvider();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:provider_categories,id',
            'short_desc_fr' => 'nullable|string|max:500',
            'description_fr' => 'nullable|string',
            'description_en' => 'nullable|string',
            'cover_url' => 'nullable|url|max:500',
            'logo_url' => 'nullable|url|max:500',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:150',
            'region' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:20',
            'phone2' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:500',
            'facebook_url' => 'nullable|url|max:500',
            'instagram_url' => 'nullable|url|max:500',
            'tiktok_url' => 'nullable|url|max:500',
            'linkedin_url' => 'nullable|url|max:500',
            'price_range' => 'nullable|in:budget,mid,premium,luxury',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0',
            'meta_title_fr' => 'nullable|string|max:70',
            'meta_desc_fr' => 'nullable|string|max:165',
        ]);

        $provider->update($data);

        return back()->with('success', 'Fiche mise à jour avec succès.');
    }

    public function updateHours(Request $request)
    {
        $provider = $this->getProvider();

        $request->validate([
            'hours' => 'required|array',
            'hours.*.day_of_week' => 'required|integer|between:0,6',
            'hours.*.open_time' => 'nullable|date_format:H:i',
            'hours.*.close_time' => 'nullable|date_format:H:i',
        ]);

        foreach ($request->hours as $dayData) {
            $isClosed = isset($dayData['is_closed']);
            ProviderHour::updateOrCreate(
                ['provider_id' => $provider->id, 'day_of_week' => $dayData['day_of_week']],
                [
                    'is_closed' => $isClosed,
                    'open_time' => $isClosed ? null : ($dayData['open_time'] ?? null),
                    'close_time' => $isClosed ? null : ($dayData['close_time'] ?? null),
                    'note' => $dayData['note'] ?? null,
                ]
            );
        }

        return back()->with('success', 'Horaires mis à jour.');
    }
}
