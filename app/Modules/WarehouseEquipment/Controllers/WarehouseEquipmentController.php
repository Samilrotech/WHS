<?php

namespace App\Modules\WarehouseEquipment\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\WarehouseEquipment\Models\WarehouseEquipment;
use App\Modules\WarehouseEquipment\Services\WarehouseEquipmentService;
use Illuminate\Http\Request;

class WarehouseEquipmentController extends Controller
{
    public function __construct(
        protected WarehouseEquipmentService $service
    ) {}

    public function index(Request $request)
    {
        $query = WarehouseEquipment::with(['branch']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('equipment_name', 'like', "%{$request->search}%")
                  ->orWhere('equipment_code', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('equipment_type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $equipment = $query->latest()->paginate(15);
        $statistics = $this->service->getStatistics(auth()->user()->branch_id);

        return view('content.WarehouseEquipment.Index', [
            'equipment' => $equipment,
            'statistics' => $statistics,
            'filters' => $request->only(['search', 'type', 'status']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_code' => 'required|unique:warehouse_equipment',
            'equipment_name' => 'required',
            'equipment_type' => 'required',
            'manufacturer' => 'nullable',
            'model' => 'nullable',
            'location' => 'nullable',
            'requires_license' => 'boolean',
            'license_type' => 'nullable|required_if:requires_license,true',
        ]);

        $equipment = WarehouseEquipment::create(array_merge($validated, [
            'branch_id' => auth()->user()->branch_id,
        ]));

        if ($request->boolean('generate_qr')) {
            $qrPath = $this->service->generateQrCode($equipment);
            $equipment->update(['qr_code_path' => $qrPath]);
        }

        return redirect()->route('warehouse-equipment.index')
            ->with('success', 'Equipment created successfully');
    }

    public function show(WarehouseEquipment $equipment)
    {
        $equipment->load(['inspections', 'custodyLogs.custodian']);

        return view('content.WarehouseEquipment.Show', [
            'equipment' => $equipment,
        ]);
    }

    public function update(Request $request, WarehouseEquipment $equipment)
    {
        $validated = $request->validate([
            'equipment_name' => 'required',
            'equipment_type' => 'required',
            'status' => 'required',
            'location' => 'nullable',
        ]);

        $equipment->update($validated);

        return redirect()->back()->with('success', 'Equipment updated successfully');
    }

    public function destroy(WarehouseEquipment $equipment)
    {
        $equipment->delete();

        return redirect()->route('warehouse-equipment.index')
            ->with('success', 'Equipment deleted successfully');
    }
}
