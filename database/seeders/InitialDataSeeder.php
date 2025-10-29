<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use App\Modules\IncidentManagement\Models\Incident;
use App\Modules\IncidentManagement\Models\Witness;
use App\Modules\VehicleManagement\Models\Vehicle;
use App\Modules\VehicleManagement\Models\VehicleAssignment;
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
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $managerRole = Role::firstOrCreate(['name' => 'Manager']);
        $employeeRole = Role::firstOrCreate(['name' => 'Employee']);

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
            Permission::firstOrCreate(['name' => $permission]);
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
        $admin = User::firstOrCreate(
            ['email' => 'admin@whs4.com.au'],
            [
                'branch_id' => null,
                'name' => 'System Administrator',
                'password' => Hash::make('Admin@2025!'),
                'phone' => null,
                'employee_id' => 'ADMIN001',
                'position' => 'System Administrator',
                'is_active' => true,
            ]
        );
        $admin->assignRole('Admin');

        // Seed cornerstone branches so administrators have working data sets
        $sydney = Branch::firstOrCreate(
            ['code' => 'SYD'],
            [
                'name' => 'Sydney Operations Centre',
                'address' => '23 Harbour Street',
                'city' => 'Sydney',
                'state' => 'NSW',
                'postcode' => '2000',
                'phone' => '(02) 8123 4500',
                'email' => 'sydney@whs4.com.au',
                'manager_name' => 'Amelia Carter',
                'is_active' => true,
            ]
        );

        $brisbane = Branch::firstOrCreate(
            ['code' => 'BNE'],
            [
                'name' => 'Brisbane Logistics Hub',
                'address' => '88 Riverside Drive',
                'city' => 'Brisbane',
                'state' => 'QLD',
                'postcode' => '4000',
                'phone' => '(07) 3567 8800',
                'email' => 'brisbane@whs4.com.au',
                'manager_name' => 'Jacob Williams',
                'is_active' => true,
            ]
        );

        $perth = Branch::firstOrCreate(
            ['code' => 'PER'],
            [
                'name' => 'Perth Regional Office',
                'address' => '12 Forrest Place',
                'city' => 'Perth',
                'state' => 'WA',
                'postcode' => '6000',
                'phone' => '(08) 9201 3322',
                'email' => 'perth@whs4.com.au',
                'manager_name' => 'Sienna Brooks',
                'is_active' => false,
            ]
        );

        // Create sample driver with active vehicle assignment for quick inspections
        $driver = User::firstOrCreate(
            ['email' => 'driver@whs4.com.au'],
            [
                'branch_id' => $sydney->id,
                'name' => 'Harper Collins',
                'password' => Hash::make('Driver@2025!'),
                'phone' => '0400 123 456',
                'employee_id' => 'DRV-1001',
                'position' => 'Fleet Driver',
                'is_active' => true,
            ]
        );
        $driver->assignRole('Employee');

        $vehicle = Vehicle::firstOrCreate(
            ['registration_number' => 'SYD-TRK-01'],
            [
                'branch_id' => $sydney->id,
                'make' => 'Toyota',
                'model' => 'Hilux',
                'year' => 2022,
                'color' => 'White',
                'odometer_reading' => 86450,
                'inspection_frequency' => 'monthly',
                'status' => 'active',
            ]
        );

        VehicleAssignment::firstOrCreate(
            [
                'vehicle_id' => $vehicle->id,
                'user_id' => $driver->id,
                'returned_date' => null,
            ],
            [
                'assigned_date' => now()->subWeeks(1)->format('Y-m-d'),
                'odometer_start' => 86000,
                'purpose' => 'Daily delivery route',
            ]
        );

        $forklift = Vehicle::firstOrCreate(
            ['registration_number' => 'SYD-FLT-01'],
            [
                'branch_id' => $sydney->id,
                'make' => 'Crown',
                'model' => 'SC 6000 Forklift',
                'year' => 2021,
                'color' => 'Safety Yellow',
                'odometer_reading' => 3120,
                'inspection_frequency' => 'daily',
                'status' => 'active',
            ]
        );

        VehicleAssignment::firstOrCreate(
            [
                'vehicle_id' => $forklift->id,
                'user_id' => $driver->id,
                'returned_date' => null,
            ],
            [
                'assigned_date' => now()->subDays(2)->format('Y-m-d'),
                'odometer_start' => 3100,
                'purpose' => 'Warehouse operations',
            ]
        );

        $this->command->info('');
        $this->command->info('WHS4 SaaS System Initialized!');
        $this->command->info('');
        $this->command->info('Summary:');
        $this->command->info('   - 3 Roles created (Admin, Manager, Employee)');
        $this->command->info('   - 7 Permissions configured');
        $this->command->info('   - 1 System Administrator created');
        $this->command->info('   - 3 Branch records seeded (Sydney, Brisbane, Perth)');
        $this->command->info('   - 1 Sample driver + vehicle assignment ready for inspections');
        $this->command->info('');
        $this->command->info('Login Credentials:');
        $this->command->info('   Email:    admin@whs4.com.au');
        $this->command->info('   Password: Admin@2025!');
        $this->command->info('   Driver:   driver@whs4.com.au / Driver@2025!');
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
