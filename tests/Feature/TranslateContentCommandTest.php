<?php

namespace Tests\Feature;

use App\Models\InformationPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Traduction des contenus : remplit les champs anglais vides, n'écrase jamais une saisie existante. */
class TranslateContentCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_fills_only_empty_english_fields(): void
    {
        config(['ai.llm.api_key' => 'test-key']);
        Http::fake(['api.anthropic.com/*' => Http::response(['content' => [['type' => 'text', 'text' => 'About us']]])]);

        $empty = InformationPage::create(['slug' => 'a-propos', 'title_fr' => 'À propos', 'body_fr' => 'Texte', 'body_en' => 'Text']);
        $kept = InformationPage::create(['slug' => 'cgu', 'title_fr' => 'Conditions', 'title_en' => 'Terms (human)', 'body_fr' => 'Corps', 'body_en' => 'Body']);

        $this->artisan('content:translate-en', ['--table' => 'information_pages'])->assertSuccessful();

        $this->assertSame('About us', DB::table('information_pages')->where('id', $empty->id)->value('title_en'));
        $this->assertSame('Terms (human)', DB::table('information_pages')->where('id', $kept->id)->value('title_en'));
        Http::assertSentCount(1);
    }

    public function test_dry_run_and_missing_key_never_call_the_api(): void
    {
        Http::fake();
        config(['ai.llm.api_key' => null]);
        InformationPage::create(['slug' => 'x', 'title_fr' => 'Titre', 'body_fr' => 'Corps']);

        $this->artisan('content:translate-en', ['--dry-run' => true])->assertSuccessful();
        $this->artisan('content:translate-en')->assertFailed();
        Http::assertNothingSent();
    }

    public function test_english_pages_show_the_translated_title_with_french_fallback(): void
    {
        $page = InformationPage::create(['slug' => 'about', 'title_fr' => 'À propos', 'title_en' => 'About us', 'body_fr' => 'Corps']);

        app()->setLocale('en');
        $this->assertSame('About us', $page->localized('title'));
        $this->assertSame('Corps', $page->localized('body'), 'Repli sur le français quand l\'anglais est vide.');

        app()->setLocale('fr');
        $this->assertSame('À propos', $page->localized('title'));
    }
}
