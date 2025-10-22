<?php

namespace App\Modules\WarehouseEquipment\Services;

use App\Modules\WarehouseEquipment\Models\WarehouseEquipment;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class WarehouseEquipmentService
{
    /**
     * Generate QR code for equipment
     */
    public function generateQrCode(WarehouseEquipment $equipment): string
    {
        $qrData = json_encode([
            'equipment_id' => $equipment->id,
            'equipment_code' => $equipment->equipment_code,
            'equipment_name' => $equipment->equipment_name,
        ]);

        $qrCode = QrCode::format('png')->size(300)->generate($qrData);
        $filename = 'qr-codes/equipment-' . $equipment->equipment_code . '.png';

        Storage::disk('public')->put($filename, $qrCode);

        return $filename;
    }

    /**
     * Schedule next inspection based on frequency
     */
    public function scheduleNextInspection(WarehouseEquipment $equipment): void
    {
        $equipment->update([
            'next_inspection_due' => now()->addDays($equipment->inspection_frequency_days),
        ]);
    }

    /**
     * Schedule next maintenance based on frequency
     */
    public function scheduleNextMaintenance(WarehouseEquipment $equipment): void
    {
        $equipment->update([
            'maintenance_due_date' => now()->addDays($equipment->maintenance_frequency_days),
        ]);
    }

    /**
     * Get equipment statistics
     */
    public function getStatistics(string $branchId): array
    {
        return [
            'total_equipment' => WarehouseEquipment::where('branch_id', $branchId)->count(),
            'available' => WarehouseEquipment::where('branch_id', $branchId)->where('status', 'available')->count(),
            'in_use' => WarehouseEquipment::where('branch_id', $branchId)->where('status', 'in_use')->count(),
            'maintenance' => WarehouseEquipment::where('branch_id', $branchId)->where('status', 'maintenance')->count(),
            'inspection_overdue' => WarehouseEquipment::where('branch_id', $branchId)->inspectionDue()->count(),
            'maintenance_due' => WarehouseEquipment::where('branch_id', $branchId)->maintenanceDue()->count(),
        ];
    }
}
