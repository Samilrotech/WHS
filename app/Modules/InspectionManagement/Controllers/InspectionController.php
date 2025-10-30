<?php

namespace App\Modules\InspectionManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\InspectionManagement\Models\Inspection;
use App\Modules\InspectionManagement\Models\InspectionItem;
use App\Modules\InspectionManagement\Services\InspectionService;
use App\Modules\VehicleManagement\Models\Vehicle;
use App\Modules\VehicleManagement\Models\VehicleAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        if ($request->filled('branch')) {
            $query->where('branch_id', $request->branch);
        } elseif (auth()->check() && !auth()->user()->isAdmin()) {
            $query->where('branch_id', auth()->user()->branch_id);
        }

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
            'filters' => $request->only(['status', 'type', 'result', 'vehicle_id', 'search', 'branch']),
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
    public function create(Request $request)
    {
        $selectedVehicleId = $request->query('vehicle_id');
        $selectedInspectionType = $request->query('inspection_type');

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

        $selectedVehicle = null;
        if ($selectedVehicleId) {
            $selectedVehicle = $vehicles->firstWhere('id', $selectedVehicleId);
        }

        return view('content.inspections.create', [
            'vehicles' => $vehicles,
            'selectedVehicleId' => $selectedVehicleId,
            'selectedInspectionType' => $selectedInspectionType,
            'selectedVehicle' => $selectedVehicle,
        ]);
    }

    /**
     * Quick-create and immediately start a monthly inspection for a vehicle assignment.
     */
    public function startMonthly(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|uuid|exists:vehicles,id',
            'vehicle_assignment_id' => 'nullable|uuid|exists:vehicle_assignments,id',
        ]);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        // Resume existing open monthly inspection if one already exists.
        $existing = Inspection::where('vehicle_id', $vehicle->id)
            ->where('inspection_type', 'monthly_routine')
            ->whereIn('status', ['pending', 'in_progress'])
            ->latest()
            ->first();

        if ($existing) {
            if ($existing->status === 'pending') {
                $existing = $this->inspectionService->startInspection($existing);
            }

            return redirect()
                ->route('inspections.show', $existing->id)
                ->with('info', 'You already have a monthly inspection open for this vehicle. Resuming it now.');
        }

        $data = [
            'vehicle_id' => $vehicle->id,
            'inspection_type' => 'monthly_routine',
            'inspection_date' => now(),
            'odometer_reading' => $vehicle->odometer_reading,
        ];

        if (!empty($validated['vehicle_assignment_id'])) {
            $assignment = VehicleAssignment::whereKey($validated['vehicle_assignment_id'])
                ->where('user_id', auth()->id())
                ->whereNull('returned_date')
                ->first();

            if (!$assignment || $assignment->vehicle_id !== $vehicle->id) {
                return back()->with('error', 'That vehicle assignment is no longer active for you.');
            }

            $data['vehicle_assignment_id'] = $assignment->id;
        }

        $inspection = $this->inspectionService->createInspection($data);
        $inspection = $this->inspectionService->startInspection($inspection);

        return redirect()
            ->route('inspections.show', $inspection->id)
            ->with('success', 'Monthly inspection started. Begin checking items.');
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

        $inspection->items->transform(function ($item) use ($inspection) {
            $photos = collect($item->photo_paths ?? [])
                ->filter()
                ->map(function ($entry, $key) use ($inspection, $item) {
                    $label = is_string($key)
                        ? Str::title(str_replace('_', ' ', $key))
                        : 'Photo ' . ((int) $key + 1);

                    $value = $entry;
                    if (is_array($entry)) {
                        $value = $entry['path'] ?? $entry['url'] ?? '';
                    }

                    if (!is_string($value) || $value === '') {
                        return null;
                    }

                    if (Str::startsWith($value, ['http://', 'https://'])) {
                        return [
                            'label' => $label,
                            'url' => $value,
                            'download_url' => $value,
                            'remote' => true,
                        ];
                    }

                    $relative = Str::of($value)
                        ->replaceFirst('/storage/', '')
                        ->replaceFirst('storage/', '')
                        ->ltrim('/')
                        ->value();

                    if ($relative === '') {
                        return null;
                    }

                    if (!Storage::disk('public')->exists($relative)) {
                        return null;
                    }

                    $photoKey = is_string($key) ? $key : (string) $key;

                    return [
                        'label' => $label,
                        'url' => route('inspections.photos.show', [
                            'inspection' => $inspection->id,
                            'item' => $item->id,
                            'photo' => $photoKey,
                        ]),
                        'download_url' => route('inspections.photos.show', [
                            'inspection' => $inspection->id,
                            'item' => $item->id,
                            'photo' => $photoKey,
                            'download' => 1,
                        ]),
                        'remote' => false,
                    ];
                })
                ->filter()
                ->values()
                ->all();

            $item->photo_gallery = $photos;

            return $item;
        });

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

    /**
     * Serve an inspection photo for preview or download.
     */
    public function photo(Request $request, Inspection $inspection, InspectionItem $item, string $photo)
    {
        if ($item->inspection_id !== $inspection->id) {
            abort(404);
        }

        $photoPaths = $item->photo_paths ?? [];
        if (!is_array($photoPaths)) {
            $photoPaths = [];
        }

        $key = array_key_exists($photo, $photoPaths) ? $photo : null;
        if ($key === null && is_numeric($photo) && array_key_exists((int) $photo, $photoPaths)) {
            $key = (int) $photo;
        }

        if ($key === null) {
            abort(404);
        }

        $value = $photoPaths[$key];
        if (is_array($value)) {
            $value = $value['path'] ?? $value['url'] ?? '';
        }

        if (!is_string($value) || $value === '') {
            abort(404);
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return redirect()->away($value);
        }

        $relative = Str::of($value)
            ->replaceFirst('/storage/', '')
            ->replaceFirst('storage/', '')
            ->ltrim('/')
            ->value();

        if ($relative === '') {
            abort(404);
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($relative)) {
            abort(404);
        }

        $mime = $disk->mimeType($relative) ?: 'application/octet-stream';
        $download = $request->boolean('download');

        $baseName = basename($relative);
        $safeName = Str::slug($inspection->inspection_number . '-' . $item->item_name . '-' . $key);
        $extension = pathinfo($relative, PATHINFO_EXTENSION) ?: pathinfo($baseName, PATHINFO_EXTENSION);
        $downloadName = $safeName !== '' ? ($extension ? "{$safeName}.{$extension}" : $safeName) : $baseName;

        if ($download) {
            return $disk->download($relative, $downloadName, [
                'Content-Type' => $mime,
            ]);
        }

        return $disk->response($relative, $downloadName, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $downloadName . '"',
            'Cache-Control' => 'private, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

}
