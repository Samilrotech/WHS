<?php

namespace App\Modules\TeamManagement\Api\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleAssignmentController extends Controller
{
    /**
     * Get current user's assigned vehicles
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $assignments = $user->vehicleAssignments()
            ->with([
                'vehicle' => fn ($query) => $query->withTrashed()->with([
                    'branch',
                    'latestPrestartInspection',
                    'latestInspection',
                ]),
            ])
            ->whereNull('returned_date')
            ->orderByDesc('assigned_date')
            ->get();

        $dailyAssignments = $assignments->filter(fn ($assignment) => $assignment->vehicle?->inspection_frequency === 'daily');
        $monthlyAssignments = $assignments->filter(fn ($assignment) => $assignment->vehicle?->inspection_frequency !== 'daily');

        return response()->json([
            'success' => true,
            'data' => [
                'all_assignments' => $assignments->map(function ($assignment) {
                    return $this->formatAssignment($assignment);
                }),
                'daily_assignments' => $dailyAssignments->map(function ($assignment) {
                    return $this->formatAssignment($assignment);
                }),
                'monthly_assignments' => $monthlyAssignments->map(function ($assignment) {
                    return $this->formatAssignment($assignment);
                }),
                'counts' => [
                    'total' => $assignments->count(),
                    'daily' => $dailyAssignments->count(),
                    'monthly' => $monthlyAssignments->count(),
                ],
            ],
        ]);
    }

    /**
     * Format assignment data for API response
     */
    protected function formatAssignment($assignment): array
    {
        $vehicle = $assignment->vehicle;

        if (!$vehicle) {
            return [
                'id' => $assignment->id,
                'vehicle' => null,
                'error' => 'Vehicle no longer available',
            ];
        }

        $latestDaily = $vehicle->latestPrestartInspection;
        $dailyStatus = $latestDaily && $latestDaily->inspection_date?->isToday()
            ? 'completed_today'
            : 'due';

        $nextDue = $vehicle->inspection_due_date;
        $overdue = $nextDue && $nextDue->isPast();

        return [
            'id' => $assignment->id,
            'assigned_date' => $assignment->assigned_date?->toIso8601String(),
            'purpose' => $assignment->purpose,
            'vehicle' => [
                'id' => $vehicle->id,
                'make' => $vehicle->make,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'registration_number' => $vehicle->registration_number,
                'registration_state' => $vehicle->registration_state,
                'odometer_reading' => $vehicle->odometer_reading,
                'next_service_odometer' => $vehicle->next_service_odometer,
                'inspection_frequency' => $vehicle->inspection_frequency,
                'inspection_due_date' => $vehicle->inspection_due_date?->toIso8601String(),
                'branch' => [
                    'id' => $vehicle->branch?->id,
                    'name' => $vehicle->branch?->name,
                ],
            ],
            'inspection_status' => [
                'daily' => [
                    'status' => $dailyStatus,
                    'last_inspection' => $latestDaily?->inspection_date?->toIso8601String(),
                ],
                'next_due' => $nextDue?->toIso8601String(),
                'is_overdue' => $overdue,
            ],
        ];
    }

    /**
     * Get single assignment details
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $assignment = $user->vehicleAssignments()
            ->with([
                'vehicle' => fn ($query) => $query->withTrashed()->with([
                    'branch',
                    'latestPrestartInspection',
                    'latestInspection',
                ]),
            ])
            ->where('id', $id)
            ->whereNull('returned_date')
            ->first();

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatAssignment($assignment),
        ]);
    }
}
