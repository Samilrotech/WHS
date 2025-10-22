<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use App\Modules\IncidentManagement\Models\Incident;
use App\Modules\IncidentManagement\Models\Witness;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Roles
        $adminRole = Role::create(['name' => 'Admin']);
        $managerRole = Role::create(['name' => 'Manager']);
        $employeeRole = Role::create(['name' => 'Employee']);

        // Create Permissions
        $permissions = [
            'view incidents',
            'create incidents',
            'edit incidents',
            'delete incidents',
            'assign incidents',
            'close incidents',
            'view all branches',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign all permissions to Admin
        $adminRole->givePermissionTo(Permission::all());

        // Assign specific permissions to Manager
        $managerRole->givePermissionTo([
            'view incidents',
            'create incidents',
            'edit incidents',
            'assign incidents',
            'close incidents',
        ]);

        // Assign basic permissions to Employee
        $employeeRole->givePermissionTo([
            'view incidents',
            'create incidents',
        ]);

        // Create System Administrator (SaaS - No branch assignment initially)
        $admin = User::create([
            'branch_id' => null, // Admin is not assigned to any branch initially
            'name' => 'System Administrator',
            'email' => 'admin@whs4.com.au',
            'password' => Hash::make('Admin@2025!'),
            'phone' => null,
            'employee_id' => 'ADMIN001',
            'position' => 'System Administrator',
            'is_active' => true,
        ]);
        $admin->assignRole('Admin');

        // Seed cornerstone branches so administrators have working data sets
        Branch::factory()->create([
            'name' => 'Sydney Operations Centre',
            'code' => 'SYD',
            'address' => '23 Harbour Street',
            'city' => 'Sydney',
            'state' => 'NSW',
            'postcode' => '2000',
            'phone' => '(02) 8123 4500',
            'email' => 'sydney@whs4.com.au',
            'manager_name' => 'Amelia Carter',
        ]);

        Branch::factory()->create([
            'name' => 'Brisbane Logistics Hub',
            'code' => 'BNE',
            'address' => '88 Riverside Drive',
            'city' => 'Brisbane',
            'state' => 'QLD',
            'postcode' => '4000',
            'phone' => '(07) 3567 8800',
            'email' => 'brisbane@whs4.com.au',
            'manager_name' => 'Jacob Williams',
        ]);

        Branch::factory()->inactive()->create([
            'name' => 'Perth Regional Office',
            'code' => 'PER',
            'address' => '12 Forrest Place',
            'city' => 'Perth',
            'state' => 'WA',
            'postcode' => '6000',
            'phone' => '(08) 9201 3322',
            'email' => 'perth@whs4.com.au',
            'manager_name' => 'Sienna Brooks',
        ]);

        $this->command->info('');
        $this->command->info('WHS4 SaaS System Initialized!');
        $this->command->info('');
        $this->command->info('Summary:');
        $this->command->info('   - 3 Roles created (Admin, Manager, Employee)');
        $this->command->info('   - 7 Permissions configured');
        $this->command->info('   - 1 System Administrator created');
        $this->command->info('   - 3 Branch records seeded (Sydney, Brisbane, Perth)');
        $this->command->info('');
        $this->command->info('Login Credentials:');
        $this->command->info('   Email:    admin@whs4.com.au');
        $this->command->info('   Password: Admin@2025!');
        $this->command->info('');
        $this->command->info('Next Steps:');
        $this->command->info('   1. Login as admin');
        $this->command->info('   2. Create branches via Branch Management');
        $this->command->info('   3. Create employees and assign to branches');
        $this->command->info('   4. Create vehicles and assign to employees');
        $this->command->info('   5. Employees submit monthly vehicle reports');
        $this->command->info('');
    }
}
