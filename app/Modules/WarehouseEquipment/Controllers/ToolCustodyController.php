<?php

namespace App\Modules\WarehouseEquipment\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\WarehouseEquipment\Models\ToolCustodyLog;
use App\Modules\WarehouseEquipment\Models\WarehouseEquipment;
use App\Modules\WarehouseEquipment\Services\ToolCustodyService;
use Illuminate\Http\Request;

class ToolCustodyController extends Controller
{
    public function __construct(
        protected ToolCustodyService $service
    ) {}

    public function index(Request $request)
    {
        $query = ToolCustodyLog::with(['equipment', 'custodian']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->latest()->paginate(15);
        $statistics = $this->service->getStatistics(auth()->user()->branch_id);

        return view('WarehouseEquipment/ToolCustody/Index', [
            'custodyLogs' => $logs,
            'statistics' => $statistics,
        ]);
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:warehouse_equipment,id',
            'custodian_user_id' => 'required|exists:users,id',
            'expected_return_date' => 'required|date',
            'purpose' => 'nullable|string',
            'condition_on_checkout' => 'required',
        ]);

        $equipment = WarehouseEquipment::findOrFail($validated['equipment_id']);
        $this->service->checkoutEquipment($equipment, $validated);

        return redirect()->back()->with('success', 'Equipment checked out successfully');
    }

    public function checkin(Request $request, ToolCustodyLog $custodyLog)
    {
        $validated = $request->validate([
            'condition_on_checkin' => 'required',
            'checkin_notes' => 'nullable|string',
            'damage_reported' => 'boolean',
            'damage_description' => 'nullable|required_if:damage_reported,true',
        ]);

        $this->service->checkinEquipment($custodyLog, $validated);

        return redirect()->back()->with('success', 'Equipment checked in successfully');
    }
}
