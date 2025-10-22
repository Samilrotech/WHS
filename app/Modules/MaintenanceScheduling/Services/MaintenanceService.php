<?php

namespace App\Modules\MaintenanceScheduling\Services;

use App\Modules\MaintenanceScheduling\Models\MaintenanceSchedule;
use App\Modules\MaintenanceScheduling\Models\MaintenanceLog;
use App\Modules\MaintenanceScheduling\Models\PartsInventory;
use Illuminate\Support\Facades\DB;

class MaintenanceService
{
    /**
     * Create a new maintenance schedule
     */
    public function createSchedule(array $data): MaintenanceSchedule
    {
        $data['created_by_user_id'] = auth()->id();
        $data['branch_id'] = auth()->user()->branch_id;

        // Calculate initial next_due_date
        $data['next_due_date'] = $data['start_date'];

        return MaintenanceSchedule::create($data);
    }

    /**
     * Create a maintenance log (work order)
     */
    public function createMaintenanceLog(array $data): MaintenanceLog
    {
        DB::beginTransaction();

        try {
            $data['performed_by_user_id'] = auth()->id();
            $data['branch_id'] = auth()->user()->branch_id;
            $data['work_order_number'] = $this->generateWorkOrderNumber();

            // Calculate total cost
            $data['total_cost'] = ($data['parts_cost'] ?? 0) +
                                 ($data['labor_cost'] ?? 0) +
                                 ($data['vendor_cost'] ?? 0);

            // Calculate downtime if both timestamps provided
            if (isset($data['vehicle_out_of_service_at']) && isset($data['vehicle_back_in_service_at'])) {
                $outOfService = \Carbon\Carbon::parse($data['vehicle_out_of_service_at']);
                $backInService = \Carbon\Carbon::parse($data['vehicle_back_in_service_at']);
                $data['downtime_hours'] = $outOfService->diffInHours($backInService);
            }

            $log = MaintenanceLog::create($data);

            // If parts were used, update inventory
            if (!empty($data['parts_used'])) {
                $this->consumePartsFromInventory($data['parts_used']);
            }

            DB::commit();

            return $log;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Complete a maintenance log and update schedule
     */
    public function completeMaintenanceLog(MaintenanceLog $log): MaintenanceLog
    {
        DB::beginTransaction();

        try {
            $log->update([
                'status' => 'completed',
            ]);

            // If this was from a schedule, update the schedule
            if ($log->maintenance_schedule_id) {
                $this->updateScheduleAfterMaintenance($log);
            }

            DB::commit();

            return $log->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Approve a maintenance log
     */
    public function approveMaintenanceLog(MaintenanceLog $log): MaintenanceLog
    {
        $log->update([
            'status' => 'approved',
            'approved_by_user_id' => auth()->id(),
        ]);

        return $log->fresh();
    }

    /**
     * Verify completed maintenance (quality check)
     */
    public function verifyMaintenanceLog(MaintenanceLog $log, array $data): MaintenanceLog
    {
        $log->update([
            'status' => 'verified',
            'quality_rating' => $data['quality_rating'] ?? null,
            'notes' => ($log->notes ?? '') . "\n\nVerification: " . ($data['verification_notes'] ?? ''),
        ]);

        return $log->fresh();
    }

    /**
     * Update maintenance schedule after completion
     */
    protected function updateScheduleAfterMaintenance(MaintenanceLog $log): void
    {
        $schedule = $log->maintenanceSchedule;

        if (!$schedule) {
            return;
        }

        // Update completion count and date
        $schedule->completed_count += 1;
        $schedule->last_completed_date = $log->service_date;
        $schedule->actual_total_cost += $log->total_cost;

        // Calculate next due date based on recurrence type
        if ($schedule->isTimeBased()) {
            $schedule->next_due_date = $schedule->getNextRecurrenceDate();
        } elseif ($schedule->isOdometerBased()) {
            // For odometer-based, we don't auto-update the date
            // It should be triggered when vehicle reaches target odometer
            $schedule->next_due_date = null;
        }

        $schedule->save();
    }

    /**
     * Check all schedules and identify due/overdue items
     */
    public function checkDueSchedules(): array
    {
        $overdue = MaintenanceSchedule::overdue()->get();
        $dueSoon = MaintenanceSchedule::dueSoon()->get();

        return [
            'overdue' => $overdue,
            'due_soon' => $dueSoon,
            'total_alerts' => $overdue->count() + $dueSoon->count(),
        ];
    }

    /**
     * Check odometer-based schedules for a vehicle
     */
    public function checkOdometerSchedules(string $vehicleId, int $currentOdometer): array
    {
        $schedules = MaintenanceSchedule::active()
            ->byVehicle($vehicleId)
            ->where('recurrence_type', 'odometer_based')
            ->get();

        $dueSchedules = [];

        foreach ($schedules as $schedule) {
            if (!$schedule->last_completed_date) {
                // First service - check if we've reached the interval
                $startOdometer = $schedule->vehicle->odometer_reading ?? 0;
                if ($currentOdometer >= $startOdometer + $schedule->odometer_interval) {
                    $dueSchedules[] = $schedule;
                }
            } else {
                // Subsequent services - check from last completion
                $lastLog = MaintenanceLog::where('maintenance_schedule_id', $schedule->id)
                    ->latest('service_date')
                    ->first();

                if ($lastLog) {
                    $lastOdometer = $lastLog->odometer_reading ?? 0;
                    if ($currentOdometer >= $lastOdometer + $schedule->odometer_interval) {
                        $dueSchedules[] = $schedule;
                    }
                }
            }
        }

        return $dueSchedules;
    }

    /**
     * Generate unique work order number
     */
    protected function generateWorkOrderNumber(): string
    {
        $year = now()->year;
        $branchId = auth()->user()->branch_id;

        $lastLog = MaintenanceLog::where('branch_id', $branchId)
            ->where('work_order_number', 'like', "WO-{$year}-%")
            ->latest('created_at')
            ->first();

        if ($lastLog) {
            $lastNumber = (int) substr($lastLog->work_order_number, -4);
            $sequence = $lastNumber + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('WO-%d-%04d', $year, $sequence);
    }

    /**
     * Consume parts from inventory
     */
    protected function consumePartsFromInventory(array $partsUsed): void
    {
        foreach ($partsUsed as $part) {
            if (isset($part['part_inventory_id'])) {
                $inventoryPart = PartsInventory::find($part['part_inventory_id']);

                if ($inventoryPart) {
                    $inventoryPart->consumeStock($part['quantity']);
                }
            }
        }
    }

    /**
     * Calculate Total Cost of Ownership (TCO) for a vehicle
     */
    public function calculateTCO(string $vehicleId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = MaintenanceLog::byVehicle($vehicleId)
            ->completed();

        if ($startDate && $endDate) {
            $query->byDateRange($startDate, $endDate);
        }

        $logs = $query->get();

        $totalCost = $logs->sum('total_cost');
        $partsCost = $logs->sum('parts_cost');
        $laborCost = $logs->sum('labor_cost');
        $vendorCost = $logs->sum('vendor_cost');
        $totalDowntime = $logs->sum('downtime_hours');

        $scheduledCount = $logs->where('maintenance_type', 'scheduled')->count();
        $unscheduledCount = $logs->where('maintenance_type', 'unscheduled')->count();

        return [
            'total_cost' => round($totalCost, 2),
            'parts_cost' => round($partsCost, 2),
            'labor_cost' => round($laborCost, 2),
            'vendor_cost' => round($vendorCost, 2),
            'total_downtime_hours' => $totalDowntime,
            'maintenance_count' => $logs->count(),
            'scheduled_maintenance' => $scheduledCount,
            'unscheduled_maintenance' => $unscheduledCount,
            'average_cost_per_service' => $logs->count() > 0 ? round($totalCost / $logs->count(), 2) : 0,
            'preventive_ratio' => $logs->count() > 0 ? round(($scheduledCount / $logs->count()) * 100, 2) : 0,
        ];
    }

    /**
     * Get maintenance statistics for dashboard
     */
    public function getStatistics(): array
    {
        $branchId = auth()->user()->branch_id;

        return [
            'total_schedules' => MaintenanceSchedule::where('branch_id', $branchId)->count(),
            'active_schedules' => MaintenanceSchedule::active()->count(),
            'overdue_schedules' => MaintenanceSchedule::overdue()->count(),
            'due_soon_schedules' => MaintenanceSchedule::dueSoon()->count(),
            'total_work_orders' => MaintenanceLog::where('branch_id', $branchId)->count(),
            'pending_approval' => MaintenanceLog::pending()->count(),
            'pending_overdue' => MaintenanceLog::overdue()->count(),
            'completed_this_month' => MaintenanceLog::completed()
                ->whereMonth('service_date', now()->month)
                ->count(),
            'total_cost_this_month' => MaintenanceLog::completed()
                ->whereMonth('service_date', now()->month)
                ->sum('total_cost'),
            'low_stock_parts' => PartsInventory::lowStock()->count(),
            'out_of_stock_parts' => PartsInventory::outOfStock()->count(),
        ];
    }

    /**
     * Create maintenance log from inspection defect
     */
    public function createLogFromInspection(string $inspectionId, array $defectItems): MaintenanceLog
    {
        $inspection = \App\Modules\InspectionManagement\Models\Inspection::findOrFail($inspectionId);

        $description = "Corrective maintenance based on inspection {$inspection->inspection_number}";

        $defectDetails = collect($defectItems)->map(function ($item) {
            return "- {$item['item_name']}: {$item['defect_severity']} defect";
        })->join("\n");

        $data = [
            'vehicle_id' => $inspection->vehicle_id,
            'inspection_id' => $inspectionId,
            'description' => $description,
            'work_performed' => $defectDetails,
            'maintenance_type' => 'inspection_followup',
            'service_date' => now()->toDateString(),
            'safety_critical' => collect($defectItems)->contains('safety_critical', true),
            'status' => 'pending',
        ];

        return $this->createMaintenanceLog($data);
    }

    /**
     * Pause a maintenance schedule
     */
    public function pauseSchedule(MaintenanceSchedule $schedule, string $reason): MaintenanceSchedule
    {
        $schedule->update([
            'status' => 'paused',
            'notes' => ($schedule->notes ?? '') . "\n\nPaused: {$reason}",
        ]);

        return $schedule->fresh();
    }

    /**
     * Resume a paused schedule
     */
    public function resumeSchedule(MaintenanceSchedule $schedule): MaintenanceSchedule
    {
        $schedule->update([
            'status' => 'active',
        ]);

        return $schedule->fresh();
    }
}
