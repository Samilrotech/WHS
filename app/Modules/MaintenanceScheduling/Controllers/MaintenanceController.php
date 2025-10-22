<?php

namespace App\Modules\MaintenanceScheduling\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MaintenanceScheduling\Models\MaintenanceSchedule;
use App\Modules\MaintenanceScheduling\Models\MaintenanceLog;
use App\Modules\MaintenanceScheduling\Models\PartsInventory;
use App\Modules\MaintenanceScheduling\Services\MaintenanceService;
use App\Modules\VehicleManagement\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class MaintenanceController extends Controller
{
    public function __construct(
        private MaintenanceService $maintenanceService
    ) {}

    // ============================================================================
    // MAINTENANCE SCHEDULES
    // ============================================================================

    /**
     * Display maintenance schedules
     */
    public function index(Request $request)
    {
        $query = MaintenanceSchedule::with(['vehicle', 'creator'])
            ->latest('next_due_date');

        // Filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('type') && $request->type !== 'all') {
            $query->where('schedule_type', $request->type);
        }

        if ($request->has('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        if ($request->has('vehicle_id') && $request->vehicle_id !== 'all') {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('schedule_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $schedules = $query->paginate(20)->withQueryString();

        // Statistics
        $statistics = [
            'total' => MaintenanceSchedule::count(),
            'active' => MaintenanceSchedule::active()->count(),
            'overdue' => MaintenanceSchedule::overdue()->count(),
            'due_soon' => MaintenanceSchedule::dueSoon()->count(),
            'paused' => MaintenanceSchedule::where('status', 'paused')->count(),
            'preventive' => MaintenanceSchedule::preventive()->count(),
        ];

        $vehicles = Vehicle::select('id', 'registration_number', 'make', 'model')->get();

        // Get overdue schedules for alert banner
        $overdueSchedules = MaintenanceSchedule::overdue()
            ->with('vehicle')
            ->take(10)
            ->get();

        return view('content.MaintenanceScheduling.Index', compact('schedules', 'statistics', 'vehicles', 'overdueSchedules'));
    }

    /**
     * Show create schedule form
     */
    public function create()
    {
        $vehicles = Vehicle::select('id', 'registration_number', 'make', 'model', 'year')->get();

        return view('content.MaintenanceScheduling.Create', compact('vehicles'));
    }

    /**
     * Store new maintenance schedule
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|uuid|exists:vehicles,id',
            'schedule_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'schedule_type' => 'required|in:preventive,predictive,corrective,emergency',
            'recurrence_type' => 'required|in:once,daily,weekly,monthly,quarterly,semi_annual,annual,odometer_based,engine_hours',
            'recurrence_interval' => 'nullable|integer|min:1',
            'odometer_interval' => 'required_if:recurrence_type,odometer_based|nullable|integer|min:1',
            'engine_hours_interval' => 'required_if:recurrence_type,engine_hours|nullable|integer|min:1',
            'start_date' => 'required|date',
            'estimated_cost_per_service' => 'nullable|numeric|min:0',
            'preferred_vendor' => 'nullable|string|max:255',
            'vendor_contact' => 'nullable|string|max:255',
            'required_parts' => 'nullable|array',
            'auto_order_parts' => 'nullable|boolean',
            'reminder_days_before' => 'nullable|integer|min:1|max:90',
            'email_notifications' => 'nullable|boolean',
            'sms_notifications' => 'nullable|boolean',
            'priority' => 'required|in:low,medium,high,critical',
        ]);

        $schedule = $this->maintenanceService->createSchedule($validated);

        return redirect()->route('maintenance.schedules.show', $schedule->id)
            ->with('success', 'Maintenance schedule created successfully');
    }

    /**
     * Display schedule details
     */
    public function show(MaintenanceSchedule $schedule)
    {
        $schedule->load(['vehicle', 'creator', 'maintenanceLogs.performer']);

        $upcomingLogs = $schedule->maintenanceLogs()
            ->where('status', '!=', 'cancelled')
            ->latest('service_date')
            ->take(5)
            ->get();

        $costAnalysis = [
            'estimated_per_service' => $schedule->estimated_cost_per_service,
            'actual_average' => $schedule->average_cost_per_service,
            'total_spent' => $schedule->actual_total_cost,
            'variance' => $schedule->cost_variance,
            'variance_percentage' => $schedule->cost_variance_percentage,
        ];

        $roiAnalysis = $schedule->getMaintenanceROI();

        return view('content.Maintenance/Schedules.Show', compact('schedule', 'upcomingLogs', 'costAnalysis', 'roiAnalysis'));
    }

    /**
     * Show edit schedule form
     */
    public function edit(MaintenanceSchedule $schedule)
    {
        $schedule->load('vehicle');
        $vehicles = Vehicle::select('id', 'registration_number', 'make', 'model', 'year')->get();

        return view('content.MaintenanceScheduling.Edit', compact('schedule', 'vehicles'));
    }

    /**
     * Update schedule
     */
    public function update(Request $request, MaintenanceSchedule $schedule): RedirectResponse
    {
        $validated = $request->validate([
            'schedule_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'recurrence_interval' => 'nullable|integer|min:1',
            'estimated_cost_per_service' => 'nullable|numeric|min:0',
            'preferred_vendor' => 'nullable|string|max:255',
            'vendor_contact' => 'nullable|string|max:255',
            'reminder_days_before' => 'nullable|integer|min:1|max:90',
            'priority' => 'required|in:low,medium,high,critical',
        ]);

        $schedule->update($validated);

        return redirect()->route('maintenance.schedules.show', $schedule->id)
            ->with('success', 'Schedule updated successfully');
    }

    /**
     * Pause schedule
     */
    public function pause(Request $request, MaintenanceSchedule $schedule): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $this->maintenanceService->pauseSchedule($schedule, $validated['reason']);

        return back()->with('success', 'Schedule paused');
    }

    /**
     * Resume schedule
     */
    public function resume(MaintenanceSchedule $schedule): RedirectResponse
    {
        $this->maintenanceService->resumeSchedule($schedule);

        return back()->with('success', 'Schedule resumed');
    }

    /**
     * Delete schedule
     */
    public function destroy(MaintenanceSchedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return redirect()->route('maintenance.schedules.index')
            ->with('success', 'Schedule deleted successfully');
    }

    // ============================================================================
    // MAINTENANCE LOGS (WORK ORDERS)
    // ============================================================================

    /**
     * Display maintenance logs
     */
    public function logsIndex(Request $request)
    {
        $query = MaintenanceLog::with(['vehicle', 'performer', 'approver', 'maintenanceSchedule'])
            ->latest('service_date');

        // Filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('type') && $request->type !== 'all') {
            $query->where('maintenance_type', $request->type);
        }

        if ($request->has('vehicle_id') && $request->vehicle_id !== 'all') {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('work_order_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        // Statistics
        $statistics = $this->maintenanceService->getStatistics();

        $vehicles = Vehicle::select('id', 'registration_number', 'make', 'model')->get();

        return view('content.Maintenance/Logs.Index', compact('logs', 'statistics', 'vehicles', 'request'));
    }

    /**
     * Show create log form
     */
    public function logsCreate()
    {
        $vehicles = Vehicle::with('maintenanceSchedules')->get();
        $schedules = MaintenanceSchedule::active()->with('vehicle')->get();

        return view('content.Maintenance/Logs.Create', compact('vehicles', 'schedules'));
    }

    /**
     * Store new maintenance log
     */
    public function logsStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|uuid|exists:vehicles,id',
            'maintenance_schedule_id' => 'nullable|uuid|exists:maintenance_schedules,id',
            'description' => 'required|string',
            'maintenance_type' => 'required|in:scheduled,unscheduled,inspection_followup,emergency',
            'service_date' => 'required|date',
            'odometer_reading' => 'nullable|integer|min:0',
            'parts_cost' => 'nullable|numeric|min:0',
            'labor_cost' => 'nullable|numeric|min:0',
            'vendor_cost' => 'nullable|numeric|min:0',
            'vendor_name' => 'nullable|string|max:255',
            'parts_used' => 'nullable|array',
            'safety_critical' => 'nullable|boolean',
        ]);

        $log = $this->maintenanceService->createMaintenanceLog($validated);

        return redirect()->route('maintenance.logs.show', $log->id)
            ->with('success', 'Work order created successfully');
    }

    /**
     * Display log details
     */
    public function logsShow(MaintenanceLog $log)
    {
        $log->load(['vehicle', 'performer', 'approver', 'maintenanceSchedule', 'inspection']);

        return view('content.Maintenance/Logs.Show', compact('log'));
    }

    /**
     * Update log
     */
    public function logsUpdate(Request $request, MaintenanceLog $log): RedirectResponse
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'work_performed' => 'nullable|string',
            'parts_cost' => 'nullable|numeric|min:0',
            'labor_cost' => 'nullable|numeric|min:0',
            'vendor_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Recalculate total cost
        $validated['total_cost'] = ($validated['parts_cost'] ?? 0) +
                                   ($validated['labor_cost'] ?? 0) +
                                   ($validated['vendor_cost'] ?? 0);

        $log->update($validated);

        return back()->with('success', 'Work order updated successfully');
    }

    /**
     * Approve work order
     */
    public function logsApprove(MaintenanceLog $log): RedirectResponse
    {
        if (!$log->isPendingApproval()) {
            return back()->with('error', 'Only pending work orders can be approved');
        }

        $this->maintenanceService->approveMaintenanceLog($log);

        return back()->with('success', 'Work order approved');
    }

    /**
     * Complete work order
     */
    public function logsComplete(MaintenanceLog $log): RedirectResponse
    {
        if ($log->status !== 'in_progress') {
            return back()->with('error', 'Only in-progress work orders can be completed');
        }

        $this->maintenanceService->completeMaintenanceLog($log);

        return back()->with('success', 'Work order completed');
    }

    /**
     * Verify completed work
     */
    public function logsVerify(Request $request, MaintenanceLog $log): RedirectResponse
    {
        if ($log->status !== 'completed') {
            return back()->with('error', 'Only completed work orders can be verified');
        }

        $validated = $request->validate([
            'quality_rating' => 'required|in:excellent,good,satisfactory,poor',
            'verification_notes' => 'nullable|string|max:500',
        ]);

        $this->maintenanceService->verifyMaintenanceLog($log, $validated);

        return back()->with('success', 'Work order verified');
    }

    /**
     * Delete log
     */
    public function logsDestroy(MaintenanceLog $log): RedirectResponse
    {
        $log->delete();

        return redirect()->route('maintenance.logs.index')
            ->with('success', 'Work order deleted successfully');
    }

    // ============================================================================
    // PARTS INVENTORY
    // ============================================================================

    /**
     * Display parts inventory
     */
    public function partsIndex(Request $request)
    {
        $query = PartsInventory::query();

        // Filters
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('part_category', $request->category);
        }

        if ($request->has('status') && $request->status !== 'all') {
            if ($request->status === 'low_stock') {
                $query->lowStock();
            } elseif ($request->status === 'out_of_stock') {
                $query->outOfStock();
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('part_name', 'like', "%{$search}%")
                  ->orWhere('part_number', 'like', "%{$search}%");
            });
        }

        $parts = $query->paginate(20)->withQueryString();

        // Statistics
        $statistics = [
            'total_parts' => PartsInventory::count(),
            'low_stock' => PartsInventory::lowStock()->count(),
            'out_of_stock' => PartsInventory::outOfStock()->count(),
            'total_value' => PartsInventory::sum(DB::raw('quantity_on_hand * unit_cost')),
            'needs_reorder' => PartsInventory::needsReorder()->count(),
        ];

        return view('content.Maintenance/Parts.Index', compact('parts', 'statistics', 'request'));
    }

    /**
     * Store new part
     */
    public function partsStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'part_number' => 'required|string|unique:parts_inventory,part_number',
            'part_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'part_category' => 'required|in:filters,fluids,brakes,electrical,tires,belts_hoses,wipers,other',
            'quantity_on_hand' => 'required|integer|min:0',
            'reorder_point' => 'required|integer|min:0',
            'reorder_quantity' => 'required|integer|min:1',
            'unit_cost' => 'nullable|numeric|min:0',
            'supplier_name' => 'nullable|string|max:255',
        ]);

        $validated['branch_id'] = auth()->user()->branch_id;

        PartsInventory::create($validated);

        return back()->with('success', 'Part added to inventory');
    }

    /**
     * Update part
     */
    public function partsUpdate(Request $request, PartsInventory $part): RedirectResponse
    {
        $validated = $request->validate([
            'quantity_on_hand' => 'required|integer|min:0',
            'reorder_point' => 'required|integer|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
        ]);

        $part->update($validated);

        return back()->with('success', 'Part updated successfully');
    }
}
