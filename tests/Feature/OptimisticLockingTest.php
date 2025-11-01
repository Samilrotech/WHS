<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OptimisticLockingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_detects_version_conflict_on_update()
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id, 'version' => 1]);
        $admin = User::factory()->admin()->create();

        // First update succeeds (version 1 → 2)
        $response1 = $this->actingAs($admin)
            ->putJson("/teams/{$user->id}", [
                'name' => 'Updated Name',
                'email' => $user->email,
                'version' => 1,
            ]);

        $user->refresh();
        $this->assertEquals(2, $user->version);

        // Second update with stale version fails (conflict)
        $response2 = $this->actingAs($admin)
            ->putJson("/teams/{$user->id}", [
                'name' => 'Another Update',
                'email' => $user->email,
                'version' => 1, // Stale version
            ]);

        $response2->assertStatus(409); // HTTP 409 Conflict
        $response2->assertJsonStructure([
            'error',
            'message',
            'server_version',
            'server_data',
            'client_version',
        ]);
    }

    /** @test */
    public function it_succeeds_with_correct_version()
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id, 'version' => 1]);
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->putJson("/teams/{$user->id}", [
                'name' => 'Updated Name',
                'email' => $user->email,
                'version' => 1,
            ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertEquals(2, $user->version);
        $this->assertEquals('Updated Name', $user->name);
    }

    /** @test */
    public function it_increments_version_on_every_update()
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id, 'version' => 1]);
        $admin = User::factory()->admin()->create();

        // Update 1
        $this->actingAs($admin)
            ->putJson("/teams/{$user->id}", [
                'name' => 'Name 1',
                'email' => $user->email,
                'version' => 1,
            ]);

        $user->refresh();
        $this->assertEquals(2, $user->version);

        // Update 2
        $this->actingAs($admin)
            ->putJson("/teams/{$user->id}", [
                'name' => 'Name 2',
                'email' => $user->email,
                'version' => 2,
            ]);

        $user->refresh();
        $this->assertEquals(3, $user->version);

        // Update 3
        $this->actingAs($admin)
            ->putJson("/teams/{$user->id}", [
                'name' => 'Name 3',
                'email' => $user->email,
                'version' => 3,
            ]);

        $user->refresh();
        $this->assertEquals(4, $user->version);
    }

    /** @test */
    public function it_allows_update_without_version_for_web_forms()
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id, 'version' => 1]);
        $admin = User::factory()->admin()->create();

        // Traditional web form update (no version field)
        $response = $this->actingAs($admin)
            ->put("/teams/{$user->id}", [
                'name' => 'Updated via Form',
                'email' => $user->email,
                'phone' => $user->phone,
                'employee_id' => $user->employee_id,
                'position' => $user->position,
                'branch_id' => $branch->id,
                'role' => 'employee',
                'is_active' => true,
            ]);

        $response->assertRedirect();

        $user->refresh();
        // Version should still increment even without explicit version field
        $this->assertEquals(2, $user->version);
        $this->assertEquals('Updated via Form', $user->name);
    }
}
