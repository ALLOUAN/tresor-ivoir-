<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Event;
use App\Models\Provider;
use App\Models\VisitorFavorite;
use App\Notifications\VisitorSystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VisitorFavoriteController extends Controller
{
    public function index(): View
    {
        $favorites = Auth::user()->favorites()
            ->with('favoritable')
            ->latest()
            ->paginate(20);

        return view('visitor.favorites', compact('favorites'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:provider,article,event'],
            'id' => ['required', 'integer'],
        ]);

        $modelClass = match ($data['type']) {
            'provider' => Provider::class,
            'article' => Article::class,
            default => Event::class,
        };

        $target = $modelClass::findOrFail($data['id']);
        $user = Auth::user();

        VisitorFavorite::firstOrCreate([
            'user_id' => $user->id,
            'favoritable_type' => $modelClass,
            'favoritable_id' => $target->id,
        ]);

        $label = $this->labelFor($target);
        $user->notify(new VisitorSystemNotification(
            'Nouveau favori',
            'Le contenu "'.$label.'" a bien été ajouté à votre wishlist.',
            route('visitor.favorites.index'),
        ));

        return back()->with('success', 'Ajouté à vos favoris.');
    }

    public function destroy(VisitorFavorite $favorite): RedirectResponse
    {
        if ($favorite->user_id !== Auth::id()) {
            abort(403, 'Accès refusé.');
        }

        $favorite->delete();

        return back()->with('success', 'Favori retiré.');
    }

    private function labelFor(mixed $model): string
    {
        if ($model instanceof Provider) {
            return $model->name;
        }
        if ($model instanceof Article) {
            return $model->title_fr;
        }

        return $model instanceof Event ? $model->title_fr : 'élément';
    }
}
