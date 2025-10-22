<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BranchController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\dashboard\Analytics;

// WHS4 Module Controllers
use App\Modules\IncidentManagement\Controllers\IncidentController;
use App\Modules\RiskAssessment\Controllers\RiskAssessmentController;
use App\Modules\EmergencyResponse\Controllers\EmergencyResponseController;
use App\Modules\VehicleManagement\Controllers\VehicleController;
use App\Modules\InspectionManagement\Controllers\InspectionController;
use App\Modules\CAPAManagement\Controllers\CAPAController;
use App\Modules\JourneyManagement\Controllers\JourneyController;
use App\Modules\MaintenanceScheduling\Controllers\MaintenanceController;
use App\Modules\WarehouseEquipment\Controllers\WarehouseEquipmentController;
use App\Modules\SafetyInspections\Controllers\SafetyInspectionController;
use App\Modules\PermitToWork\Controllers\PermitController;
use App\Modules\TeamManagement\Controllers\TeamController;
use App\Modules\TrainingManagement\Controllers\TrainingController;
use App\Modules\ContractorManagement\Controllers\ContractorController;
use App\Modules\DocumentManagement\Controllers\DocumentController;
use App\Modules\ComplianceReporting\Controllers\ComplianceReportController;
use App\Modules\VehicleReportingDashboard\Controllers\VehicleReportingController;

// PWA Offline Fallback Page (No authentication required)
Route::get('/offline', function () {
    return view('offline');
})->name('offline');

// Authentication Routes (Breeze)
Route::middleware('guest')->group(function () {
    require __DIR__.'/auth.php';
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/', [Analytics::class, 'index'])->name('dashboard-analytics');
    Route::get('/dashboard', [Analytics::class, 'index'])->name('dashboard');

    // Profile Routes (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // =====================================
    // WHS4 CORE SYSTEM ROUTES
    // =====================================

    // Branch Management (SaaS Core - Admin Only)
    Route::middleware('role:Admin')->prefix('branches')->name('branches.')->group(function () {
        Route::get('/', [BranchController::class, 'index'])->name('index');
        Route::get('/create', [BranchController::class, 'create'])->name('create');
        Route::post('/', [BranchController::class, 'store'])->name('store');
        Route::get('/{branch}', [BranchController::class, 'show'])->name('show');
        Route::get('/{branch}/edit', [BranchController::class, 'edit'])->name('edit');
        Route::put('/{branch}', [BranchController::class, 'update'])->name('update');
        Route::delete('/{branch}', [BranchController::class, 'destroy'])->name('destroy');
        Route::post('/{branch}/toggle-status', [BranchController::class, 'toggleStatus'])->name('toggleStatus');
    });

    // =====================================
    // WHS4 MODULE ROUTES
    // =====================================

    // Module 1: Incident Management
    Route::prefix('incidents')->name('incidents.')->group(function () {
        Route::get('/', [IncidentController::class, 'index'])->name('index');
        Route::get('/create', [IncidentController::class, 'create'])->name('create');
        Route::post('/', [IncidentController::class, 'store'])->name('store');
        Route::get('/{incident}', [IncidentController::class, 'show'])->name('show');
        Route::get('/{incident}/edit', [IncidentController::class, 'edit'])->name('edit');
        Route::put('/{incident}', [IncidentController::class, 'update'])->name('update');
        Route::delete('/{incident}', [IncidentController::class, 'destroy'])->name('destroy');

        // Additional incident actions
        Route::post('/{incident}/assign', [IncidentController::class, 'assign'])->name('assign');
        Route::post('/{incident}/close', [IncidentController::class, 'close'])->name('close');
        Route::delete('/photos/{photo}', [IncidentController::class, 'deletePhoto'])->name('deletePhoto');
    });

    // Module 2: Risk Assessment
    Route::prefix('risk')->name('risk.')->group(function () {
        Route::get('/', [RiskAssessmentController::class, 'index'])->name('index');
        Route::get('/create', [RiskAssessmentController::class, 'create'])->name('create');
        Route::get('/matrix', [RiskAssessmentController::class, 'matrix'])->name('matrix'); // Must be before {riskAssessment}
        Route::post('/', [RiskAssessmentController::class, 'store'])->name('store');
        Route::get('/{riskAssessment}', [RiskAssessmentController::class, 'show'])->name('show');
        Route::get('/{riskAssessment}/edit', [RiskAssessmentController::class, 'edit'])->name('edit');
        Route::put('/{riskAssessment}', [RiskAssessmentController::class, 'update'])->name('update');
        Route::delete('/{riskAssessment}', [RiskAssessmentController::class, 'destroy'])->name('destroy');

        // Additional actions
        Route::post('/{riskAssessment}/submit', [RiskAssessmentController::class, 'submit'])->name('submit');
        Route::post('/{riskAssessment}/approve', [RiskAssessmentController::class, 'approve'])->name('approve');
        Route::post('/{riskAssessment}/reject', [RiskAssessmentController::class, 'reject'])->name('reject');
    });

    // Module 3: Emergency Response
    Route::prefix('emergency')->name('emergency.')->group(function () {
        Route::get('/', [EmergencyResponseController::class, 'index'])->name('index');
        Route::get('/create', [EmergencyResponseController::class, 'create'])->name('create');
        Route::post('/', [EmergencyResponseController::class, 'store'])->name('store');
        Route::get('/{emergencyAlert}', [EmergencyResponseController::class, 'show'])->name('show');
        Route::get('/{emergencyAlert}/edit', [EmergencyResponseController::class, 'edit'])->name('edit');
        Route::put('/{emergencyAlert}', [EmergencyResponseController::class, 'update'])->name('update');
        Route::delete('/{emergencyAlert}', [EmergencyResponseController::class, 'destroy'])->name('destroy');

        // Additional emergency response actions
        Route::post('/{emergencyAlert}/respond', [EmergencyResponseController::class, 'respond'])->name('respond');
        Route::post('/{emergencyAlert}/resolve', [EmergencyResponseController::class, 'resolve'])->name('resolve');
        Route::post('/{emergencyAlert}/cancel', [EmergencyResponseController::class, 'cancel'])->name('cancel');
    });

    // Module 4: Vehicle Management
    Route::prefix('vehicles')->name('vehicles.')->group(function () {
        Route::get('/', [VehicleController::class, 'index'])->name('index');
        Route::get('/create', [VehicleController::class, 'create'])->name('create');
        Route::post('/', [VehicleController::class, 'store'])->name('store');
        Route::get('/{vehicle}', [VehicleController::class, 'show'])->name('show');
        Route::get('/{vehicle}/edit', [VehicleController::class, 'edit'])->name('edit');
        Route::put('/{vehicle}', [VehicleController::class, 'update'])->name('update');
        Route::delete('/{vehicle}', [VehicleController::class, 'destroy'])->name('destroy');

        // Additional vehicle actions
        Route::post('/{vehicle}/assign', [VehicleController::class, 'assign'])->name('assign');
        Route::post('/{vehicle}/return', [VehicleController::class, 'returnVehicle'])->name('return');
        Route::post('/{vehicle}/qr-code', [VehicleController::class, 'generateQRCode'])->name('generateQRCode');
        Route::get('/alerts', [VehicleController::class, 'alerts'])->name('alerts');
    });

    // Module 5: Inspection Management
    Route::prefix('inspections')->name('inspections.')->group(function () {
        Route::get('/', [InspectionController::class, 'index'])->name('index');
        Route::get('/create', [InspectionController::class, 'create'])->name('create');
        Route::post('/', [InspectionController::class, 'store'])->name('store');
        Route::get('/{inspection}', [InspectionController::class, 'show'])->name('show');
        Route::get('/{inspection}/edit', [InspectionController::class, 'edit'])->name('edit');
        Route::put('/{inspection}', [InspectionController::class, 'update'])->name('update');
        Route::delete('/{inspection}', [InspectionController::class, 'destroy'])->name('destroy');

        // Inspection workflow actions
        Route::post('/{inspection}/start', [InspectionController::class, 'start'])->name('start');
        Route::post('/{inspection}/complete', [InspectionController::class, 'complete'])->name('complete');
        Route::post('/{inspection}/approve', [InspectionController::class, 'approve'])->name('approve');
        Route::post('/{inspection}/reject', [InspectionController::class, 'reject'])->name('reject');

        // Inspection item updates
        Route::put('/{inspection}/items/{item}', [InspectionController::class, 'updateItem'])->name('updateItem');
        Route::post('/{inspection}/items/{item}/repair', [InspectionController::class, 'completeRepair'])->name('completeRepair');
    });

    // Module 6: CAPA Management
    Route::prefix('capa')->name('capa.')->group(function () {
        Route::get('/', [CAPAController::class, 'index'])->name('index');
        Route::get('/create', [CAPAController::class, 'create'])->name('create');
        Route::post('/', [CAPAController::class, 'store'])->name('store');
        Route::get('/{capa}', [CAPAController::class, 'show'])->name('show');
        Route::get('/{capa}/edit', [CAPAController::class, 'edit'])->name('edit');
        Route::put('/{capa}', [CAPAController::class, 'update'])->name('update');
        Route::delete('/{capa}', [CAPAController::class, 'destroy'])->name('destroy');

        // CAPA workflow actions
        Route::post('/{capa}/submit', [CAPAController::class, 'submit'])->name('submit');
        Route::post('/{capa}/approve', [CAPAController::class, 'approve'])->name('approve');
        Route::post('/{capa}/reject', [CAPAController::class, 'reject'])->name('reject');
        Route::post('/{capa}/start', [CAPAController::class, 'start'])->name('start');
        Route::post('/{capa}/complete', [CAPAController::class, 'complete'])->name('complete');
        Route::post('/{capa}/verify', [CAPAController::class, 'verify'])->name('verify');
        Route::post('/{capa}/close', [CAPAController::class, 'close'])->name('close');

        // CAPA action management
        Route::post('/{capa}/actions', [CAPAController::class, 'createAction'])->name('createAction');
        Route::post('/actions/{action}/complete', [CAPAController::class, 'completeAction'])->name('completeAction');
    });

    // Module 7: Journey Management
    Route::prefix('journey')->name('journey.')->group(function () {
        Route::get('/', [JourneyController::class, 'index'])->name('index');
        Route::get('/create', [JourneyController::class, 'create'])->name('create');
        Route::post('/', [JourneyController::class, 'store'])->name('store');
        Route::get('/{journey}', [JourneyController::class, 'show'])->name('show');
        Route::get('/{journey}/edit', [JourneyController::class, 'edit'])->name('edit');
        Route::put('/{journey}', [JourneyController::class, 'update'])->name('update');
        Route::delete('/{journey}', [JourneyController::class, 'destroy'])->name('destroy');

        // Journey actions
        Route::post('/{journey}/start', [JourneyController::class, 'start'])->name('start');
        Route::post('/{journey}/checkin', [JourneyController::class, 'checkin'])->name('checkin');
        Route::post('/{journey}/complete', [JourneyController::class, 'complete'])->name('complete');
    });

    // Module 8: Maintenance Scheduling
    Route::prefix('maintenance')->name('maintenance.')->group(function () {
        // Maintenance Logs (Work Orders) - MUST be before {maintenance} routes
        Route::prefix('logs')->name('logs.')->group(function () {
            Route::get('/', [MaintenanceController::class, 'logsIndex'])->name('index');
            Route::get('/create', [MaintenanceController::class, 'logsCreate'])->name('create');
            Route::post('/', [MaintenanceController::class, 'logsStore'])->name('store');
            Route::get('/{log}', [MaintenanceController::class, 'logsShow'])->name('show');
            Route::put('/{log}', [MaintenanceController::class, 'logsUpdate'])->name('update');
            Route::delete('/{log}', [MaintenanceController::class, 'logsDestroy'])->name('destroy');
            Route::post('/{log}/approve', [MaintenanceController::class, 'logsApprove'])->name('approve');
            Route::post('/{log}/complete', [MaintenanceController::class, 'logsComplete'])->name('complete');
            Route::post('/{log}/verify', [MaintenanceController::class, 'logsVerify'])->name('verify');
        });

        // Parts Inventory - MUST be before {maintenance} routes
        Route::prefix('parts')->name('parts.')->group(function () {
            Route::get('/', [MaintenanceController::class, 'partsIndex'])->name('index');
            Route::post('/', [MaintenanceController::class, 'partsStore'])->name('store');
            Route::put('/{part}', [MaintenanceController::class, 'partsUpdate'])->name('update');
        });

        // Maintenance Schedules
        Route::get('/', [MaintenanceController::class, 'index'])->name('index');
        Route::get('/create', [MaintenanceController::class, 'create'])->name('create');
        Route::post('/', [MaintenanceController::class, 'store'])->name('store');
        Route::get('/{maintenance}', [MaintenanceController::class, 'show'])->name('show');
        Route::get('/{maintenance}/edit', [MaintenanceController::class, 'edit'])->name('edit');
        Route::put('/{maintenance}', [MaintenanceController::class, 'update'])->name('update');
        Route::delete('/{maintenance}', [MaintenanceController::class, 'destroy'])->name('destroy');
        Route::post('/{maintenance}/pause', [MaintenanceController::class, 'pause'])->name('pause');
        Route::post('/{maintenance}/resume', [MaintenanceController::class, 'resume'])->name('resume');
    });

    // Module 9: Warehouse Equipment
    Route::prefix('warehouse-equipment')->name('warehouse-equipment.')->group(function () {
        Route::get('/', [WarehouseEquipmentController::class, 'index'])->name('index');
        Route::get('/create', [WarehouseEquipmentController::class, 'create'])->name('create');
        Route::post('/', [WarehouseEquipmentController::class, 'store'])->name('store');
        Route::get('/{equipment}', [WarehouseEquipmentController::class, 'show'])->name('show');
        Route::get('/{equipment}/edit', [WarehouseEquipmentController::class, 'edit'])->name('edit');
        Route::put('/{equipment}', [WarehouseEquipmentController::class, 'update'])->name('update');
        Route::delete('/{equipment}', [WarehouseEquipmentController::class, 'destroy'])->name('destroy');

        // Equipment actions
        Route::post('/{equipment}/checkout', [WarehouseEquipmentController::class, 'checkout'])->name('checkout');
        Route::post('/{equipment}/return', [WarehouseEquipmentController::class, 'return'])->name('return');
    });

    // Module 10: Safety Inspections
    Route::prefix('safety-inspections')->name('safety-inspections.')->group(function () {
        Route::get('/', [SafetyInspectionController::class, 'index'])->name('index');
        Route::get('/create', [SafetyInspectionController::class, 'create'])->name('create');
        Route::post('/', [SafetyInspectionController::class, 'store'])->name('store');
        Route::get('/{safetyInspection}', [SafetyInspectionController::class, 'show'])->name('show');
        Route::get('/{safetyInspection}/edit', [SafetyInspectionController::class, 'edit'])->name('edit');
        Route::put('/{safetyInspection}', [SafetyInspectionController::class, 'update'])->name('update');
        Route::delete('/{safetyInspection}', [SafetyInspectionController::class, 'destroy'])->name('destroy');

        // Safety inspection actions
        Route::post('/{safetyInspection}/start', [SafetyInspectionController::class, 'start'])->name('start');
        Route::post('/{safetyInspection}/complete', [SafetyInspectionController::class, 'complete'])->name('complete');
        Route::post('/{safetyInspection}/submit', [SafetyInspectionController::class, 'submit'])->name('submit');
        Route::post('/{safetyInspection}/approve', [SafetyInspectionController::class, 'approve'])->name('approve');
        Route::post('/{safetyInspection}/reject', [SafetyInspectionController::class, 'reject'])->name('reject');
        Route::post('/{safetyInspection}/escalate', [SafetyInspectionController::class, 'escalate'])->name('escalate');
    });

    // Module 11: Permit to Work
    Route::prefix('permit-to-work')->name('permit-to-work.')->group(function () {
        Route::get('/', [PermitController::class, 'index'])->name('index');
        Route::get('/create', [PermitController::class, 'create'])->name('create');
        Route::post('/', [PermitController::class, 'store'])->name('store');
        Route::get('/{permit}', [PermitController::class, 'show'])->name('show');
        Route::get('/{permit}/edit', [PermitController::class, 'edit'])->name('edit');
        Route::put('/{permit}', [PermitController::class, 'update'])->name('update');
        Route::delete('/{permit}', [PermitController::class, 'destroy'])->name('destroy');

        // Permit actions
        Route::post('/{permit}/approve', [PermitController::class, 'approve'])->name('approve');
        Route::post('/{permit}/close', [PermitController::class, 'close'])->name('close');
    });

    // Module 12: Team Management
    Route::prefix('teams')->name('teams.')->group(function () {
        Route::get('/', [TeamController::class, 'index'])->name('index');
        Route::get('/create', [TeamController::class, 'create'])->name('create');
        Route::post('/', [TeamController::class, 'store'])->name('store');
        Route::get('/{team}', [TeamController::class, 'show'])->name('show');
        Route::get('/{team}/edit', [TeamController::class, 'edit'])->name('edit');
        Route::put('/{team}', [TeamController::class, 'update'])->name('update');
        Route::delete('/{team}', [TeamController::class, 'destroy'])->name('destroy');

        // Team member management
        Route::post('/{team}/members', [TeamController::class, 'addMember'])->name('addMember');
        Route::delete('/{team}/members/{user}', [TeamController::class, 'removeMember'])->name('removeMember');
    });

    // Module 13: Training Management
    Route::prefix('training')->name('training.')->group(function () {
        Route::get('/', [TrainingController::class, 'index'])->name('index');

        // Training Courses sub-module
        Route::prefix('courses')->name('courses.')->group(function () {
            Route::get('/', [TrainingController::class, 'coursesIndex'])->name('index');
            Route::get('/create', [TrainingController::class, 'coursesCreate'])->name('create');
            Route::post('/', [TrainingController::class, 'coursesStore'])->name('store');
            Route::get('/{course}', [TrainingController::class, 'coursesShow'])->name('show');
            Route::put('/{course}', [TrainingController::class, 'coursesUpdate'])->name('update');
            Route::delete('/{course}', [TrainingController::class, 'coursesDestroy'])->name('destroy');
        });

        // Training Records sub-module
        Route::prefix('records')->name('records.')->group(function () {
            Route::get('/', [TrainingController::class, 'recordsIndex'])->name('index');
            Route::get('/create', [TrainingController::class, 'recordsCreate'])->name('create');
            Route::post('/', [TrainingController::class, 'recordsStore'])->name('store');
            Route::get('/{record}', [TrainingController::class, 'recordsShow'])->name('show');
            Route::put('/{record}', [TrainingController::class, 'recordsUpdate'])->name('update');
            Route::delete('/{record}', [TrainingController::class, 'recordsDestroy'])->name('destroy');
            Route::post('/{record}/complete', [TrainingController::class, 'recordsComplete'])->name('complete');
        });

        // Certifications sub-module
        Route::prefix('certifications')->name('certifications.')->group(function () {
            Route::get('/', [TrainingController::class, 'certificationsIndex'])->name('index');
            Route::get('/create', [TrainingController::class, 'certificationsCreate'])->name('create');
            Route::post('/', [TrainingController::class, 'certificationsStore'])->name('store');
            Route::get('/{certification}', [TrainingController::class, 'certificationsShow'])->name('show');
            Route::put('/{certification}', [TrainingController::class, 'certificationsUpdate'])->name('update');
            Route::delete('/{certification}', [TrainingController::class, 'certificationsDestroy'])->name('destroy');
            Route::post('/{certification}/renew', [TrainingController::class, 'certificationsRenew'])->name('renew');
        });
    });

    // Module 14: Contractor Management
    Route::prefix('contractors')->name('contractors.')->group(function () {
        Route::get('/', [ContractorController::class, 'index'])->name('index');
        Route::get('/create', [ContractorController::class, 'create'])->name('create');
        Route::post('/', [ContractorController::class, 'store'])->name('store');
        Route::get('/{contractor}', [ContractorController::class, 'show'])->name('show');
        Route::get('/{contractor}/edit', [ContractorController::class, 'edit'])->name('edit');
        Route::put('/{contractor}', [ContractorController::class, 'update'])->name('update');
        Route::delete('/{contractor}', [ContractorController::class, 'destroy'])->name('destroy');

        // Contractor actions
        Route::post('/{contractor}/induct', [ContractorController::class, 'induct'])->name('induct');
        Route::post('/{contractor}/sign-in', [ContractorController::class, 'signIn'])->name('signIn');
        Route::post('/{contractor}/sign-out', [ContractorController::class, 'signOut'])->name('signOut');
    });

    // Module 15: Document Management
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::get('/create', [DocumentController::class, 'create'])->name('create');
        Route::post('/', [DocumentController::class, 'store'])->name('store');
        Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
        Route::get('/{document}/edit', [DocumentController::class, 'edit'])->name('edit');
        Route::put('/{document}', [DocumentController::class, 'update'])->name('update');
        Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy');

        // Document actions
        Route::get('/{document}/download', [DocumentController::class, 'download'])->name('download');
        Route::post('/{document}/version', [DocumentController::class, 'newVersion'])->name('newVersion');
    });

    // Module 16: Compliance Reporting
    Route::prefix('compliance')->name('compliance.')->group(function () {
        Route::get('/', [ComplianceReportController::class, 'index'])->name('index');
        Route::get('/create', [ComplianceReportController::class, 'create'])->name('create');
        Route::post('/', [ComplianceReportController::class, 'store'])->name('store');
        Route::get('/{report}', [ComplianceReportController::class, 'show'])->name('show');
        Route::get('/{report}/edit', [ComplianceReportController::class, 'edit'])->name('edit');
        Route::put('/{report}', [ComplianceReportController::class, 'update'])->name('update');
        Route::delete('/{report}', [ComplianceReportController::class, 'destroy'])->name('destroy');

        // Report actions
        Route::post('/{report}/submit', [ComplianceReportController::class, 'submit'])->name('submit');
        Route::get('/{report}/export', [ComplianceReportController::class, 'export'])->name('export');
    });

    // Module 17: Vehicle Reporting Dashboard
    Route::prefix('vehicle-reporting')->name('vehicle-reporting.')->group(function () {
        Route::get('/', [VehicleReportingController::class, 'index'])->name('index');
        Route::get('/fleet', [VehicleReportingController::class, 'fleet'])->name('fleet');
        Route::get('/inspections', [VehicleReportingController::class, 'inspections'])->name('inspections');
        Route::get('/maintenance', [VehicleReportingController::class, 'maintenance'])->name('maintenance');
        Route::get('/costs', [VehicleReportingController::class, 'costs'])->name('costs');
        Route::get('/compliance', [VehicleReportingController::class, 'compliance'])->name('compliance');
    });
});
