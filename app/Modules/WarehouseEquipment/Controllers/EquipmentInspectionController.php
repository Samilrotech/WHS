<?php

namespace App\Modules\WarehouseEquipment\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\WarehouseEquipment\Models\EquipmentInspection;
use App\Modules\WarehouseEquipment\Models\WarehouseEquipment;
use App\Modules\WarehouseEquipment\Services\EquipmentInspectionService;
use Illuminate\Http\Request;

class EquipmentInspectionController extends Controller
{
    public function __construct(
        protected EquipmentInspectionService $service
    ) {}

    public function index(Request $request)
    {
        $query = EquipmentInspection::with(['equipment', 'inspector']);

        if ($request->filled('equipment_type')) {
            $query->whereHas('equipment', fn($q) => $q->where('equipment_type', $request->equipment_type));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $inspections = $query->latest()->paginate(15);

        $statistics = [
            'total' => EquipmentInspection::count(),
            'passed' => EquipmentInspection::where('passed', true)->count(),
            'failed' => EquipmentInspection::where('passed', false)->count(),
            'defects_found' => EquipmentInspection::where('defects_found', true)->count(),
        ];

        return view('WarehouseEquipment/Inspections/Index', [
            'inspections' => $inspections,
            'statistics' => $statistics,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:warehouse_equipment,id',
            'inspection_type' => 'required',
            'scheduled_date' => 'nullable|date',
        ]);

        $equipment = WarehouseEquipment::findOrFail($validated['equipment_id']);
        $inspection = $this->service->createInspection($equipment, $validated);

        return redirect()->route('equipment-inspections.show', $inspection)
            ->with('success', 'Inspection created successfully');
    }

    public function show(EquipmentInspection $inspection)
    {
        $inspection->load(['equipment', 'inspector', 'checklistItems']);

        return view('WarehouseEquipment/Inspections/Show', [
            'inspection' => $inspection,
        ]);
    }

    public function start(EquipmentInspection $inspection)
    {
        $this->service->startInspection($inspection);

        return redirect()->back()->with('success', 'Inspection started');
    }

    public function complete(Request $request, EquipmentInspection $inspection)
    {
        $validated = $request->validate([
            'inspector_notes' => 'nullable|string',
        ]);

        $this->service->completeInspection($inspection, $validated);

        return redirect()->back()->with('success', 'Inspection completed');
    }

    public function approve(Request $request, EquipmentInspection $inspection)
    {
        $validated = $request->validate([
            'reviewer_comments' => 'nullable|string',
        ]);

        $this->service->approveInspection($inspection, $validated);

        return redirect()->back()->with('success', 'Inspection approved');
    }
}
