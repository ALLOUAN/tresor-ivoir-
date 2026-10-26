<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SiteAudience;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** Audience du site : comptage anonyme des visiteurs, robots et back-office exclus, chiffres du tableau de bord. */
class SiteAudienceTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36';

    private function user(string $role): User
    {
        return User::create([
            'first_name' => 'Test', 'last_name' => ucfirst($role), 'email' => $role.'@exemple.ci',
            'password_hash' => Hash::make('MotDePasse123!'), 'role' => $role, 'is_active' => true,
            'is_verified' => true, 'email_verified_at' => now(),
        ]);
    }

    public function test_counts_human_visitors_once_per_day_without_storing_ip(): void
    {
        $this->withHeaders(['User-Agent' => self::BROWSER])->get('/')->assertOk();
        $this->withHeaders(['User-Agent' => self::BROWSER])->get('/')->assertOk();   // même visiteur : pas de doublon
        $this->withHeaders(['User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)'])->get('/')->assertOk();

        $this->assertSame(1, DB::table('site_visitors')->count(), 'Un seul visiteur humain, robot ignoré');
        $row = DB::table('site_visitors')->first();
        $this->assertSame(64, strlen($row->visitor_hash), 'Empreinte SHA-256');
        $this->assertStringNotContainsString('127.0.0.1', json_encode($row), 'Aucune IP stockée');
    }

    public function test_back_office_and_admins_are_not_counted(): void
    {
        $admin = $this->user('admin');
        $this->actingAs($admin)->withHeaders(['User-Agent' => self::BROWSER])->get('/');
        $this->assertSame(0, DB::table('site_visitors')->count(), 'Les administrateurs ne sont pas comptés');
    }

    public function test_dashboard_shows_audience_and_live_endpoint(): void
    {
        $today = now();
        DB::table('site_visitors')->insert([
            ['date' => $today->toDateString(), 'visitor_hash' => str_repeat('a', 64), 'hits' => 3, 'first_seen_at' => $today, 'last_seen_at' => $today],
            ['date' => $today->toDateString(), 'visitor_hash' => str_repeat('b', 64), 'hits' => 1, 'first_seen_at' => $today->copy()->subHours(2), 'last_seen_at' => $today->copy()->subHours(2)],
            ['date' => $today->copy()->subDays(3)->toDateString(), 'visitor_hash' => str_repeat('c', 64), 'hits' => 1, 'first_seen_at' => $today->copy()->subDays(3), 'last_seen_at' => $today->copy()->subDays(3)],
        ]);

        $s = app(SiteAudience::class)->summary();
        $this->assertSame(1, $s['online'], 'En ligne : vu il y a moins de 5 minutes');
        $this->assertSame(2, $s['today']);
        $this->assertSame(3, $s['week']);
        $this->assertCount(24, $s['series']['day']['values']);
        $this->assertCount(7, $s['series']['week']['values']);
        $this->assertCount(30, $s['series']['month']['values']);
        $this->assertCount(12, $s['series']['year']['values']);

        $admin = $this->user('admin');
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Audience du site')->assertSee('En ligne maintenant');
        $this->actingAs($admin)->getJson(route('admin.dashboard.audience'))->assertOk()->assertJsonPath('today', 2)->assertJsonPath('online', 1);
    }
}
