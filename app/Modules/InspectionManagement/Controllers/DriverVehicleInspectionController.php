<?php

namespace App\Modules\InspectionManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\InspectionManagement\Services\InspectionService;
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
}
