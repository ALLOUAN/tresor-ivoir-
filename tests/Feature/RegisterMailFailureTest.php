<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Tests\TestCase;

/** Une panne du serveur d'e-mails (ex. « Connection … timed out ») ne doit pas faire échouer l'inscription. */
class RegisterMailFailureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::extend('panne', fn () => new class extends AbstractTransport
        {
            protected function doSend(SentMessage $message): void
            {
                throw new TransportException('Connection to "ssl://mail.exemple:465" timed out.');
            }

            public function __toString(): string
            {
                return 'panne://';
            }
        });
        config(['mail.mailers.panne' => ['transport' => 'panne'], 'mail.default' => 'panne']);
    }

    public function test_registration_succeeds_and_warns_when_mail_server_is_down(): void
    {
        $this->post(route('register.post'), [
            'first_name' => 'Awa', 'last_name' => 'Koné', 'email' => 'awa@exemple.ci',
            'password' => 'MotDePasse123!', 'password_confirmation' => 'MotDePasse123!',
            'role' => 'visitor', 'terms' => '1',
        ])->assertRedirect(route('verification.notice'))->assertSessionHas('mail_warning');

        $user = User::firstWhere('email', 'awa@exemple.ci');
        $this->assertNotNull($user);
        $this->assertAuthenticatedAs($user);

        // La page de vérification s'affiche avec l'avertissement au lieu d'une erreur 500.
        $this->get(route('verification.notice'))->assertOk()->assertSee('Renvoyer le code');
        $this->post(route('verification.send'))->assertRedirect()->assertSessionHas('mail_warning');
    }
}
