<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormMail;
use App\Models\ContactMessage;
use App\Models\SiteContactSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PublicContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $to = null;
        if (Schema::hasTable('site_contact_settings')) {
            $contact = SiteContactSetting::singleton();
            $to = $contact->contact_form_email ?: $contact->email_primary;
        }

        if (! $to) {
            return redirect()->route('home')
                ->withInput()
                ->with('contact_error', 'Le formulaire de contact n’est pas encore configuré (e-mail de réception dans Administration → Coordonnées).');
        }

        if (Schema::hasTable('contact_messages')) {
            ContactMessage::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'status' => ContactMessage::STATUS_NEW,
            ]);
        }

        try {
            Mail::to($to)->send(new ContactFormMail(
                $validated['name'],
                $validated['email'],
                $validated['subject'],
                $validated['message'],
            ));
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('home')
                ->withInput()
                ->with('contact_error', 'L’envoi e-mail a échoué, mais votre message a été enregistré : notre équipe pourra le consulter depuis l’administration.');
        }

        return redirect()->route('home')->with('contact_success', 'Votre message a bien été envoyé. Nous vous répondrons dès que possible.');
    }
}
