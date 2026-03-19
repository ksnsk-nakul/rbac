<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Fortify\Features;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Our auth flow is role-based (login/{role}); tests need a default role present.
        Role::firstOrCreate(
            ['slug' => 'user'],
            [
                'name' => 'User',
                'route' => 'user',
                'is_subadmin' => false,
                'is_default' => true,
                'mfa_required' => false,
                'require_ip_allowlist' => false,
            ]
        );
    }

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get(route('login'));

        $response->assertRedirect('/login/user');
        $this->get('/login/user')->assertOk();
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $role = Role::where('slug', 'user')->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_with_two_factor_enabled_are_redirected_to_two_factor_challenge()
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two-factor authentication is not enabled.');
        }

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $role = Role::where('slug', 'user')->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->id]);

        $user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.login'));
        $response->assertSessionHas('login.id', $user->id);
        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $role = Role::where('slug', 'user')->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout()
    {
        $role = Role::where('slug', 'user')->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('home'));
    }

    public function test_users_are_rate_limited()
    {
        $role = Role::where('slug', 'user')->firstOrFail();
        $user = User::factory()->create(['role_id' => $role->id]);

        $throttleKey = Str::transliterate(Str::lower($user->email).'|127.0.0.1');
        RateLimiter::increment($throttleKey, amount: 5);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
    }
}
