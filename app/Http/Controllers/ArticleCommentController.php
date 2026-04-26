<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArticleCommentController extends Controller
{
    public function store(Request $request, Article $article): RedirectResponse
    {
        $rules = ['content' => ['required', 'string', 'min:10', 'max:2000']];

        if (! Auth::check()) {
            $rules['author_name']  = ['required', 'string', 'max:100'];
            $rules['author_email'] = ['required', 'email', 'max:255'];
        }

        $data = $request->validate($rules, [
            'content.required' => 'Le commentaire ne peut pas être vide.',
            'content.min'      => 'Le commentaire doit contenir au moins 10 caractères.',
            'content.max'      => 'Le commentaire ne doit pas dépasser 2 000 caractères.',
            'author_name.required' => 'Votre nom est requis.',
            'author_email.required' => 'Votre e-mail est requis.',
            'author_email.email'   => 'Adresse e-mail invalide.',
        ]);

        ArticleComment::create([
            'article_id'   => $article->id,
            'user_id'      => Auth::id(),
            'author_name'  => Auth::check() ? null : $data['author_name'],
            'author_email' => Auth::check() ? null : ($data['author_email'] ?? null),
            'content'      => $data['content'],
            'is_approved'  => false,
        ]);

        return back()->with('comment_sent', 'Votre commentaire a été soumis et sera visible après modération. Merci !');
    }
}
