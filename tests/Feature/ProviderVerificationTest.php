<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\ProviderDocument;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Aucun retrait sans compte vérifié ; la vérification passe par des justificatifs privés
 * examinés par l'administration.
 */
class ProviderVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $providerUser;

    private Provider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Notification::fake();
        $this->providerUser = User::factory()->provider()->create();
        $category = ProviderCategory::create(['slug' => 'hotels-'.uniqid(), 'name_fr' => 'Hôtels', 'name_en' => 'Hotels', 'is_active' => true]);
        $this->provider = Provider::create([
            'user_id' => $this->providerUser->id, 'category_id' => $category->id,
            'name' => 'Hôtel KYC', 'slug' => 'hotel-kyc-'.uniqid(), 'status' => 'active', 'is_verified' => false,
        ]);
    }

    public function test_an_unverified_provider_cannot_request_a_payout(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('vérifié');

        app(WalletService::class)->requestPayout($this->provider, 1000, 'orange_money', '0700000000');
    }

    public function test_documents_are_stored_privately_and_approval_of_all_required_pieces_verifies_the_provider(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);

        foreach (ProviderDocument::REQUIRED_TYPES as $type) {
            $this->actingAs($this->providerUser)->post(route('provider.verification.store'), [
                'type' => $type,
                'document' => UploadedFile::fake()->create($type.'.pdf', 200, 'application/pdf'),
            ])->assertRedirect()->assertSessionHas('success');
        }

        $docs = ProviderDocument::where('provider_id', $this->provider->id)->get();
        $this->assertCount(2, $docs);
        Storage::disk('local')->assertExists($docs[0]->file_path);
        $this->assertStringNotContainsString('public', $docs[0]->file_path);

        $this->actingAs($admin)->get(route('admin.provider-verifications.download', $docs[0]))->assertOk();
        $this->actingAs($this->providerUser)->get(route('admin.provider-verifications.download', $docs[0]))->assertForbidden();

        $this->actingAs($admin)->get(route('admin.provider-verifications.index'))->assertOk()->assertSee('Hôtel KYC');
        $this->actingAs($this->providerUser)->get(route('provider.wallet.index'))->assertOk()->assertSee('pas encore vérifié');

        $this->actingAs($admin)->post(route('admin.provider-verifications.approve', $docs[0]))->assertRedirect();
        $this->assertFalse($this->provider->fresh()->is_verified, 'Une seule pièce validée ne suffit pas.');

        $this->actingAs($admin)->post(route('admin.provider-verifications.approve', $docs[1]))->assertRedirect();
        $this->assertTrue($this->provider->fresh()->is_verified);
    }

    public function test_a_rejected_document_requires_a_reason_and_keeps_the_provider_unverified(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);
        $doc = ProviderDocument::create([
            'provider_id' => $this->provider->id, 'type' => ProviderDocument::TYPE_IDENTITY,
            'file_path' => 'provider-documents/x.pdf', 'status' => ProviderDocument::STATUS_PENDING,
        ]);

        $this->actingAs($admin)->post(route('admin.provider-verifications.reject', $doc), [])->assertSessionHasErrors('reason');
        $this->actingAs($admin)->post(route('admin.provider-verifications.reject', $doc), ['reason' => 'Illisible'])->assertRedirect();

        $this->assertSame(ProviderDocument::STATUS_REJECTED, $doc->fresh()->status);
        $this->assertFalse($this->provider->fresh()->is_verified);
        $this->actingAs($this->providerUser)->get(route('provider.verification.index'))->assertOk()->assertSee('Illisible');
    }
}
