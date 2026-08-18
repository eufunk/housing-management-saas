<?php

namespace Tests\Feature\Auth;

use App\Actions\ProvisionDemoAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_can_start_a_demo_session_without_registering()
    {
        $response = $this->post('/demo-login');

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', ProvisionDemoAccount::DEMO_EMAIL)->firstOrFail();
        $this->assertNotNull($user->current_organization_id);
        $this->assertTrue($user->organizations()->exists());
    }

    public function test_repeated_demo_logins_reuse_the_same_account()
    {
        $this->post('/demo-login');
        $firstUserId = User::where('email', ProvisionDemoAccount::DEMO_EMAIL)->firstOrFail()->id;

        auth()->logout();

        $this->post('/demo-login');
        $secondUserId = User::where('email', ProvisionDemoAccount::DEMO_EMAIL)->firstOrFail()->id;

        $this->assertSame($firstUserId, $secondUserId);
    }

    public function test_an_already_authenticated_user_cannot_hit_the_demo_login_route()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/demo-login');

        $response->assertRedirect(route('dashboard'));
    }
}
