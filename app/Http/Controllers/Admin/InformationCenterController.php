<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InformationPage;
use Database\Seeders\InformationPageSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class InformationCenterController extends Controller
{
    public function index(): View
    {
        if (Schema::hasTable('information_pages') && InformationPage::query()->doesntExist()) {
            (new InformationPageSeeder)->run();
        }

        $pages = InformationPage::query()->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.system.information-center.index', compact('pages'));
    }

    public function edit(InformationPage $informationPage): View
    {
        return view('admin.system.information-center.edit', [
            'page' => $informationPage,
        ]);
    }

    public function update(Request $request, InformationPage $informationPage): RedirectResponse
    {
        $validated = $request->validate([
            'title_fr' => ['required', 'string', 'max:200'],
            'title_en' => ['nullable', 'string', 'max:200'],
            'body_fr' => ['nullable', 'string', 'max:100000'],
            'body_en' => ['nullable', 'string', 'max:100000'],
        ]);

        foreach (['title_en', 'body_fr', 'body_en'] as $key) {
            if (isset($validated[$key]) && $validated[$key] === '') {
                $validated[$key] = null;
            }
        }

        $informationPage->update($validated);

        return redirect()
            ->route('admin.administration.info-center.edit', $informationPage)
            ->with('success', 'Contenu enregistré.');
    }
}
