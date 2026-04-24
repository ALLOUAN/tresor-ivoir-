<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class PublicNewsletterController extends Controller
{
    public function subscribe(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'newsletter_email' => ['required', 'email', 'max:255'],
        ]);

        if ($validator->fails()) {
            return redirect()->to(route('home').'#newsletter-footer')
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        if (! Schema::hasTable('newsletter_subscribers')) {
            return $this->redirectHome()
                ->with('newsletter_error', 'L’inscription à la newsletter n’est pas disponible pour le moment.');
        }

        $email = mb_strtolower($validated['newsletter_email']);

        $subscriber = NewsletterSubscriber::query()->where('email', $email)->first();

        if ($subscriber && $subscriber->status === 'active') {
            return $this->redirectHome()
                ->with('newsletter_info', 'Cette adresse e-mail est déjà inscrite à notre newsletter.');
        }

        if ($subscriber) {
            $subscriber->update([
                'status' => 'active',
                'confirmed_at' => now(),
                'unsubscribed_at' => null,
                'source' => 'welcome_footer',
                'locale' => 'fr',
                'ip_address' => $request->ip(),
            ]);
        } else {
            NewsletterSubscriber::query()->create([
                'email' => $email,
                'status' => 'active',
                'confirmed_at' => now(),
                'source' => 'welcome_footer',
                'locale' => 'fr',
                'ip_address' => $request->ip(),
            ]);
        }

        return $this->redirectHome()
            ->with('newsletter_success', 'Merci ! Vous êtes bien inscrit·e à notre newsletter.');
    }

    private function redirectHome(): RedirectResponse
    {
        return redirect()->to(route('home').'#newsletter-footer');
    }
}
