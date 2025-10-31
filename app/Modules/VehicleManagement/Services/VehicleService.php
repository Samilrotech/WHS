<?php

namespace App\Modules\VehicleManagement\Services;

use App\Modules\VehicleManagement\Models\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class VehicleService
{
    /**
     * Get paginated list of vehicles
     */
    public function getPaginated(array $filters = []): LengthAwarePaginator
    {
        $query = Vehicle::query()->with(['branch', 'currentAssignment.user', 'latestInspection']);

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('registration_number', 'like', "%{$search}%")
                  ->orWhere('registration_state', 'like', "%{$search}%")
                  ->orWhere('make', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('vin_number', 'like', "%{$search}%");
            });
        }

        // Branch filter
        if (!empty($filters['branch'])) {
            $query->where('branch_id', $filters['branch']);
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Make filter
        if (!empty($filters['make'])) {
            $query->where('make', $filters['make']);
        }

        // Assignment status filter
        if (isset($filters['assigned']) && $filters['assigned'] !== 'all') {
            if ($filters['assigned'] === 'yes') {
                $query->whereHas('currentAssignment');
            } else {
                $query->whereDoesntHave('currentAssignment');
            }
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Create a new vehicle
     */
    public function create(array $data): Vehicle
    {
        $vehicle = Vehicle::create($data);

        // Generate QR code for the vehicle (skip if package not installed)
        try {
            // Check if the SimpleSoftwareIO QR code package is installed
            if (class_exists('SimpleSoftwareIO\\QrCode\\Facades\\QrCode')) {
                $qrService = app(QRCodeService::class);
                $qrPath = $qrService->generateVehicleQRCode($vehicle);
                $vehicle->update(['qr_code_path' => $qrPath]);
            }
        } catch (\Exception $e) {
            // QR code generation failed, continue without it
            logger()->warning('QR code generation failed for vehicle ' . $vehicle->id . ': ' . $e->getMessage());
        }

        return $vehicle->fresh();
    }

    /**
     * Update a vehicle
     */
    public function update(Vehicle $vehicle, array $data): Vehicle
    {
        $vehicle->update($data);

        return $vehicle->fresh();
    }

    /**
     * Delete a vehicle
     */
    public function delete(Vehicle $vehicle): bool
    {
        // Delete QR code file if exists
        if ($vehicle->qr_code_path && file_exists(storage_path('app/public/' . $vehicle->qr_code_path))) {
            unlink(storage_path('app/public/' . $vehicle->qr_code_path));
        }

        return $vehicle->delete();
    }

    /**
     * Get vehicle statistics for a branch
     */
    public function getStatistics(?string $branchId = null): array
    {
        $query = Vehicle::query();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $totalVehicles = $query->count();
        $activeVehicles = (clone $query)->where('status', 'active')->count();
        $inMaintenance = (clone $query)->where('status', 'maintenance')->count();
        $assigned = (clone $query)->whereHas('currentAssignment')->count();
        $available = $activeVehicles - $assigned;

        // Alerts
        $regoExpiring = (clone $query)->where(function ($q) {
            $q->whereNotNull('rego_expiry_date')
              ->whereDate('rego_expiry_date', '<=', now()->addDays(30));
        })->count();

        $insuranceExpiring = (clone $query)->where(function ($q) {
            $q->whereNotNull('insurance_expiry_date')
              ->whereDate('insurance_expiry_date', '<=', now()->addDays(30));
        })->count();

        $inspectionDue = (clone $query)->where(function ($q) {
            $q->whereNotNull('inspection_due_date')
              ->whereDate('inspection_due_date', '<=', now()->addDays(7));
        })->count();

        return [
            'total' => $totalVehicles,
            'active' => $activeVehicles,
            'maintenance' => $inMaintenance,
            'assigned' => $assigned,
            'available' => $available,
            'rego_expiring' => $regoExpiring,
            'insurance_expiring' => $insuranceExpiring,
            'inspection_due' => $inspectionDue,
        ];
    }

    /**
     * Get vehicles with upcoming maintenance or compliance issues
     */
    public function getAlertsAndReminders(?string $branchId = null): Collection
    {
        $query = Vehicle::query()->with(['branch', 'currentAssignment.user', 'latestInspection']);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->where(function ($q) {
            $q->where(function ($qq) {
                $qq->whereNotNull('rego_expiry_date')
                   ->whereDate('rego_expiry_date', '<=', now()->addDays(30));
            })
            ->orWhere(function ($qq) {
                $qq->whereNotNull('insurance_expiry_date')
                   ->whereDate('insurance_expiry_date', '<=', now()->addDays(30));
            })
            ->orWhere(function ($qq) {
                $qq->whereNotNull('inspection_due_date')
                   ->whereDate('inspection_due_date', '<=', now()->addDays(7));
            });
        })->get();
    }

    /**
     * Get all unique makes from vehicles
     */
    public function getUniqueMakes(?string $branchId = null): array
    {
        $query = Vehicle::query();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->distinct()->pluck('make')->filter()->sort()->values()->toArray();
    }

    /**
     * Assign a vehicle to a user
     */
    public function assign(Vehicle $vehicle, string $userId, array $data): Vehicle
    {
        // Check if vehicle is already assigned
        if ($vehicle->isAssigned()) {
            throw new \Exception('Vehicle is already assigned to another user');
        }

        $vehicle->assignments()->create([
            'user_id' => $userId,
            'branch_id' => $data['branch_id'] ?? $vehicle->branch_id,
            'assigned_date' => $data['assigned_date'] ?? now(),
            'odometer_start' => $data['odometer_start'] ?? $vehicle->odometer_reading,
            'purpose' => $data['purpose'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        if (!empty($data['branch_id'])) {
            $vehicle->update(['branch_id' => $data['branch_id']]);
        }

        return $vehicle->fresh(['currentAssignment.user']);
    }

    /**
     * Return a vehicle from assignment
     */
    public function returnVehicle(Vehicle $vehicle, array $data): Vehicle
    {
        $currentAssignment = $vehicle->currentAssignment;

        if (!$currentAssignment) {
            throw new \Exception('Vehicle is not currently assigned');
        }

        $currentAssignment->update([
            'returned_date' => $data['returned_date'] ?? now(),
            'odometer_end' => $data['odometer_end'] ?? null,
            'notes' => $data['notes'] ?? $currentAssignment->notes,
        ]);

        // Update vehicle odometer if provided
        if (!empty($data['odometer_end'])) {
            $vehicle->update(['odometer_reading' => $data['odometer_end']]);
        }

        return $vehicle->fresh(['currentAssignment.user']);
    }
}
