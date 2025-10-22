<?php

namespace App\Modules\VehicleManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\VehicleManagement\Models\Vehicle;
use App\Modules\VehicleManagement\Services\VehicleService;
use App\Modules\VehicleManagement\Services\QRCodeService;
use App\Modules\VehicleManagement\Services\DepreciationService;
use Illuminate\Http\Request;

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
        $vehicles = $this->vehicleService->getPaginated($request->all());
        $statistics = $this->vehicleService->getStatistics(auth()->user()->branch_id);
        $makes = $this->vehicleService->getUniqueMakes(auth()->user()->branch_id);

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
            'filters' => $request->only(['search', 'status', 'make', 'assigned']),
        ]);
    }

    /**
     * Show the form for creating a new vehicle
     */
    public function create()
    {
        return view('content.vehicles.create', [
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
        ]);
    }

    /**
     * Store a newly created vehicle
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'registration_number' => 'required|string|unique:vehicles',
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

        return view('content.vehicles.show', [
            'vehicle' => $vehicle,
            'depreciation_schedule' => $depreciationSchedule,
            'total_depreciation' => $totalDepreciation,
            'service_stats' => $serviceStats,
            'qr_code_url' => $vehicle->qr_code_path
                ? $this->qrCodeService->getQRCodeUrl($vehicle->qr_code_path)
                : null,
        ]);
    }

    /**
     * Show the form for editing the vehicle
     */
    public function edit(Vehicle $vehicle)
    {
        return view('content.vehicles.edit', [
            'vehicle' => $vehicle,
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
        ]);
    }

    /**
     * Update the specified vehicle
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'registration_number' => 'required|string|unique:vehicles,registration_number,' . $vehicle->id,
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
