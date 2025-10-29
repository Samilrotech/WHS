<?php

namespace App\Modules\InspectionManagement\Services;

use App\Modules\InspectionManagement\Models\Inspection;
use App\Modules\InspectionManagement\Models\InspectionItem;
use App\Modules\VehicleManagement\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class InspectionService
{
    /**
     * Generate unique inspection number
     * Format: INS-{YEAR}-{BRANCH}-{SEQUENCE}
     */
    protected function generateInspectionNumber(): string
    {
        $year = now()->year;
        $branchId = auth()->user()->branch_id;

        $lastInspection = Inspection::where('branch_id', $branchId)
            ->where('inspection_number', 'like', "INS-{$year}-%")
            ->orderBy('inspection_number', 'desc')
            ->first();

        if ($lastInspection) {
            $parts = explode('-', $lastInspection->inspection_number);
            $sequence = intval(end($parts)) + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('INS-%d-%04d', $year, $sequence);
    }

    /**
     * Create a new inspection with standard checklist items
     */
    public function createInspection(array $data): Inspection
    {
        return DB::transaction(function () use ($data) {
            $data['branch_id'] = auth()->user()->branch_id;
            $data['inspector_user_id'] = auth()->id();
            $data['inspection_number'] = $this->generateInspectionNumber();
            $data['status'] = 'pending';

            $inspection = Inspection::create($data);

            // Create standard checklist items based on inspection type
            $this->createStandardChecklistItems($inspection);

            return $inspection->load(['vehicle', 'inspector', 'items']);
        });
    }

    /**
     * Quick-create a driver inspection for the current assignment
     */
    public function createDriverVehicleInspection(array $data): Inspection
    {
        $template = collect($this->getDriverQuickChecklist());

        return DB::transaction(function () use ($data, $template) {
            $inspection = Inspection::create([
                'branch_id' => auth()->user()->branch_id,
                'vehicle_id' => $data['vehicle_id'],
                'vehicle_assignment_id' => $data['vehicle_assignment_id'] ?? null,
                'inspector_user_id' => auth()->id(),
                'inspection_number' => $this->generateInspectionNumber(),
                'inspection_type' => 'pre_trip',
                'inspection_date' => $data['inspection_date'] ?? now(),
                'odometer_reading' => $data['odometer_reading'] ?? null,
                'location' => $data['location'] ?? null,
                'status' => 'pending',
                'inspector_notes' => $data['inspector_notes'] ?? null,
            ]);

            foreach ($template as $index => $item) {
                $slug = $item['slug'];
                $result = $data['checks'][$slug] ?? 'pass';
                $result = in_array($result, ['pass', 'fail', 'na'], true) ? $result : 'pass';

                $defectSeverity = null;
                $repairRequired = false;
                $repairDueDate = null;

                if ($result === 'fail') {
                    $defectSeverity = $item['severity_on_fail'] ?? 'minor';
                    $repairRequired = true;
                    $repairDueDate = $this->calculateRepairDueDate($defectSeverity);
                }

                InspectionItem::create([
                    'inspection_id' => $inspection->id,
                    'item_category' => $item['category'],
                    'item_name' => $item['name'],
                    'item_description' => $item['description'] ?? null,
                    'sequence_order' => $index + 1,
                    'result' => $result,
                    'defect_severity' => $defectSeverity,
                    'defect_notes' => $data['notes'][$slug] ?? null,
                    'repair_required' => $repairRequired,
                    'repair_due_date' => $repairDueDate,
                    'urgency' => $repairRequired ? ($item['urgency'] ?? 'urgent') : null,
                    'safety_critical' => $item['safety_critical'] ?? false,
                    'compliance_item' => $item['compliance'] ?? false,
                    'compliance_standard' => $item['compliance_standard'] ?? null,
                ]);
            }

            $inspection->update(['total_items_checked' => $template->count()]);

            // Refresh stats and determine overall outcome
            $inspection->load('items');
            $this->updateInspectionStatistics($inspection);
            $inspection->refresh();

            $inspection->update([
                'status' => 'completed',
                'overall_result' => $this->determineOverallResult($inspection),
                'defects_summary' => $this->summarizeFailedItems($inspection),
                'recommendations' => $data['recommendations'] ?? null,
            ]);

            return $inspection->load(['vehicle', 'vehicleAssignment', 'inspector', 'items']);
        });
    }

    /**
     * Create standard checklist items for the inspection
     */
    protected function createStandardChecklistItems(Inspection $inspection): void
    {
        $standardItems = $this->getStandardChecklistTemplate();

        foreach ($standardItems as $index => $item) {
            InspectionItem::create([
                'inspection_id' => $inspection->id,
                'item_category' => $item['category'],
                'item_name' => $item['name'],
                'item_description' => $item['description'] ?? null,
                'sequence_order' => $index + 1,
                'result' => 'pending',
                'safety_critical' => $item['safety_critical'] ?? false,
                'compliance_item' => $item['compliance'] ?? false,
                'compliance_standard' => $item['compliance_standard'] ?? null,
            ]);
        }

        $inspection->update(['total_items_checked' => count($standardItems)]);
    }

    /**
     * Get standard checklist template
     */
    protected function getStandardChecklistTemplate(): array
    {
        return [
            // Engine checks
            ['category' => 'Engine', 'name' => 'Engine oil level', 'description' => 'Check oil level and quality', 'safety_critical' => true],
            ['category' => 'Engine', 'name' => 'Coolant level', 'description' => 'Check coolant level and condition', 'safety_critical' => true],
            ['category' => 'Engine', 'name' => 'Engine leaks', 'description' => 'Check for oil, coolant, or fuel leaks', 'safety_critical' => true],
            ['category' => 'Engine', 'name' => 'Air filter', 'description' => 'Check air filter condition'],
            ['category' => 'Engine', 'name' => 'Battery condition', 'description' => 'Check battery terminals and charge', 'safety_critical' => true],

            // Tires and wheels
            ['category' => 'Tires', 'name' => 'Front left tire tread depth', 'description' => 'Minimum 1.5mm legal requirement', 'safety_critical' => true, 'compliance' => true, 'compliance_standard' => 'ADR 42/05'],
            ['category' => 'Tires', 'name' => 'Front right tire tread depth', 'description' => 'Minimum 1.5mm legal requirement', 'safety_critical' => true, 'compliance' => true, 'compliance_standard' => 'ADR 42/05'],
            ['category' => 'Tires', 'name' => 'Rear left tire tread depth', 'description' => 'Minimum 1.5mm legal requirement', 'safety_critical' => true, 'compliance' => true, 'compliance_standard' => 'ADR 42/05'],
            ['category' => 'Tires', 'name' => 'Rear right tire tread depth', 'description' => 'Minimum 1.5mm legal requirement', 'safety_critical' => true, 'compliance' => true, 'compliance_standard' => 'ADR 42/05'],
            ['category' => 'Tires', 'name' => 'Tire pressure (all)', 'description' => 'Check tire pressure as per manufacturer specs', 'safety_critical' => true],
            ['category' => 'Tires', 'name' => 'Wheel nuts torque', 'description' => 'Check wheel nuts are properly tightened', 'safety_critical' => true],

            // Brakes
            ['category' => 'Brakes', 'name' => 'Brake fluid level', 'description' => 'Check brake fluid level and color', 'safety_critical' => true, 'compliance' => true],
            ['category' => 'Brakes', 'name' => 'Brake pad wear', 'description' => 'Check front and rear brake pad thickness', 'safety_critical' => true],
            ['category' => 'Brakes', 'name' => 'Brake performance', 'description' => 'Test brake operation and pedal feel', 'safety_critical' => true, 'compliance' => true],
            ['category' => 'Brakes', 'name' => 'Handbrake operation', 'description' => 'Test parking brake effectiveness', 'safety_critical' => true, 'compliance' => true],

            // Lights and signals
            ['category' => 'Lights', 'name' => 'Headlights (high beam)', 'description' => 'Check both headlights on high beam', 'safety_critical' => true, 'compliance' => true, 'compliance_standard' => 'ADR 13/00'],
            ['category' => 'Lights', 'name' => 'Headlights (low beam)', 'description' => 'Check both headlights on low beam', 'safety_critical' => true, 'compliance' => true, 'compliance_standard' => 'ADR 13/00'],
            ['category' => 'Lights', 'name' => 'Tail lights', 'description' => 'Check rear lights operational', 'safety_critical' => true, 'compliance' => true],
            ['category' => 'Lights', 'name' => 'Brake lights', 'description' => 'Check both brake lights operational', 'safety_critical' => true, 'compliance' => true],
            ['category' => 'Lights', 'name' => 'Turn signals', 'description' => 'Check all indicators flash properly', 'safety_critical' => true, 'compliance' => true],
            ['category' => 'Lights', 'name' => 'Reverse lights', 'description' => 'Check reverse lights operational', 'compliance' => true],
            ['category' => 'Lights', 'name' => 'Hazard lights', 'description' => 'Check hazard warning lights', 'safety_critical' => true, 'compliance' => true],

            // Steering and suspension
            ['category' => 'Steering', 'name' => 'Steering play', 'description' => 'Check for excessive steering wheel play', 'safety_critical' => true],
            ['category' => 'Steering', 'name' => 'Power steering fluid', 'description' => 'Check power steering fluid level'],
            ['category' => 'Steering', 'name' => 'Suspension noise', 'description' => 'Listen for unusual suspension noises'],
            ['category' => 'Steering', 'name' => 'Shock absorbers', 'description' => 'Check shock absorber condition', 'safety_critical' => true],

            // Interior and safety equipment
            ['category' => 'Interior', 'name' => 'Seatbelts', 'description' => 'Check all seatbelts for damage and operation', 'safety_critical' => true, 'compliance' => true],
            ['category' => 'Interior', 'name' => 'Horn', 'description' => 'Test horn operation', 'safety_critical' => true, 'compliance' => true],
            ['category' => 'Interior', 'name' => 'Windscreen wipers', 'description' => 'Check wiper blade condition and operation', 'safety_critical' => true, 'compliance' => true],
            ['category' => 'Interior', 'name' => 'Windscreen washer', 'description' => 'Check washer fluid and operation'],
            ['category' => 'Interior', 'name' => 'Mirrors', 'description' => 'Check all mirrors secure and adjustable', 'safety_critical' => true, 'compliance' => true],
            ['category' => 'Interior', 'name' => 'First aid kit', 'description' => 'Verify first aid kit present and in-date', 'compliance' => true],
            ['category' => 'Interior', 'name' => 'Fire extinguisher', 'description' => 'Check fire extinguisher present and in-date', 'safety_critical' => true, 'compliance' => true],
            ['category' => 'Interior', 'name' => 'Warning triangle', 'description' => 'Verify emergency warning triangle present', 'compliance' => true],

            // Exterior and body
            ['category' => 'Exterior', 'name' => 'Windscreen condition', 'description' => 'Check for cracks or chips in windscreen', 'safety_critical' => true, 'compliance' => true],
            ['category' => 'Exterior', 'name' => 'Body damage', 'description' => 'Note any body damage or rust'],
            ['category' => 'Exterior', 'name' => 'Doors and locks', 'description' => 'Check all doors open, close, and lock properly'],
            ['category' => 'Exterior', 'name' => 'Fuel cap', 'description' => 'Check fuel cap secure and seals properly'],
        ];
    }

    /**
     * Driver daily inspection checklist template
     */
    public function getDriverQuickChecklist(): array
    {
        return [
            [
                'slug' => 'tyres_wheels',
                'category' => 'Exterior',
                'name' => 'Tyres & Wheels',
                'description' => 'Tread depth, inflation, visible damage, wheel nuts secure',
                'severity_on_fail' => 'critical',
                'safety_critical' => true,
            ],
            [
                'slug' => 'lights_indicators',
                'category' => 'Exterior',
                'name' => 'Lights & Indicators',
                'description' => 'Headlights, brake lights, indicators operational',
                'severity_on_fail' => 'major',
                'safety_critical' => true,
            ],
            [
                'slug' => 'brakes_handbrake',
                'category' => 'Critical Systems',
                'name' => 'Brakes & Handbrake',
                'description' => 'Foot brake responsive, park brake holds on incline',
                'severity_on_fail' => 'critical',
                'safety_critical' => true,
            ],
            [
                'slug' => 'windscreen_wipers',
                'category' => 'Visibility',
                'name' => 'Windscreen & Wipers',
                'description' => 'Windscreen clear, wipers effective, washer fluid topped up',
                'severity_on_fail' => 'major',
            ],
            [
                'slug' => 'fluids_leaks',
                'category' => 'Under Bonnet',
                'name' => 'Fluid Levels & Leaks',
                'description' => 'No visible leaks, engine oil/coolant within range',
                'severity_on_fail' => 'major',
            ],
            [
                'slug' => 'seatbelts_restraints',
                'category' => 'Cabin',
                'name' => 'Seatbelts & Restraints',
                'description' => 'Belts latch securely, no fraying or damage',
                'severity_on_fail' => 'critical',
                'safety_critical' => true,
            ],
            [
                'slug' => 'horn_warning_devices',
                'category' => 'Cabin',
                'name' => 'Horn & Warning Devices',
                'description' => 'Horn, reversing alarm and other alerts working',
                'severity_on_fail' => 'minor',
            ],
            [
                'slug' => 'cleanliness_documents',
                'category' => 'Ready To Operate',
                'name' => 'Cab Clean & Documents',
                'description' => 'Cab tidy, logbook and emergency gear present',
                'severity_on_fail' => 'minor',
            ],
        ];
    }

    /**
     * Build a compact summary of failed items for quick driver inspections
     */
    protected function summarizeFailedItems(Inspection $inspection): ?string
    {
        $failedItems = $inspection->items
            ->where('result', 'fail')
            ->map(function ($item) {
                $note = trim((string) ($item->defect_notes ?? ''));
                return $note
                    ? "{$item->item_name}: {$note}"
                    : "{$item->item_name}: Issue reported";
            });

        if ($failedItems->isEmpty()) {
            return null;
        }

        return $failedItems->implode("\n");
    }

    /**
     * Start an inspection (change status to in_progress)
     */
    public function startInspection(Inspection $inspection): Inspection
    {
        $inspection->update([
            'status' => 'in_progress',
            'inspection_date' => now(),
        ]);

        return $inspection->fresh();
    }

    /**
     * Update an inspection item result
     */
    public function updateInspectionItem(InspectionItem $item, array $data): InspectionItem
    {
        // Determine defect severity if failed
        if ($data['result'] === 'fail' && !isset($data['defect_severity'])) {
            $data['defect_severity'] = $this->determinDefectSeverity($item, $data);
        }

        // Set repair requirement if failed
        if ($data['result'] === 'fail') {
            $data['repair_required'] = true;
            $data['repair_due_date'] = $this->calculateRepairDueDate($data['defect_severity'] ?? 'minor');
        }

        $item->update($data);

        // Recalculate inspection statistics
        $this->updateInspectionStatistics($item->inspection);

        return $item->fresh();
    }

    /**
     * Determine defect severity based on item and data
     */
    protected function determinDefectSeverity(InspectionItem $item, array $data): string
    {
        // If it's a safety critical item, default to major
        if ($item->safety_critical) {
            return 'major';
        }

        // If compliance item, default to major
        if ($item->compliance_item) {
            return 'major';
        }

        // Otherwise default to minor
        return 'minor';
    }

    /**
     * Calculate repair due date based on severity
     */
    protected function calculateRepairDueDate(string $severity): \Carbon\Carbon
    {
        return match ($severity) {
            'critical' => now(), // Immediate
            'major' => now()->addDay(), // 24 hours
            'minor' => now()->addWeek(), // 7 days
            default => now()->addMonth(), // 30 days
        };
    }

    /**
     * Update inspection statistics based on items
     */
    protected function updateInspectionStatistics(Inspection $inspection): void
    {
        $items = $inspection->items;

        $inspection->update([
            'items_passed' => $items->where('result', 'pass')->count(),
            'items_failed' => $items->where('result', 'fail')->count(),
            'critical_defects' => $items->where('defect_severity', 'critical')->count(),
            'major_defects' => $items->where('defect_severity', 'major')->count(),
            'minor_defects' => $items->where('defect_severity', 'minor')->count(),
        ]);
    }

    /**
     * Complete an inspection
     */
    public function completeInspection(Inspection $inspection, array $data): Inspection
    {
        // Determine overall result based on defects
        $overallResult = $this->determineOverallResult($inspection);

        $inspection->update([
            'status' => 'completed',
            'overall_result' => $overallResult,
            'inspector_notes' => $data['inspector_notes'] ?? null,
            'defects_summary' => $data['defects_summary'] ?? null,
            'recommendations' => $data['recommendations'] ?? null,
            'next_inspection_due' => $data['next_inspection_due'] ?? now()->addMonth(),
        ]);

        // Update vehicle inspection due date
        $inspection->vehicle->update([
            'inspection_due_date' => $inspection->next_inspection_due,
        ]);

        return $inspection->fresh();
    }

    /**
     * Determine overall inspection result
     */
    protected function determineOverallResult(Inspection $inspection): string
    {
        if ($inspection->critical_defects > 0) {
            return 'fail_critical';
        }

        if ($inspection->major_defects > 0) {
            return 'fail_major';
        }

        if ($inspection->minor_defects > 0) {
            return 'pass_minor';
        }

        return 'pass';
    }

    /**
     * Approve an inspection
     */
    public function approveInspection(Inspection $inspection, array $data): Inspection
    {
        $inspection->update([
            'status' => 'approved',
            'approved_by_user_id' => auth()->id(),
            'approved_date' => now(),
            'approval_notes' => $data['approval_notes'] ?? null,
            'compliance_verified' => true,
        ]);

        return $inspection->fresh();
    }

    /**
     * Reject an inspection
     */
    public function rejectInspection(Inspection $inspection, array $data): Inspection
    {
        $inspection->update([
            'status' => 'rejected',
            'rejection_reason' => $data['rejection_reason'],
        ]);

        return $inspection->fresh();
    }

    /**
     * Mark inspection item repair as completed
     */
    public function completeRepair(InspectionItem $item, array $data): InspectionItem
    {
        $item->update([
            'repair_completed' => true,
            'repaired_by_user_id' => auth()->id(),
            'repair_completion_date' => now(),
            'repair_cost' => $data['repair_cost'] ?? null,
            'repair_notes' => $data['repair_notes'] ?? null,
        ]);

        return $item->fresh();
    }
}
