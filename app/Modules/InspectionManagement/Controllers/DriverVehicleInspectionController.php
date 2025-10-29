<?php

namespace App\Modules\InspectionManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\InspectionManagement\Services\InspectionService;
use App\Modules\VehicleManagement\Models\VehicleAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DriverVehicleInspectionController extends Controller
{
    public function __construct(
        private InspectionService $inspectionService
    ) {
    }

    /**
     * Show the quick driver inspection form for the assigned vehicle
     */
    public function create(Request $request, ?string $assignmentId = null): View
    {
        $user = $request->user();

        $assignment = $user->vehicleAssignments()
            ->with('vehicle.branch')
            ->whereNull('returned_date')
            ->when($assignmentId, fn ($query) => $query->where('id', $assignmentId))
            ->latest('assigned_date')
            ->first();

        return view('content.inspections.driver', [
            'assignment' => $assignment,
            'vehicle' => $assignment?->vehicle,
            'checklist' => $this->inspectionService->getDriverQuickChecklist(),
        ]);
    }

    /**
     * Store the submitted driver inspection
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $checklist = collect($this->inspectionService->getDriverQuickChecklist());
        $slugs = $checklist->pluck('slug')->all();

        $rules = [
            'vehicle_assignment_id' => ['required', 'uuid'],
            'odometer_reading' => ['nullable', 'integer', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'inspector_notes' => ['nullable', 'string'],
            'checks' => ['required', 'array'],
            'notes' => ['nullable', 'array'],
        ];

        foreach ($slugs as $slug) {
            $rules["checks.{$slug}"] = ['required', Rule::in(['pass', 'fail', 'na'])];
            $rules["notes.{$slug}"] = ['nullable', 'string', 'max:500'];
        }

        $validated = $request->validate($rules);

        $assignment = $user->vehicleAssignments()
            ->with('vehicle')
            ->where('id', $validated['vehicle_assignment_id'])
            ->whereNull('returned_date')
            ->first();

        if (!$assignment) {
            return back()
                ->withErrors(['vehicle_assignment_id' => 'We could not find an active vehicle assignment for your profile.'])
                ->withInput();
        }

        $payload = [
            'vehicle_id' => $assignment->vehicle_id,
            'vehicle_assignment_id' => $assignment->id,
            'odometer_reading' => $validated['odometer_reading'] ?? null,
            'location' => $validated['location'] ?? null,
            'inspector_notes' => $validated['inspector_notes'] ?? null,
            'checks' => array_intersect_key($validated['checks'], array_flip($slugs)),
            'notes' => collect($slugs)
                ->mapWithKeys(function ($slug) use ($validated) {
                    $note = $validated['notes'][$slug] ?? null;
                    $trimmed = trim((string) $note);

                    return [$slug => $trimmed !== '' ? $trimmed : null];
                })
                ->toArray(),
        ];

        $inspection = $this->inspectionService->createDriverVehicleInspection($payload);

        if (!empty($payload['odometer_reading'])) {
            $assignment->vehicle->update([
                'odometer_reading' => $payload['odometer_reading'],
            ]);
        }

        return redirect()
            ->route('driver.vehicle-inspections.index')
            ->with('success', "Inspection {$inspection->inspection_number} submitted. We'll keep you posted if anything needs attention.");
    }

    /**
     * Show the monthly inspection form for the driver's assigned vehicle.
     */
    public function monthly(Request $request, VehicleAssignment $assignment): View
    {
        $user = $request->user();

        if ($assignment->user_id !== $user->id || $assignment->returned_date) {
            abort(403, 'You are not authorised to access this inspection.');
        }

        $assignment->loadMissing('vehicle.branch');

        return view('content.inspections.monthly-driver', [
            'assignment' => $assignment,
            'vehicle' => $assignment->vehicle,
        ]);
    }

    /**
     * Store the submitted monthly inspection.
     */
    public function storeMonthly(Request $request, VehicleAssignment $assignment): RedirectResponse
    {
        $user = $request->user();

        if ($assignment->user_id !== $user->id || $assignment->returned_date) {
            abort(403, 'You are not authorised to submit this inspection.');
        }

        $conditionOptions = ['good', 'attention', 'poor'];
        $binaryOptions = ['yes', 'no'];

        $validated = $request->validate([
            'odometer_reading' => ['required', 'integer', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],

            'exterior_condition' => ['required', Rule::in($conditionOptions)],
            'lights_condition' => ['required', Rule::in($conditionOptions)],
            'body_condition' => ['required', Rule::in($conditionOptions)],
            'interior_condition' => ['required', Rule::in($conditionOptions)],
            'seatbelt_condition' => ['required', Rule::in($conditionOptions)],
            'dashboard_lights' => ['required', Rule::in($binaryOptions)],
            'air_conditioning' => ['required', Rule::in($binaryOptions)],
            'tire_condition' => ['required', Rule::in($conditionOptions)],
            'tread_condition' => ['required', Rule::in($conditionOptions)],

            'tire_front_left_photo' => ['required', 'image', 'max:5120'],
            'tire_front_right_photo' => ['required', 'image', 'max:5120'],
            'tire_rear_left_photo' => ['required', 'image', 'max:5120'],
            'tire_rear_right_photo' => ['required', 'image', 'max:5120'],

            'brakes_normal' => ['required', Rule::in($binaryOptions)],
            'steering_smooth' => ['required', Rule::in($binaryOptions)],
            'noise_smoke' => ['required', Rule::in($binaryOptions)],

            'issue_description' => ['nullable', 'string'],
            'issue_photo' => ['nullable', 'image', 'max:5120'],

            'incident_occurred' => ['required', Rule::in($binaryOptions)],
            'incident_description' => ['required_if:incident_occurred,yes', 'nullable', 'string'],

            'next_service_date' => ['required', 'date'],
            'next_service_kilometre' => ['required', 'integer', 'min:0'],

            'vehicle_photo_front' => ['required', 'image', 'max:5120'],
            'vehicle_photo_rear' => ['required', 'image', 'max:5120'],
            'vehicle_photo_driver_side' => ['required', 'image', 'max:5120'],
            'vehicle_photo_passenger_side' => ['required', 'image', 'max:5120'],
            'vehicle_photo_interior' => ['required', 'image', 'max:5120'],

            'employee_confirmation' => ['accepted'],
        ], [
            'employee_confirmation.accepted' => 'You must confirm the inspection details before submitting.',
            'incident_description.required_if' => 'Please describe the accident or damage for this month.',
        ]);

        $inspection = $this->inspectionService->createMonthlyDriverInspection($assignment, $validated);

        if (!empty($validated['odometer_reading'])) {
            $assignment->vehicle->update([
                'odometer_reading' => (int) $validated['odometer_reading'],
            ]);
        }

        return redirect()
            ->route('inspections.show', $inspection->id)
            ->with('success', "Monthly inspection {$inspection->inspection_number} submitted successfully.");
    }
}
