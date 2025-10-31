<?php

namespace App\Modules\VehicleManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\InspectionManagement\Models\Inspection;
use App\Models\Branch;
use App\Models\User;
use App\Modules\VehicleManagement\Models\Vehicle;
use App\Modules\VehicleManagement\Services\VehicleService;
use App\Modules\VehicleManagement\Services\QRCodeService;
use App\Modules\VehicleManagement\Services\DepreciationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function __construct(
        private VehicleService $vehicleService,
        private QRCodeService $qrCodeService,
        private DepreciationService $depreciationService
    ) {}

    /**
     * Display a listing of vehicles
     */
    public function index(Request $request)
    {
        $branchFilter = $request->input('branch');
        $user = $request->user();
        if (!$branchFilter && !$user->isAdmin()) {
            $branchFilter = $user->branch_id;
        }

        $vehicles = $this->vehicleService->getPaginated(array_merge($request->all(), ['branch' => $branchFilter]));
        $statistics = $this->vehicleService->getStatistics($branchFilter);
        $makes = $this->vehicleService->getUniqueMakes($branchFilter);
        $branches = Branch::active()->orderBy('name')->get(['id', 'name']);

        return view('content.vehicles.index', [
            'vehicles' => [
                'data' => $vehicles->items(),
                'current_page' => $vehicles->currentPage(),
                'last_page' => $vehicles->lastPage(),
                'per_page' => $vehicles->perPage(),
                'total' => $vehicles->total(),
            ],
            'statistics' => $statistics,
            'makes' => $makes,
            'branches' => $branches,
            'filters' => $request->only(['search', 'status', 'make', 'assigned', 'branch']) + ['branch' => $branchFilter],
            'mobileNavActive' => 'vehicles',
        ]);
    }

    /**
     * Show the form for creating a new vehicle
     */
    public function create()
    {
        return view('content.vehicles.create', [
            'registrationStates' => config('vehicles.registration_states'),
            'statuses' => [
                'active' => 'Active',
                'maintenance' => 'In Maintenance',
                'inactive' => 'Inactive',
                'sold' => 'Sold',
            ],
            'depreciationMethods' => [
                'straight_line' => 'Straight Line',
                'declining_balance' => 'Declining Balance',
            ],
            'inspectionFrequencies' => [
                'monthly' => 'Monthly (standard fleet)',
                'daily' => 'Daily prestart required',
            ],
        ]);
    }

    /**
     * Store a newly created vehicle
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'registration_number' => 'required|string|unique:vehicles',
            'registration_state' => ['nullable', Rule::in(array_keys(config('vehicles.registration_states')))],
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'vin_number' => 'nullable|string|unique:vehicles',
            'color' => 'nullable|string|max:255',
            'fuel_type' => 'nullable|string|max:255',
            'odometer_reading' => 'nullable|integer|min:0',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'depreciation_method' => 'nullable|in:straight_line,declining_balance',
            'depreciation_rate' => 'nullable|numeric|min:0|max:100',
            'insurance_company' => 'nullable|string|max:255',
            'insurance_policy_number' => 'nullable|string|max:255',
            'insurance_expiry_date' => 'nullable|date',
            'insurance_premium' => 'nullable|numeric|min:0',
            'rego_expiry_date' => 'nullable|date',
            'inspection_due_date' => 'nullable|date',
            'inspection_frequency' => 'required|in:monthly,daily',
            'status' => 'required|in:active,maintenance,inactive,sold',
            'notes' => 'nullable|string',
        ]);

        $vehicle = $this->vehicleService->create($validated);

        // Calculate initial depreciated value
        if ($vehicle->purchase_price && $vehicle->purchase_date) {
            $this->depreciationService->updateCurrentValue($vehicle);
        }

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Vehicle created successfully');
    }

    /**
     * Display the specified vehicle
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle->load([
            'branch',
            'serviceRecords' => fn($q) => $q->latest('service_date')->limit(10),
            'assignments' => fn($q) => $q->latest('assigned_date')->limit(10),
            'assignments.user',
            'currentAssignment.user'
        ]);

        // Calculate depreciation details
        $depreciationSchedule = $this->depreciationService->getDepreciationSchedule($vehicle);
        $totalDepreciation = $this->depreciationService->calculateTotalDepreciation($vehicle);

        // Calculate service cost statistics
        $serviceStats = [
            'total_cost' => $vehicle->serviceRecords->sum('cost'),
            'count' => $vehicle->serviceRecords->count(),
            'average_cost' => $vehicle->serviceRecords->avg('cost') ?? 0,
            'last_service' => $vehicle->serviceRecords->first()?->service_date,
        ];

        $recentInspections = $vehicle->inspections()
            ->latest('inspection_date')
            ->with(['inspector'])
            ->limit(5)
            ->get();

        $latestInspection = $recentInspections->first();

        $inspectionStats = [
            'total' => Inspection::where('vehicle_id', $vehicle->id)->count(),
            'failed' => Inspection::where('vehicle_id', $vehicle->id)->failed()->count(),
            'critical' => Inspection::where('vehicle_id', $vehicle->id)->withCriticalDefects()->count(),
            'pending' => Inspection::where('vehicle_id', $vehicle->id)->where('status', 'pending')->count(),
            'passed' => Inspection::where('vehicle_id', $vehicle->id)->whereIn('overall_result', ['pass', 'pass_minor'])->count(),
        ];

        $currentAssignment = $vehicle->currentAssignment;
        $canStartDriverInspection = $currentAssignment && auth()->id() === $currentAssignment->user_id;

        $assignableBranches = Branch::orderBy('name')->get(['id', 'name']);
        $assignableUsers = User::with('branch')->orderBy('name')->get();

        return view('content.vehicles.show', [
            'vehicle' => $vehicle,
            'depreciation_schedule' => $depreciationSchedule,
            'total_depreciation' => $totalDepreciation,
            'service_stats' => $serviceStats,
            'latest_inspection' => $latestInspection,
            'recent_inspections' => $recentInspections,
            'inspection_stats' => $inspectionStats,
            'can_start_driver_inspection' => $canStartDriverInspection,
            'driver_inspection_url' => ($canStartDriverInspection && $currentAssignment)
                ? route('driver.vehicle-inspections.create', ['assignment' => $currentAssignment->id])
                : null,
            'qr_code_url' => $vehicle->qr_code_path
                ? $this->qrCodeService->getQRCodeUrl($vehicle->qr_code_path)
                : null,
            'assignable_branches' => $assignableBranches,
            'assignable_users' => $assignableUsers,
        ]);
    }

    /**
     * Show the form for editing the vehicle
     */
    public function edit(Vehicle $vehicle)
    {
        return view('content.vehicles.edit', [
            'vehicle' => $vehicle,
            'registrationStates' => config('vehicles.registration_states'),
            'statuses' => [
                'active' => 'Active',
                'maintenance' => 'In Maintenance',
                'inactive' => 'Inactive',
                'sold' => 'Sold',
            ],
            'depreciationMethods' => [
                'straight_line' => 'Straight Line',
                'declining_balance' => 'Declining Balance',
            ],
            'inspectionFrequencies' => [
                'monthly' => 'Monthly (standard fleet)',
                'daily' => 'Daily prestart required',
            ],
        ]);
    }

    /**
     * Update the specified vehicle
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'registration_number' => 'required|string|unique:vehicles,registration_number,' . $vehicle->id,
            'registration_state' => ['nullable', Rule::in(array_keys(config('vehicles.registration_states')))],
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'vin_number' => 'nullable|string|unique:vehicles,vin_number,' . $vehicle->id,
            'color' => 'nullable|string|max:255',
            'fuel_type' => 'nullable|string|max:255',
            'odometer_reading' => 'nullable|integer|min:0',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'depreciation_method' => 'nullable|in:straight_line,declining_balance',
            'depreciation_rate' => 'nullable|numeric|min:0|max:100',
            'insurance_company' => 'nullable|string|max:255',
            'insurance_policy_number' => 'nullable|string|max:255',
            'insurance_expiry_date' => 'nullable|date',
            'insurance_premium' => 'nullable|numeric|min:0',
            'rego_expiry_date' => 'nullable|date',
            'inspection_due_date' => 'nullable|date',
            'inspection_frequency' => 'required|in:monthly,daily',
            'status' => 'required|in:active,maintenance,inactive,sold',
            'notes' => 'nullable|string',
        ]);

        $vehicle = $this->vehicleService->update($vehicle, $validated);

        // Recalculate depreciated value
        if ($vehicle->purchase_price && $vehicle->purchase_date) {
            $this->depreciationService->updateCurrentValue($vehicle);
        }

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', 'Vehicle updated successfully');
    }

    /**
     * Remove the specified vehicle
     */
    public function destroy(Vehicle $vehicle)
    {
        $this->vehicleService->delete($vehicle);

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehicle deleted successfully');
    }

    /**
     * Assign vehicle to a user
     */
    public function assign(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'branch_id' => 'required|uuid|exists:branches,id',
            'user_id' => 'required|exists:users,id',
            'assigned_date' => 'nullable|date',
            'odometer_start' => 'nullable|integer|min:0',
            'purpose' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $vehicle = $this->vehicleService->assign($vehicle, $validated['user_id'], $validated);

            return back()->with('success', 'Vehicle assigned successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Return vehicle from assignment
     */
    public function returnVehicle(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'returned_date' => 'nullable|date',
            'odometer_end' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            $vehicle = $this->vehicleService->returnVehicle($vehicle, $validated);

            return back()->with('success', 'Vehicle returned successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Generate or regenerate QR code for vehicle
     */
    public function generateQRCode(Vehicle $vehicle)
    {
        $qrPath = $this->qrCodeService->generateVehicleQRCode($vehicle);
        $vehicle->update(['qr_code_path' => $qrPath]);

        return back()->with('success', 'QR Code generated successfully');
    }

    /**
     * Get alerts and reminders
     */
    public function alerts()
    {
        $alerts = $this->vehicleService->getAlertsAndReminders(auth()->user()->branch_id);

        return response()->json($alerts);
    }
}

