<?php

namespace App\Modules\EmergencyResponse\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmergencyAlert;
use App\Modules\EmergencyResponse\Requests\StoreEmergencyAlertRequest;
use App\Modules\EmergencyResponse\Requests\UpdateEmergencyAlertRequest;
use App\Modules\EmergencyResponse\Resources\EmergencyAlertResource;
use App\Modules\EmergencyResponse\Services\EmergencyResponseService;
use Illuminate\Http\Request;

class EmergencyResponseController extends Controller
{
    public function __construct(
        private EmergencyResponseService $service
    ) {}

    /**
     * Display a listing of emergency alerts
     */
    public function index(Request $request)
    {
        $alerts = $this->service->getPaginated($request->all());
        $statistics = $this->service->getStatistics(auth()->user()->branch_id);

        return view('content.emergency.index', [
            'alerts' => $alerts,
            'statistics' => $statistics,
            'filters' => $request->only(['status', 'type', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new emergency alert
     */
    public function create()
    {
        return view('content.emergency.create', [
            'types' => [
                'panic' => 'Panic Button',
                'medical' => 'Medical Emergency',
                'fire' => 'Fire',
                'evacuation' => 'Evacuation',
                'other' => 'Other Emergency',
            ],
        ]);
    }

    /**
     * Store a newly created emergency alert
     */
    public function store(StoreEmergencyAlertRequest $request)
    {
        $alert = $this->service->create($request->validated());

        return redirect()->route('emergency.show', $alert)
            ->with('success', 'Emergency alert created successfully');
    }

    /**
     * Display the specified emergency alert
     */
    public function show(EmergencyAlert $emergencyAlert)
    {
        $emergencyAlert->load(['user', 'branch', 'responder']);

        return view('content.emergency.show', [
            'alert' => new EmergencyAlertResource($emergencyAlert),
        ]);
    }

    /**
     * Show the form for editing the emergency alert
     */
    public function edit(EmergencyAlert $emergencyAlert)
    {
        return view('content.emergency.edit', [
            'alert' => new EmergencyAlertResource($emergencyAlert),
            'types' => [
                'panic' => 'Panic Button',
                'medical' => 'Medical Emergency',
                'fire' => 'Fire',
                'evacuation' => 'Evacuation',
                'other' => 'Other Emergency',
            ],
        ]);
    }

    /**
     * Update the specified emergency alert
     */
    public function update(UpdateEmergencyAlertRequest $request, EmergencyAlert $emergencyAlert)
    {
        $alert = $this->service->update($emergencyAlert, $request->validated());

        return redirect()->route('emergency.show', $alert)
            ->with('success', 'Emergency alert updated successfully');
    }

    /**
     * Remove the specified emergency alert
     */
    public function destroy(EmergencyAlert $emergencyAlert)
    {
        $emergencyAlert->delete();

        return redirect()->route('emergency.index')
            ->with('success', 'Emergency alert deleted successfully');
    }

    /**
     * Respond to emergency alert
     */
    public function respond(EmergencyAlert $emergencyAlert)
    {
        $this->service->respond($emergencyAlert);

        return back()->with('success', 'Emergency alert marked as responded');
    }

    /**
     * Resolve emergency alert
     */
    public function resolve(Request $request, EmergencyAlert $emergencyAlert)
    {
        $this->service->resolve($emergencyAlert, $request->input('response_notes'));

        return back()->with('success', 'Emergency alert resolved');
    }

    /**
     * Cancel emergency alert
     */
    public function cancel(EmergencyAlert $emergencyAlert)
    {
        $this->service->cancel($emergencyAlert);

        return back()->with('success', 'Emergency alert cancelled');
    }

    /**
     * API: Get statistics
     */
    public function statistics()
    {
        $statistics = $this->service->getStatistics(auth()->user()->branch_id);

        return response()->json($statistics);
    }

    /**
     * API: Get active alerts
     */
    public function activeAlerts()
    {
        $alerts = $this->service->getActiveAlerts(auth()->user()->branch_id);

        return EmergencyAlertResource::collection($alerts);
    }
}
