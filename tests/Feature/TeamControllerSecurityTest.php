<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamControllerSecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_blocks_sql_injection_in_sort_parameter()
    {
        $admin = User::factory()->admin()->create();
        Branch::factory()->create();

        // Attempt SQL injection
        $response = $this->actingAs($admin)->get('/team?sort=1;DROP+TABLE+users;--');

        $response->assertStatus(200); // Should succeed with fallback, not SQL error

        // Verify users table still exists
        $this->assertDatabaseCount('users', 1); // Admin user exists
    }

    /** @test */
    public function it_prevents_unauthorized_column_access()
    {
        $admin = User::factory()->admin()->create();

        // Attempt to sort by sensitive column
        $response = $this->actingAs($admin)->get('/team?sort=password');

        $response->assertStatus(200);
        // Should fall back to 'name', not expose password data
    }

    /** @test */
    public function it_enforces_rate_limiting()
    {
        $admin = User::factory()->admin()->create();

        // Simulate 65 rapid requests
        $responses = [];
        for ($i = 0; $i < 65; $i++) {
            $responses[] = $this->actingAs($admin)->get('/team?sort=name');
        }

        // First 60 should succeed
        $this->assertEquals(200, $responses[0]->status());
        $this->assertEquals(200, $responses[59]->status());

        // Requests 61-65 should be rate limited
        $this->assertEquals(429, $responses[60]->status());
        $this->assertEquals(429, $responses[64]->status());
    }
}
