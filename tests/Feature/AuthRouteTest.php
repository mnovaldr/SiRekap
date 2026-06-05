<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Feature Test untuk Route Autentikasi
 */
class AuthRouteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function root_route_redirects_to_admin_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/admin/login');
    }

    #[Test]
    public function root_route_returns_redirect_status_302(): void
    {
        $response = $this->get('/');
        $response->assertStatus(302);
    }

    #[Test]
    public function do_logout_route_redirects_to_login_with_logged_out_param(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/admin/do-logout');
        $response->assertRedirect('/admin/login?logged_out=1');
    }

    #[Test]
    public function do_logout_route_logs_out_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->assertAuthenticated();

        $this->get('/admin/do-logout');

        $this->assertGuest();
    }

    #[Test]
    public function do_logout_route_works_for_unauthenticated_user(): void
    {
        $response = $this->get('/admin/do-logout');
        $response->assertRedirect();
    }

    #[Test]
    public function do_logout_route_is_named_admin_do_logout(): void
    {
        $this->assertEquals(
            url('/admin/do-logout'),
            route('admin.do-logout')
        );
    }
}
