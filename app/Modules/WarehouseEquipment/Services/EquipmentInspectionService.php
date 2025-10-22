<?php

namespace App\Modules\WarehouseEquipment\Services;

use App\Modules\WarehouseEquipment\Models\EquipmentInspection;
use App\Modules\WarehouseEquipment\Models\InspectionChecklistItem;
use App\Modules\WarehouseEquipment\Models\WarehouseEquipment;

class EquipmentInspectionService
{
    /**
     * Create inspection with standard checklist items
     */
    public function createInspection(WarehouseEquipment $equipment, array $data): EquipmentInspection
    {
        $inspection = EquipmentInspection::create([
            'branch_id' => $equipment->branch_id,
            'equipment_id' => $equipment->id,
            'inspector_user_id' => auth()->id(),
            'inspection_type' => $data['inspection_type'],
            'scheduled_date' => $data['scheduled_date'] ?? now(),
            'status' => 'scheduled',
        ]);

        // Create standard checklist items based on equipment type
        $this->createChecklistItems($inspection, $equipment->equipment_type);

        return $inspection;
    }

    /**
     * Create standard checklist items for equipment type
     */
    protected function createChecklistItems(EquipmentInspection $inspection, string $equipmentType): void
    {
        $standardChecks = $this->getStandardChecks($equipmentType);

        foreach ($standardChecks as $index => $check) {
            InspectionChecklistItem::create([
                'inspection_id' => $inspection->id,
                'sequence_order' => $index + 1,
                'item_code' => $check['code'],
                'category' => $check['category'],
                'question' => $check['question'],
                'item_type' => $check['type'] ?? 'checkbox',
                'is_critical' => $check['critical'] ?? false,
            ]);
        }
    }

    /**
     * Get standard inspection checks for equipment type
     */
    protected function getStandardChecks(string $equipmentType): array
    {
        $common = [
            ['code' => 'GEN-01', 'category' => 'General', 'question' => 'Is equipment clean and free from damage?', 'critical' => false],
            ['code' => 'SAF-01', 'category' => 'Safety', 'question' => 'Are all safety guards in place and functional?', 'critical' => true],
            ['code' => 'SAF-02', 'category' => 'Safety', 'question' => 'Are emergency stops accessible and functional?', 'critical' => true],
        ];

        $specific = match($equipmentType) {
            'forklift' => [
                ['code' => 'FLT-01', 'category' => 'Forklift', 'question' => 'Are forks straight and undamaged?', 'critical' => true],
                ['code' => 'FLT-02', 'category' => 'Forklift', 'question' => 'Is load backrest secure?', 'critical' => true],
                ['code' => 'FLT-03', 'category' => 'Forklift', 'question' => 'Are lights and horn operational?', 'critical' => false],
            ],
            'power_tools' => [
                ['code' => 'PWR-01', 'category' => 'Power Tools', 'question' => 'Is power cord undamaged?', 'critical' => true],
                ['code' => 'PWR-02', 'category' => 'Power Tools', 'question' => 'Are guards and shields in place?', 'critical' => true],
            ],
            default => []
        };

        return array_merge($common, $specific);
    }

    /**
     * Start an inspection
     */
    public function startInspection(EquipmentInspection $inspection): void
    {
        $inspection->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    /**
     * Complete an inspection
     */
    public function completeInspection(EquipmentInspection $inspection, array $data): void
    {
        $inspection->update([
            'status' => 'completed',
            'completed_at' => now(),
            'inspector_notes' => $data['inspector_notes'] ?? null,
        ]);

        $inspection->calculateScore();
    }

    /**
     * Approve an inspection
     */
    public function approveInspection(EquipmentInspection $inspection, array $data): void
    {
        $inspection->update([
            'status' => 'approved',
            'reviewer_user_id' => auth()->id(),
            'reviewed_at' => now(),
            'reviewer_comments' => $data['reviewer_comments'] ?? null,
        ]);

        // Update equipment last inspection date
        $inspection->equipment->update([
            'last_inspection_date' => now(),
        ]);
    }
}
