<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use App\Modules\InspectionManagement\Models\Inspection;
use App\Modules\InspectionManagement\Services\InspectionService;
use App\Modules\VehicleManagement\Models\Vehicle;
use App\Modules\VehicleManagement\Models\VehicleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverVehicleInspectionTest extends TestCase
{
    use RefreshDatabase;

    protected function createDriverWithVehicle(string $frequency = 'daily'): array
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'is_active' => true,
        ]);

        $vehicle = Vehicle::factory()
            ->for($branch)
            ->create([
                'branch_id' => $branch->id,
                'status' => 'active',
                'odometer_reading' => 125000,
                'inspection_frequency' => $frequency,
            ]);

        $assignment = VehicleAssignment::factory()
            ->for($vehicle)
            ->for($user)
            ->active()
            ->create([
                'assigned_date' => now()->subDay()->format('Y-m-d'),
                'odometer_start' => 120000,
            ]);

        return compact('branch', 'user', 'vehicle', 'assignment');
    }

    public function test_assigned_vehicle_overview_lists_vehicle_and_actions(): void
    {
        $setup = $this->createDriverWithVehicle();

        $response = $this->actingAs($setup['user'])
            ->get(route('driver.vehicle-inspections.index'));

        $response->assertOk();
        $response->assertSeeText($setup['vehicle']->registration_number);
        $response->assertSeeText('Start daily prestart');
        $response->assertSeeText('View vehicle');
    }

    public function test_driver_can_open_daily_form_for_assignment(): void
    {
        $setup = $this->createDriverWithVehicle();

        $response = $this->actingAs($setup['user'])
            ->get(route('driver.vehicle-inspections.create', $setup['assignment']->id));

        $response->assertOk();
        $response->assertSeeText('Rapid safety checklist');
    }

    public function test_driver_submission_creates_completed_inspection_with_items(): void
    {
        $setup = $this->createDriverWithVehicle();
        $service = app(InspectionService::class);
        $checklist = collect($service->getDriverQuickChecklist());

        $payload = [
            'vehicle_assignment_id' => $setup['assignment']->id,
            'odometer_reading' => 126500,
            'location' => 'Head office depot',
            'inspector_notes' => 'Vehicle ready for delivery run.',
            'checks' => $checklist->mapWithKeys(fn ($item) => [$item['slug'] => 'pass'])->toArray(),
            'notes' => [],
        ];

        // Flag a single defect to ensure severity handling
        $payload['checks']['tyres_wheels'] = 'fail';
        $payload['notes']['tyres_wheels'] = 'Front left tyre low pressure.';

        $response = $this->actingAs($setup['user'])
            ->post(route('driver.vehicle-inspections.store'), $payload);

        $response->assertRedirect(route('driver.vehicle-inspections.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('inspections', 1);
        $inspection = Inspection::with('items')->first();

        $this->assertNotNull($inspection);
        $this->assertSame($setup['vehicle']->id, $inspection->vehicle_id);
        $this->assertSame($setup['assignment']->id, $inspection->vehicle_assignment_id);
        $this->assertSame('completed', $inspection->status);
        $this->assertSame('fail_critical', $inspection->overall_result);
        $this->assertSame($payload['odometer_reading'], $inspection->odometer_reading);
        $this->assertSame($checklist->count(), $inspection->items()->count());

        $failedItem = $inspection->items()->where('item_name', 'Tyres & Wheels')->first();
        $this->assertNotNull($failedItem);
        $this->assertSame('fail', $failedItem->result);
        $this->assertSame('critical', $failedItem->defect_severity);
        $this->assertSame('Front left tyre low pressure.', $failedItem->defect_notes);

        $setup['vehicle']->refresh();
        $this->assertSame($payload['odometer_reading'], $setup['vehicle']->odometer_reading);
    }

    public function test_driver_cannot_submit_without_active_assignment(): void
    {
        $setup = $this->createDriverWithVehicle();

        // Close the assignment to simulate vehicle being returned
        $setup['assignment']->update(['returned_date' => now()->format('Y-m-d')]);

        $service = app(InspectionService::class);
        $checklist = collect($service->getDriverQuickChecklist());

        $payload = [
            'vehicle_assignment_id' => $setup['assignment']->id,
            'checks' => $checklist->mapWithKeys(fn ($item) => [$item['slug'] => 'pass'])->toArray(),
        ];

        $response = $this->actingAs($setup['user'])
            ->from(route('driver.vehicle-inspections.index'))
            ->post(route('driver.vehicle-inspections.store'), $payload);

        $response->assertRedirect(route('driver.vehicle-inspections.index'));
        $response->assertSessionHasErrors('vehicle_assignment_id');

        $this->assertDatabaseCount('inspections', 0);
    }

    public function test_page_prompts_for_assignment_when_none_exists(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id]);

        $response = $this->actingAs($user)
            ->get(route('driver.vehicle-inspections.index'));

        $response->assertOk();
        $response->assertSeeText('No vehicle assigned');
    }
}
