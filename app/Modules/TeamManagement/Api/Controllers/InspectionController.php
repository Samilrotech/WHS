<?php

namespace App\Modules\TeamManagement\Api\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\InspectionManagement\Services\InspectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InspectionController extends Controller
{
    public function __construct(
        private InspectionService $inspectionService
    ) {
    }

    /**
     * Get inspection checklist for daily prestart
     */
    public function getChecklist(Request $request): JsonResponse
    {
        $checklist = $this->inspectionService->getDriverQuickChecklist();

        return response()->json([
            'success' => true,
            'data' => $checklist,
        ]);
    }

    /**
     * Submit daily prestart inspection
     */
    public function store(Request $request): JsonResponse
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
            return response()->json([
                'success' => false,
                'message' => 'We could not find an active vehicle assignment for your profile.',
            ], 404);
        }

        try {
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

            return response()->json([
                'success' => true,
                'message' => 'Inspection submitted successfully',
                'data' => [
                    'inspection_id' => $inspection->id,
                    'inspection_number' => $inspection->inspection_number,
                    'overall_result' => $inspection->overall_result,
                    'status' => $inspection->status,
                    'critical_defects' => $inspection->critical_defects,
                    'major_defects' => $inspection->major_defects,
                    'minor_defects' => $inspection->minor_defects,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit inspection',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
