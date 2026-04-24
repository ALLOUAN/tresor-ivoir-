<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Provider $provider)
    {
        $data = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'rating_quality' => 'nullable|integer|between:1,5',
            'rating_price' => 'nullable|integer|between:1,5',
            'rating_welcome' => 'nullable|integer|between:1,5',
            'rating_clean' => 'nullable|integer|between:1,5',
            'title' => 'nullable|string|max:200',
            'comment' => 'required|string|min:20|max:2000',
            'author_name' => 'nullable|string|max:150',
            'visit_date' => 'nullable|date|before_or_equal:today',
        ]);

        if (Auth::check()) {
            $alreadyReviewed = Review::where('provider_id', $provider->id)
                ->where('user_id', Auth::id())->exists();

            if ($alreadyReviewed) {
                return back()->with('error', 'Vous avez déjà laissé un avis pour cet établissement.');
            }

            $data['user_id'] = Auth::id();
            $data['author_name'] = $data['author_name'] ?: Auth::user()->full_name;
        }

        $data['provider_id'] = $provider->id;
        $data['ip_address'] = $request->ip();
        $data['status'] = 'pending';

        Review::create($data);

        return back()->with('success', 'Votre avis a été soumis et sera publié après modération. Merci !');
    }
}
