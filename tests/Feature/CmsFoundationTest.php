<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_home_page_uses_the_public_foundation(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Ideas que abren nuevos mundos.')
            ->assertSee('ACCESO CMS');
    }

    public function test_login_page_is_available_to_guests(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Iniciar sesión')
            ->assertSee('name="_token"', false);
    }

    public function test_admin_dashboard_requires_authentication(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_log_in_and_view_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'editor@example.test',
            'password' => 'correct horse battery staple',
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'correct horse battery staple',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee('CERRAR SESIÓN');
    }

    public function test_invalid_credentials_return_a_generic_error(): void
    {
        $this->from(route('login'))->post(route('login.store'), [
            'email' => 'unknown@example.test',
            'password' => 'incorrect',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_attempts(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->from(route('login'))->post(route('login.store'), [
                'email' => 'unknown@example.test',
                'password' => 'incorrect',
            ])->assertRedirect(route('login'));
        }

        $this->post(route('login.store'), [
            'email' => 'unknown@example.test',
            'password' => 'incorrect',
        ])->assertTooManyRequests();
    }

    public function test_authenticated_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }
}
