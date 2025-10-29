<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Modules\VehicleManagement\Models\Vehicle;
use App\Modules\VehicleManagement\Models\VehicleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeamManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('Admin');
        Role::findOrCreate('Manager');
        Role::findOrCreate('Employee');
        Role::findOrCreate('Supervisor');
    }

    public function test_admin_can_view_team_directory(): void
    {
        $admin = $this->actingAsAdmin();

        $branch = Branch::factory()->create(['name' => 'Logistics']);
        $member = User::factory()->create([
            'branch_id' => $branch->id,
            'name' => 'Logistics Lead',
        ]);
        $member->assignRole('Employee');

        $response = $this->actingAs($admin)->get(route('teams.index'));

        $response->assertOk();
        $response->assertSee('Logistics Lead');
    }

    public function test_manager_only_sees_members_in_their_branch(): void
    {
        $branchA = Branch::factory()->create(['name' => 'Sydney']);
        $branchB = Branch::factory()->create(['name' => 'Perth']);

        $manager = User::factory()->create([
            'branch_id' => $branchA->id,
            'name' => 'Sydney Manager',
        ]);
        $manager->assignRole('Manager');

        $localMember = User::factory()->create([
            'branch_id' => $branchA->id,
            'name' => 'Local Operator',
        ]);
        $localMember->assignRole('Employee');

        $remoteMember = User::factory()->create([
            'branch_id' => $branchB->id,
            'name' => 'Remote Operator',
        ]);
        $remoteMember->assignRole('Employee');

        $response = $this->actingAs($manager)->get(route('teams.index'));

        $response->assertOk();
        $response->assertSee('Local Operator');
        $response->assertDontSee('Remote Operator');
    }

    public function test_admin_can_create_team_member(): void
    {
        $admin = $this->actingAsAdmin();
        $branch = Branch::factory()->create();

        $payload = [
            'name' => 'Harper Collins',
            'employee_id' => 'EMP-500',
            'email' => 'harper@example.com',
            'phone' => '0400 222 333',
            'position' => 'Fleet Driver',
            'branch_id' => $branch->id,
            'role' => 'employee',
            'is_active' => '1',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'employment_start_date' => '2024-01-10',
            'notes' => 'Night shift driver',
        ];

        $response = $this->actingAs($admin)->post(route('teams.store'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'harper@example.com',
            'employment_status' => 'active',
            'employment_start_date' => '2024-01-10',
        ]);

        $created = User::whereEmail('harper@example.com')->first();
        $this->assertTrue($created->hasRole('Employee'));
        $this->assertTrue(Hash::check('Secret123!', $created->password));
    }

    public function test_admin_can_update_team_member(): void
    {
        $admin = $this->actingAsAdmin();
        $branch = Branch::factory()->create();
        $newBranch = Branch::factory()->create();

        $member = User::factory()->create([
            'branch_id' => $branch->id,
            'name' => 'Alex Rivers',
            'employment_status' => 'active',
        ]);
        $member->assignRole('Employee');

        $payload = [
            'name' => 'Alex Rivers',
            'employee_id' => $member->employee_id,
            'email' => 'alex.rivers@example.com',
            'phone' => '0400 555 111',
            'position' => 'Safety Supervisor',
            'branch_id' => $newBranch->id,
            'role' => 'supervisor',
            'is_active' => '0',
            'password' => '',
            'password_confirmation' => '',
            'employment_start_date' => '2023-07-01',
            'notes' => 'Transferred to Perth branch',
        ];

        $response = $this->actingAs($admin)->put(route('teams.update', $member), $payload);

        $response->assertRedirect(route('teams.show', $member));

        $member->refresh();
        $this->assertSame('alex.rivers@example.com', $member->email);
        $this->assertSame($newBranch->id, $member->branch_id);
        $this->assertSame('inactive', $member->employment_status);
        $this->assertEquals('2023-07-01', optional($member->employment_start_date)->format('Y-m-d'));
        $this->assertSame('Transferred to Perth branch', $member->notes);
        $this->assertTrue($member->hasRole('Supervisor'));
    }

    public function test_member_with_active_assignment_cannot_be_deleted(): void
    {
        $admin = $this->actingAsAdmin();
        $branch = Branch::factory()->create();

        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'name' => 'Assigned Driver',
        ]);
        $user->assignRole('Employee');

        $vehicle = Vehicle::factory()->create([
            'branch_id' => $branch->id,
        ]);

        VehicleAssignment::factory()->create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'returned_date' => null,
        ]);

        $response = $this->actingAs($admin)->delete(route('teams.destroy', $user));

        $response->assertRedirect(route('teams.show', $user));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);
    }

    public function test_admin_can_mark_team_member_on_leave(): void
    {
        $admin = $this->actingAsAdmin();
        $branch = Branch::factory()->create();

        $member = User::factory()->create([
            'branch_id' => $branch->id,
            'employment_status' => 'active',
            'is_active' => true,
        ]);
        $member->assignRole('Employee');

        $response = $this->actingAs($admin)->post(route('teams.on-leave', $member));

        $response->assertRedirect();
        $member->refresh();

        $this->assertSame('on_leave', $member->employment_status);
        $this->assertFalse((bool) $member->is_active);
    }

    public function test_member_with_active_assignment_cannot_be_marked_on_leave(): void
    {
        $admin = $this->actingAsAdmin();
        $branch = Branch::factory()->create();

        $member = User::factory()->create([
            'branch_id' => $branch->id,
            'employment_status' => 'active',
            'is_active' => true,
        ]);
        $member->assignRole('Employee');

        $vehicle = Vehicle::factory()->create([
            'branch_id' => $branch->id,
        ]);

        VehicleAssignment::factory()->create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $member->id,
            'returned_date' => null,
        ]);

        $response = $this->actingAs($admin)->post(route('teams.on-leave', $member));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $member->refresh();
        $this->assertSame('active', $member->employment_status);
        $this->assertTrue((bool) $member->is_active);
    }

    protected function actingAsAdmin(): User
    {
        $admin = User::factory()->create([
            'name' => 'System Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('Admin@123'),
        ]);

        $admin->assignRole('Admin');

        return $admin;
    }
}