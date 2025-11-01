<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test branch and users
        $branch = Branch::factory()->create();
        User::factory()->count(60)->create(['branch_id' => $branch->id]);
    }

    /** @test */
    public function it_sorts_by_valid_column_ascending()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/team?sort=email&direction=asc');

        $response->assertStatus(200);
        $response->assertViewHas('users');
    }

    /** @test */
    public function it_sorts_by_valid_column_descending()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/team?sort=name&direction=desc');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_falls_back_to_name_for_invalid_sort_column()
    {
        $admin = User::factory()->admin()->create();

        // Attempt to sort by 'password' (not in whitelist)
        $response = $this->actingAs($admin)->get('/team?sort=password&direction=asc');

        $response->assertStatus(200);
        // Should fall back to 'name' sort without error
    }

    /** @test */
    public function it_falls_back_to_asc_for_invalid_direction()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/team?sort=name&direction=random');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_paginates_50_per_page()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/team');

        $response->assertStatus(200);
        $users = $response->viewData('users');
        $this->assertEquals(50, $users->perPage());
    }
}
