<?php

namespace Tests\Feature;

use App\Services\Ai\Analytics\PlanAccess;
use App\Services\Ai\Analytics\ProviderForecaster;
use App\Services\Ai\ChatAssistant;
use App\Services\Ai\Moderation\TextAnalyzer;
use App\Support\HtmlSanitizer;
use App\Support\ImageUploadGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Filet de sécurité des modules IA (recommandation, recherche, assistant, modération, analytique)
 * et des correctifs de sécurité associés. Ne dépend d'aucun service externe : les modèles de langage
 * sont désactivés, seuls les moteurs locaux sont exercés.
 */
class AiModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_ai_pages_answer_for_guests(): void
    {
        foreach (['/pour-vous', '/mon-sejour', '/ia/recommandations', '/ia/recherche?q=hotel', '/recherche?q=hotel'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_smart_search_json_has_expected_shape(): void
    {
        $json = $this->getJson('/ia/recherche?q=hotel+a+grand-bassam')->assertOk()->json();

        $this->assertSame('hotel a grand-bassam', $json['query']);
        $this->assertArrayHasKey('understood', $json);
        $this->assertIsArray($json['results']);
    }

    public function test_chat_assistant_handles_empty_greeting_and_human_handoff(): void
    {
        $chat = app(ChatAssistant::class);

        $this->assertSame('smalltalk', $chat->answer('')['status']);
        $this->assertSame('smalltalk', $chat->answer('bonjour')['status']);
        $this->assertTrue($chat->answer('je veux parler à un humain')['handoff']);
    }

    public function test_text_moderation_flags_spam_and_leaves_clean_text_alone(): void
    {
        $analyzer = app(TextAnalyzer::class);

        $spam = $analyzer->analyze('GAGNEZ de ARGENT facile cliquez ici maintenant!!! envoyez votre mot de passe', ['kind' => 'review']);
        $this->assertArrayHasKey('spam_phrases', $spam['flags']);
        $this->assertGreaterThanOrEqual(40, $spam['score']);

        $clean = $analyzer->analyze(str_repeat('Le quartier France de Grand-Bassam est un ensemble colonial classé par l\'UNESCO, avec des maisons restaurées et des musées. ', 8));
        $this->assertArrayNotHasKey('spam_phrases', $clean['flags']);
        $this->assertArrayNotHasKey('profanity', $clean['flags']);
    }

    public function test_forecast_refuses_to_predict_without_enough_history(): void
    {
        $forecaster = app(ProviderForecaster::class);

        $this->assertSame('insufficient', $forecaster->forecast(1, 7, [])['status']);
        $this->assertSame([8, 8, 8], array_map('intval', $forecaster->predict(
            'moyenne',
            [5, 6, 7, 8, 9, 10, 11],
            array_map(fn ($i) => date('Y-m-d', strtotime("-$i day")), range(6, 0)),
            3,
        )));
    }

    public function test_provider_without_subscription_only_gets_basic_insights(): void
    {
        $access = app(PlanAccess::class)->for(999999);

        $this->assertSame('basic', $access['level']);
        $this->assertSame(1, $access['rank']);
    }

    public function test_payment_webhooks_are_not_rejected_by_csrf(): void
    {
        $this->withMiddleware();   // la protection CSRF est active : c'est ce qu'on vérifie

        foreach (['/webhook/cynetpay', '/galerie/achat/webhook', '/art-creations/achat/webhook', '/reservations/paiement/webhook', '/wallet/recharge/webhook', '/visites/webhook'] as $url) {
            $this->assertNotSame(419, $this->post($url, ['x' => 1])->getStatusCode(), $url);
        }
    }

    public function test_permissions_policy_allows_geolocation_for_the_itinerary_feature(): void
    {
        $header = $this->get('/')->headers->get('Permissions-Policy');

        $this->assertStringContainsString('geolocation=(self)', (string) $header);
    }

    public function test_page_sanitizer_keeps_structure_but_removes_scripts(): void
    {
        $html = HtmlSanitizer::pageBody('<section><details><summary>Q</summary><p onclick="x()">R</p></details><script>alert(1)</script></section>');

        $this->assertStringContainsString('<details>', $html);
        $this->assertStringContainsString('<summary>', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('onclick', $html);
    }

    public function test_image_guard_rejects_a_text_file_renamed_as_jpg(): void
    {
        $fake = UploadedFile::fake()->createWithContent('photo.jpg', 'ceci n\'est pas une image');
        $real = UploadedFile::fake()->image('photo.jpg', 20, 20);

        $this->assertFalse(ImageUploadGuard::isRealImage($fake));
        $this->assertTrue(ImageUploadGuard::isRealImage($real));
    }
}
