<?php

namespace Tests\Feature;

use App\Models\Accommodation;
use App\Models\GuestRegistration;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\Reservation;
use App\Models\TouristCity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/** Fiche d'enregistrement voyageur : champs de police, pièces en stockage privé, liens signés. */
class GuestRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private Reservation $reservation;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');

        $client = User::factory()->create(['first_name' => 'Awa', 'last_name' => 'Koné']);
        $category = ProviderCategory::create(['slug' => 'hotels-'.uniqid(), 'name_fr' => 'Hôtels', 'name_en' => 'Hotels', 'is_active' => true]);
        $provider = Provider::create(['user_id' => User::factory()->provider()->create()->id, 'category_id' => $category->id, 'name' => 'Hôtel Test', 'slug' => 'hotel-test-'.uniqid(), 'status' => 'active']);
        $city = TouristCity::create(['name' => 'Abidjan', 'slug' => 'abidjan-'.uniqid(), 'is_active' => true]);
        $acc = Accommodation::create(['provider_id' => $provider->id, 'city_id' => $city->id, 'name' => 'Hôtel Test', 'slug' => 'hotel-'.uniqid(), 'type' => 'hotel', 'is_active' => true, 'room_types' => []]);

        $this->reservation = Reservation::create([
            'user_id' => $client->id, 'provider_id' => $provider->id, 'accommodation_id' => $acc->id, 'accommodation_name' => 'Hôtel Test',
            'room_name' => 'Standard', 'check_in' => now()->addDays(10)->toDateString(), 'check_out' => now()->addDays(12)->toDateString(),
            'nights' => 2, 'rooms_count' => 1, 'guests_count' => 2, 'room_price_xof' => 30000, 'total_xof' => 60000,
            'deposit_amount_xof' => 20000, 'commission_rate_percent' => 15, 'commission_amount_xof' => 3000,
            'full_name' => 'Awa Koné', 'email' => 'awa@exemple.ci', 'phone' => '+225 0700000000',
            'status' => Reservation::STATUS_CONFIRMED, 'payment_status' => Reservation::PAYMENT_DEPOSIT_PAID,
        ]);
    }

    private function payload(array $over = []): array
    {
        return $over + [
            'travel_type' => 'domestique', 'document_type' => 'cni', 'document_number' => 'ci0012345',
            'document_expiry_date' => now()->addYears(3)->toDateString(), 'travel_reason' => 'tourisme',
            'hotel_check_in_date' => $this->reservation->check_in->toDateString(), 'hotel_check_in_time' => '15:00',
            'hotel_check_out_date' => $this->reservation->check_out->toDateString(), 'hotel_check_out_time' => '11:00',
            'first_name' => 'Awa', 'last_name' => 'Koné', 'nationality' => 'Ivoirienne', 'birth_date' => '1990-05-12',
            'birth_place' => 'Bouaké', 'father_name' => 'Koné Ali', 'mother_name' => 'Traoré Mariam', 'profession' => 'Comptable',
            'home_address' => 'Cocody, Abidjan', 'phone_country_code' => '+225', 'phone_number' => '0700000000', 'data_confirmed' => '1',
            'document_scan_front' => UploadedFile::fake()->image('recto.jpg'),
            'document_scan_back' => UploadedFile::fake()->image('verso.jpg'),
            'selfie' => UploadedFile::fake()->image('selfie.jpg'),
            'signature_image' => UploadedFile::fake()->image('signature.png'),
        ];
    }

    private function postUrl(): string
    {
        return URL::temporarySignedRoute('reservations.guest-registration.store', now()->addHour(), ['reservation' => $this->reservation->id]);
    }

    public function test_form_is_prefilled_from_the_reservation(): void
    {
        $this->get($this->reservation->guestRegistrationUrl())->assertOk()
            ->assertSee('value="Awa"', false)->assertSee('value="awa@exemple.ci"', false)->assertSee('value="0700000000"', false);
    }

    public function test_files_are_stored_privately_and_served_only_through_signed_links(): void
    {
        $this->post($this->postUrl(), $this->payload())->assertRedirect()->assertSessionHasNoErrors();

        $reg = GuestRegistration::firstOrFail();
        $this->assertSame('Ivoirienne', $reg->nationality);
        $this->assertSame('CI0012345', $reg->document_number);
        foreach (GuestRegistration::FILE_COLUMNS as $column) {
            $this->assertStringStartsWith(GuestRegistration::PRIVATE_PREFIX, $reg->{$column});
            Storage::disk('local')->assertExists(substr($reg->{$column}, strlen(GuestRegistration::PRIVATE_PREFIX)));
        }
        $this->assertSame([], Storage::disk('public')->allFiles());

        $this->get($reg->fileUrl('recto'))->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get(route('guest-registrations.file', ['registration' => $reg->id, 'kind' => 'recto']))->assertForbidden();
    }

    public function test_passport_needs_no_back_side_but_identity_card_does(): void
    {
        $this->post($this->postUrl(), $this->payload(['document_type' => 'cni', 'document_scan_back' => null]))
            ->assertSessionHasErrors('document_scan_back');

        $this->post($this->postUrl(), $this->payload(['document_type' => 'passeport', 'document_scan_back' => null]))
            ->assertSessionHasNoErrors();
        $this->assertNull(GuestRegistration::firstOrFail()->document_scan_back_url);
    }

    public function test_document_must_be_valid_for_the_whole_stay_and_nationality_is_required(): void
    {
        $this->post($this->postUrl(), $this->payload(['document_expiry_date' => now()->addDays(11)->toDateString(), 'nationality' => '']))
            ->assertSessionHasErrors(['document_expiry_date', 'nationality']);
        $this->assertSame(0, GuestRegistration::count());
    }

    public function test_fake_image_is_rejected_with_a_form_error_not_a_crash(): void
    {
        $fake = UploadedFile::fake()->createWithContent('recto.jpg', 'pas une image');
        $this->post($this->postUrl(), $this->payload(['document_scan_front' => $fake]))
            ->assertRedirect()->assertSessionHasErrors('document_scan_front');
    }

    public function test_paid_reservation_returns_to_the_summary_instead_of_payment(): void
    {
        $this->post($this->postUrl(), $this->payload())->assertSessionHas('success');
        $this->get($this->reservation->guestRegistrationUrl())->assertOk()->assertSee('Fiche transmise');
    }
}
