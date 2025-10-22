<?php

namespace App\Modules\InspectionManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\InspectionManagement\Models\Inspection;
use App\Modules\InspectionManagement\Models\InspectionItem;
use App\Modules\InspectionManagement\Services\InspectionService;
use App\Modules\VehicleManagement\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    public function __construct(
        private InspectionService $inspectionService
    ) {}

    /**
     * Display listing of inspections with filters
     */
    public function index(Request $request)
    {
        $query = Inspection::with(['vehicle', 'inspector', 'approver'])
            ->latest('inspection_date');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by inspection type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('inspection_type', $request->type);
        }

        // Filter by overall result
        if ($request->has('result') && $request->result !== 'all') {
            $query->where('overall_result', $request->result);
        }

        // Filter by vehicle
        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        // Search by inspection number or vehicle registration
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('inspection_number', 'like', "%{$request->search}%")
                    ->orWhereHas('vehicle', function ($vq) use ($request) {
                        $vq->where('registration_number', 'like', "%{$request->search}%");
                    });
            });
        }

        $inspections = $query->paginate(15)->withQueryString();

        // Get statistics for dashboard cards
        $stats = $this->getInspectionStatistics();

        return view('content.inspections.index', [
            'inspections' => $inspections,
            'statistics' => $stats,
            'filters' => $request->only(['status', 'type', 'result', 'vehicle_id', 'search']),
        ]);
    }

    /**
     * Get inspection statistics for dashboard
     */
    protected function getInspectionStatistics(): array
    {
        return [
            'total' => Inspection::count(),
            'pending_approval' => Inspection::where('status', 'completed')->count(),
            'overdue_approval' => Inspection::overdue()->count(),
            'failed' => Inspection::failed()->count(),
            'with_critical_defects' => Inspection::withCriticalDefects()->count(),
            'in_progress' => Inspection::where('status', 'in_progress')->count(),
            'monthly' => Inspection::whereMonth('inspection_date', now()->month)->count(),
        ];
    }

    /**
     * Show form to create new inspection
     */
    public function create()
    {
        $vehicles = Vehicle::with('latestInspection')
            ->where('status', 'active')
            ->get()
            ->map(function ($vehicle) {
                return [
                    'id' => $vehicle->id,
                    'registration_number' => $vehicle->registration_number,
                    'make' => $vehicle->make,
                    'model' => $vehicle->model,
                    'year' => $vehicle->year,
                    'odometer_reading' => $vehicle->odometer_reading,
                    'inspection_due' => $vehicle->isInspectionDue(),
                    'last_inspection_date' => $vehicle->latestInspection?->inspection_date,
                ];
            });

        return view('content.inspections.create', [
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * Store newly created inspection
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|uuid|exists:vehicles,id',
            'inspection_type' => 'required|in:monthly_routine,pre_trip,post_incident,annual_compliance,maintenance_followup,random_spot_check',
            'inspection_date' => 'nullable|date',
            'odometer_reading' => 'nullable|integer|min:0',
            'location' => 'nullable|string|max:255',
        ]);

        $inspection = $this->inspectionService->createInspection($validated);

        return redirect()->route('inspections.show', $inspection->id)
            ->with('success', 'Inspection created successfully with ' . $inspection->total_items_checked . ' checklist items.');
    }

    /**
     * Display inspection details and checklist
     */
    public function show(Inspection $inspection)
    {
        $inspection->load([
            'vehicle',
            'inspector',
            'approver',
            'items' => function ($query) {
                $query->orderBy('sequence_order');
            },
        ]);

        // Group items by category for better display
        $itemsByCategory = $inspection->items->groupBy('item_category');

        return view('content.inspections.show', [
            'inspection' => $inspection,
            'itemsByCategory' => $itemsByCategory,
            'statistics' => [
                'completion_percentage' => $inspection->completion_percentage,
                'total_items' => $inspection->total_items_checked,
                'items_checked' => $inspection->items->whereNotIn('result', ['pending'])->count(),
                'items_passed' => $inspection->items_passed,
                'items_failed' => $inspection->items_failed,
                'critical_defects' => $inspection->critical_defects,
                'major_defects' => $inspection->major_defects,
                'minor_defects' => $inspection->minor_defects,
            ],
        ]);
    }

    /**
     * Show form to edit inspection
     */
    public function edit(Inspection $inspection)
    {
        if (!in_array($inspection->status, ['pending', 'in_progress'])) {
            abort(403, 'Cannot edit inspection in current status');
        }

        $inspection->load(['vehicle', 'items']);

        return view('content.inspections.edit', [
            'inspection' => $inspection,
        ]);
    }

    /**
     * Update inspection details
     */
    public function update(Request $request, Inspection $inspection): RedirectResponse
    {
        if (!in_array($inspection->status, ['pending', 'in_progress'])) {
            return back()->with('error', 'Cannot edit inspection in current status.');
        }

        $validated = $request->validate([
            'inspection_type' => 'sometimes|in:monthly_routine,pre_trip,post_incident,annual_compliance,maintenance_followup,random_spot_check',
            'inspection_date' => 'sometimes|date',
            'odometer_reading' => 'sometimes|integer|min:0',
            'location' => 'sometimes|string|max:255',
            'inspector_notes' => 'nullable|string',
        ]);

        $inspection->update($validated);

        return redirect()->route('inspections.show', $inspection->id)
            ->with('success', 'Inspection updated successfully.');
    }

    /**
     * Delete inspection
     */
    public function destroy(Inspection $inspection): RedirectResponse
    {
        if ($inspection->status === 'approved') {
            return back()->with('error', 'Cannot delete approved inspection.');
        }

        $inspection->delete();

        return redirect()->route('inspections.index')
            ->with('success', 'Inspection deleted successfully.');
    }

    /**
     * Start an inspection (change to in_progress)
     */
    public function start(Inspection $inspection): RedirectResponse
    {
        if ($inspection->status !== 'pending') {
            return back()->with('error', 'Only pending inspections can be started.');
        }

        $this->inspectionService->startInspection($inspection);

        return redirect()->route('inspections.show', $inspection->id)
            ->with('success', 'Inspection started. Begin checking items.');
    }

    /**
     * Update an inspection item result
     */
    public function updateItem(Request $request, Inspection $inspection, InspectionItem $item): RedirectResponse
    {
        if (!in_array($inspection->status, ['in_progress', 'pending'])) {
            return back()->with('error', 'Cannot update items in current inspection status.');
        }

        $validated = $request->validate([
            'result' => 'required|in:pass,fail,na,pending',
            'defect_severity' => 'nullable|in:none,minor,major,critical',
            'measurement_value' => 'nullable|string',
            'within_tolerance' => 'nullable|boolean',
            'defect_notes' => 'nullable|string',
            'repair_recommendation' => 'nullable|string',
            'urgency' => 'nullable|in:immediate,urgent,moderate,low',
            'photo_paths' => 'nullable|array',
        ]);

        $this->inspectionService->updateInspectionItem($item, $validated);

        return back()->with('success', 'Item updated successfully.');
    }

    /**
     * Complete an inspection
     */
    public function complete(Request $request, Inspection $inspection): RedirectResponse
    {
        if ($inspection->status !== 'in_progress') {
            return back()->with('error', 'Only in-progress inspections can be completed.');
        }

        // Check if all items are checked
        $uncheckedItems = $inspection->items()->where('result', 'pending')->count();
        if ($uncheckedItems > 0) {
            return back()->with('error', "Cannot complete inspection. {$uncheckedItems} items still pending.");
        }

        $validated = $request->validate([
            'inspector_notes' => 'nullable|string',
            'defects_summary' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'next_inspection_due' => 'nullable|date|after:today',
        ]);

        $this->inspectionService->completeInspection($inspection, $validated);

        return redirect()->route('inspections.show', $inspection->id)
            ->with('success', 'Inspection completed. Awaiting supervisor approval.');
    }

    /**
     * Approve an inspection
     */
    public function approve(Request $request, Inspection $inspection): RedirectResponse
    {
        if ($inspection->status !== 'completed') {
            return back()->with('error', 'Only completed inspections can be approved.');
        }

        $validated = $request->validate([
            'approval_notes' => 'nullable|string',
        ]);

        $this->inspectionService->approveInspection($inspection, $validated);

        return redirect()->route('inspections.show', $inspection->id)
            ->with('success', 'Inspection approved successfully.');
    }

    /**
     * Reject an inspection
     */
    public function reject(Request $request, Inspection $inspection): RedirectResponse
    {
        if ($inspection->status !== 'completed') {
            return back()->with('error', 'Only completed inspections can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $this->inspectionService->rejectInspection($inspection, $validated);

        return redirect()->route('inspections.show', $inspection->id)
            ->with('warning', 'Inspection rejected. Inspector must address issues and resubmit.');
    }

    /**
     * Mark an inspection item repair as completed
     */
    public function completeRepair(Request $request, Inspection $inspection, InspectionItem $item): RedirectResponse
    {
        if (!$item->repair_required || $item->repair_completed) {
            return back()->with('error', 'Item does not require repair or is already completed.');
        }

        $validated = $request->validate([
            'repair_cost' => 'nullable|numeric|min:0',
            'repair_notes' => 'nullable|string',
        ]);

        $this->inspectionService->completeRepair($item, $validated);

        return back()->with('success', 'Repair marked as completed.');
    }
}
