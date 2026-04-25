<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class PublicNewsletterController extends Controller
{
    public function subscribe(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'newsletter_email' => ['required', 'email', 'max:255'],
            'redirect_to' => ['nullable', 'string', 'in:dashboard'],
        ]);

        if ($validator->fails()) {
            return $this->redirectAfterSubscribeFailure($request)->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        if (! Schema::hasTable('newsletter_subscribers')) {
            return $this->redirectAfterSubscribeFailure($request)
                ->with('newsletter_error', 'L’inscription à la newsletter n’est pas disponible pour le moment.');
        }

        $email = mb_strtolower($validated['newsletter_email']);
        $user = Auth::user();

        if (($validated['redirect_to'] ?? null) === 'dashboard') {
            if (! $user || mb_strtolower((string) $user->email) !== $email) {
                return redirect()
                    ->route('dashboard')
                    ->withErrors(['newsletter_email' => 'Utilisez uniquement l’adresse e-mail de votre compte pour vous inscrire depuis l’espace membre.']);
            }
        }

        $userId = ($user && mb_strtolower((string) $user->email) === $email) ? $user->id : null;
        $source = ($validated['redirect_to'] ?? null) === 'dashboard' && $user
            ? 'visitor_dashboard'
            : 'welcome_footer';

        $subscriber = NewsletterSubscriber::query()->where('email', $email)->first();

        if ($subscriber && $subscriber->status === 'active') {
            return $this->redirectAfterSubscribe($validated)
                ->with('newsletter_info', 'Cette adresse e-mail est déjà inscrite à notre newsletter.');
        }

        $payload = [
            'status' => 'active',
            'confirmed_at' => now(),
            'unsubscribed_at' => null,
            'source' => $source,
            'locale' => 'fr',
            'ip_address' => $request->ip(),
        ];

        if ($userId !== null) {
            $payload['user_id'] = $userId;
            $payload['first_name'] = $user->first_name;
        }

        if ($subscriber) {
            $subscriber->update($payload);
        } else {
            NewsletterSubscriber::query()->create(array_merge([
                'email' => $email,
            ], $payload));
        }

        return $this->redirectAfterSubscribe($validated)
            ->with('newsletter_success', 'Merci ! Vous êtes bien inscrit·e à notre newsletter.');
    }

    public function unsubscribe(NewsletterSubscriber $subscriber): RedirectResponse
    {
        if (! Schema::hasTable('newsletter_subscribers')) {
            return redirect()->route('home');
        }

        if ($subscriber->status === 'active') {
            $subscriber->unsubscribe();
        }

        return redirect()
            ->route('home')
            ->with('newsletter_info', 'Vous n’êtes plus inscrit·e à la newsletter.');
    }

    private function redirectHome(): RedirectResponse
    {
        return redirect()->to(route('home').'#newsletter-footer');
    }

    /**
     * @param  array{newsletter_email: string, redirect_to?: string|null}  $validated
     */
    private function redirectAfterSubscribe(array $validated): RedirectResponse
    {
        if (($validated['redirect_to'] ?? null) === 'dashboard' && Auth::check()) {
            return redirect()->route('dashboard');
        }

        return $this->redirectHome();
    }

    private function redirectAfterSubscribeFailure(Request $request): RedirectResponse
    {
        if ($request->input('redirect_to') === 'dashboard' && Auth::check()) {
            return redirect()->route('dashboard');
        }

        return $this->redirectHome();
    }
}
