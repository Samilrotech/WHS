<?php

namespace App\Modules\VehicleManagement\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssignedVehicleController extends Controller
{
    /**
     * Display the current user's active vehicle assignments.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $assignments = $user->vehicleAssignments()
            ->with([
                'vehicle.branch',
                'vehicle.latestPrestartInspection',
                'vehicle.latestInspection',
            ])
            ->whereNull('returned_date')
            ->orderByDesc('assigned_date')
            ->get();

        $dailyAssignments = $assignments->filter(fn ($assignment) => $assignment->vehicle?->inspection_frequency === 'daily');
        $monthlyAssignments = $assignments->filter(fn ($assignment) => $assignment->vehicle?->inspection_frequency !== 'daily');

        return view('content.vehicles.assigned', [
            'assignments' => $assignments,
            'dailyAssignments' => $dailyAssignments,
            'monthlyAssignments' => $monthlyAssignments,
        ]);
    }
}
