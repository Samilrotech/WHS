<?php

use App\Modules\TeamManagement\Api\Controllers\AuthController;
use App\Modules\TeamManagement\Api\Controllers\InspectionController;
use App\Modules\TeamManagement\Api\Controllers\TeamMemberController;
use App\Modules\TeamManagement\Api\Controllers\VehicleAssignmentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API Routes (v1)
|--------------------------------------------------------------------------
|
| These routes are for the WHS5 mobile application (Flutter Android app).
| All routes are prefixed with /api/v1/mobile and use Sanctum authentication.
|
*/

Route::prefix('v1/mobile')->group(function () {
    // Public authentication endpoints
    Route::post('auth/login', [AuthController::class, 'login'])->name('api.mobile.auth.login');

    // Protected routes (require Sanctum token)
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        // Authentication
        Route::get('auth/user', [AuthController::class, 'user'])->name('api.mobile.auth.user');
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('api.mobile.auth.logout');
        Route::delete('auth/tokens/{token}', [AuthController::class, 'revokeToken'])->name('api.mobile.auth.revoke');

        // Team Members
        Route::get('team-members', [TeamMemberController::class, 'index'])->name('api.mobile.team-members.index');
        Route::get('team-members/{id}', [TeamMemberController::class, 'show'])->name('api.mobile.team-members.show');

        // Vehicle Assignments
        Route::get('vehicle-assignments', [VehicleAssignmentController::class, 'index'])->name('api.mobile.vehicle-assignments.index');
        Route::get('vehicle-assignments/{id}', [VehicleAssignmentController::class, 'show'])->name('api.mobile.vehicle-assignments.show');

        // Inspections
        Route::get('inspections/checklist', [InspectionController::class, 'getChecklist'])->name('api.mobile.inspections.checklist');
        Route::post('inspections', [InspectionController::class, 'store'])->name('api.mobile.inspections.store');
    });
});
