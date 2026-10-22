<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Le site reste en français par défaut (même si APP_LOCALE=en) ; l'anglais seulement sur choix explicite. */
class LocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_is_french_by_default_even_when_app_locale_is_english(): void
    {
        config(['app.locale' => 'en']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<html lang="fr"', false)
            ->assertSee('Annuaire')
            ->assertSee('Tous droits réservés')
            ->assertDontSee('All rights reserved');
    }

    public function test_choosing_english_translates_the_interface(): void
    {
        $this->get(route('lang.switch', 'en'))->assertRedirect();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<html lang="en"', false)
            ->assertSee('Directory')
            ->assertSee('All rights reserved');

        $this->get(route('lang.switch', 'fr'));
        $this->get(route('home'))->assertSee('Tous droits réservés');
    }

    public function test_pagination_renders_in_both_languages(): void
    {
        // Régression : la clé __('Pagination') entrait en collision avec lang/fr/pagination.php
        // (tableau renvoyé → erreur 500 sur toutes les listes paginées).
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(range(1, 100), 100, 10, 2, ['path' => '/annuaire']);

        foreach (['fr' => 'Page suivante', 'en' => 'Next page'] as $locale => $label) {
            app()->setLocale($locale);
            $html = (string) $paginator->links();
            $this->assertStringContainsString($label, $html);
        }
    }

    public function test_validation_messages_are_in_french_for_french_visitors(): void
    {
        app()->setLocale('fr');
        $errors = \Illuminate\Support\Facades\Validator::make(
            ['email' => 'pas-un-email', 'check_in' => '2020-01-01'],
            ['email' => 'required|email', 'password' => 'required', 'check_in' => 'after_or_equal:today']
        )->errors()->all();

        $this->assertContains('Le champ e-mail doit être une adresse e-mail valide.', $errors);
        $this->assertContains('Le champ mot de passe est obligatoire.', $errors);
        $this->assertContains("Le champ date d'arrivée doit être une date postérieure ou égale à aujourd'hui.", $errors);
    }

    public function test_plural_strings_stay_french_in_french(): void
    {
        app()->setLocale('fr');
        $this->assertSame('3 sites à explorer', trans_choice(':count site à explorer|:count sites à explorer', 3));
        $this->assertSame('1 site à explorer', trans_choice(':count site à explorer|:count sites à explorer', 1));

        app()->setLocale('en');
        $this->assertSame('3 sites to explore', trans_choice(':count site à explorer|:count sites à explorer', 3));
    }
}
