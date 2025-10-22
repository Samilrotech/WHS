<?php

namespace App\Modules\WarehouseEquipment\Services;

use App\Modules\WarehouseEquipment\Models\ToolCustodyLog;
use App\Modules\WarehouseEquipment\Models\WarehouseEquipment;

class ToolCustodyService
{
    /**
     * Check out equipment to a custodian
     */
    public function checkoutEquipment(WarehouseEquipment $equipment, array $data): ToolCustodyLog
    {
        // Verify equipment is available
        if ($equipment->status !== 'available') {
            throw new \Exception('Equipment is not available for checkout');
        }

        // Check license requirement
        if ($equipment->requires_license && ! $this->hasRequiredLicense($data['custodian_user_id'], $equipment->license_type)) {
            throw new \Exception('Custodian does not have required license: ' . $equipment->license_type);
        }

        $custodyLog = ToolCustodyLog::create([
            'branch_id' => $equipment->branch_id,
            'equipment_id' => $equipment->id,
            'custodian_user_id' => $data['custodian_user_id'],
            'checked_out_at' => now(),
            'expected_return_date' => $data['expected_return_date'],
            'purpose' => $data['purpose'] ?? null,
            'checkout_notes' => $data['checkout_notes'] ?? null,
            'condition_on_checkout' => $data['condition_on_checkout'] ?? 'good',
            'status' => 'checked_out',
        ]);

        // Update equipment status
        $equipment->update(['status' => 'in_use']);

        return $custodyLog;
    }

    /**
     * Check if user has required license (simplified check)
     */
    protected function hasRequiredLicense(string $userId, ?string $licenseType): bool
    {
        // In production, this would check against certifications table
        // For now, simplified implementation
        return true;
    }

    /**
     * Check in equipment
     */
    public function checkinEquipment(ToolCustodyLog $custodyLog, array $data): void
    {
        $damaged = isset($data['damage_reported']) && $data['damage_reported'];

        $custodyLog->checkIn(
            $data['condition_on_checkin'],
            $data['checkin_notes'] ?? null,
            $damaged,
            $data['damage_description'] ?? null
        );

        // If damaged, update equipment status to maintenance
        if ($damaged) {
            $custodyLog->equipment->update(['status' => 'maintenance']);
        }
    }

    /**
     * Update overdue status for all checked out equipment
     */
    public function updateOverdueStatus(): void
    {
        ToolCustodyLog::checkedOut()->each(function ($log) {
            $log->updateOverdueStatus();
        });
    }

    /**
     * Get custody statistics
     */
    public function getStatistics(string $branchId): array
    {
        return [
            'checked_out' => ToolCustodyLog::where('branch_id', $branchId)->checkedOut()->count(),
            'overdue' => ToolCustodyLog::where('branch_id', $branchId)->overdue()->count(),
            'total_logs' => ToolCustodyLog::where('branch_id', $branchId)->count(),
            'average_checkout_days' => ToolCustodyLog::where('branch_id', $branchId)
                ->whereNotNull('checked_in_at')
                ->get()
                ->avg('checkout_duration') ?? 0,
        ];
    }
}
