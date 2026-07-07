<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_visit_dashboard()
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.dashboard'));
        $response->assertOk();
    }

    public function test_member_can_visit_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('member.dashboard'));
        $response->assertOk();
    }

    public function test_member_cannot_visit_admin_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('admin.dashboard'));
        $response->assertForbidden();
    }

    public function test_dashboard_redirects_based_on_role()
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('admin.dashboard'));

        $member = User::factory()->create();
        $this->actingAs($member);
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('member.dashboard'));
    }
}
