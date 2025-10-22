<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BranchManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('Admin');
    }

    public function test_admin_can_view_the_branch_directory_with_filters(): void
    {
        $admin = $this->actingAsAdmin();

        $activeBranch = Branch::factory()->create([
            'name' => 'Sydney Operations',
            'code' => 'SYD',
            'is_active' => true,
        ]);

        $inactiveBranch = Branch::factory()->inactive()->create([
            'name' => 'Perth Logistics',
            'code' => 'PER',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('branches.index', ['status' => 'inactive']));

        $response->assertOk()
            ->assertSee('Branch Management')
            ->assertSee($inactiveBranch->name)
            ->assertDontSee($activeBranch->name);
    }

    public function test_admin_can_create_a_branch(): void
    {
        $admin = $this->actingAsAdmin();

        $payload = [
            'name' => 'Melbourne Hub',
            'code' => 'MEL',
            'address' => '123 Collins Street',
            'city' => 'Melbourne',
            'state' => 'VIC',
            'postcode' => '3000',
            'phone' => '03 9000 0000',
            'email' => 'melbourne@whs4.com.au',
            'manager_name' => 'Jamie Doe',
            'is_active' => true,
        ];

        $response = $this->actingAs($admin)
            ->post(route('branches.store'), $payload);

        $response->assertRedirect(route('branches.index'));
        $this->assertDatabaseHas('branches', [
            'name' => 'Melbourne Hub',
            'code' => 'MEL',
            'city' => 'Melbourne',
            'state' => 'VIC',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_branch_details(): void
    {
        $admin = $this->actingAsAdmin();
        $branch = Branch::factory()->create([
            'name' => 'Gold Coast Office',
            'code' => 'GCO',
            'city' => 'Gold Coast',
            'state' => 'QLD',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('branches.update', $branch), [
                'name' => 'Gold Coast Operations',
                'code' => 'GCO',
                'address' => '456 Marine Parade',
                'city' => 'Gold Coast',
                'state' => 'QLD',
                'postcode' => '4217',
                'phone' => '07 7000 0000',
                'email' => 'goldcoast@whs4.com.au',
                'manager_name' => 'Alex Taylor',
                'is_active' => false,
            ]);

        $response->assertRedirect(route('branches.index'));

        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'name' => 'Gold Coast Operations',
            'postcode' => '4217',
            'is_active' => false,
        ]);
    }

    public function test_branch_with_employees_cannot_be_deleted(): void
    {
        $admin = $this->actingAsAdmin();
        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id]);
        $user->assignRole('Admin');

        $response = $this->actingAs($admin)
            ->delete(route('branches.destroy', $branch));

        $response->assertRedirect(route('branches.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('branches', ['id' => $branch->id]);
    }

    public function test_an_empty_branch_can_be_deleted(): void
    {
        $admin = $this->actingAsAdmin();
        $branch = Branch::factory()->create();

        $response = $this->actingAs($admin)
            ->delete(route('branches.destroy', $branch));

        $response->assertRedirect(route('branches.index'));
        $this->assertSoftDeleted($branch);
    }

    public function test_an_admin_can_toggle_branch_status(): void
    {
        $admin = $this->actingAsAdmin();
        $branch = Branch::factory()->inactive()->create();

        $response = $this->actingAs($admin)
            ->post(route('branches.toggleStatus', $branch));

        $response->assertRedirect(route('branches.index'));
        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'is_active' => true,
        ]);
    }

    protected function actingAsAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');

        return $user;
    }
}
