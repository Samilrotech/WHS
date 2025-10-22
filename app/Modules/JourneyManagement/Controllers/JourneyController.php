<?php

namespace App\Modules\JourneyManagement\Controllers;

use App\Modules\JourneyManagement\Models\Journey;
use App\Modules\JourneyManagement\Services\JourneyService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class JourneyController
{
    public function __construct(
        private JourneyService $journeyService
    ) {}

    /**
     * Display a listing of journeys
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'user_id']);
        $perPage = $request->input('per_page', 15);

        $journeys = $this->journeyService->index($filters, $perPage);
        $statistics = $this->journeyService->getStatistics();
        $overdueJourneys = $this->journeyService->getOverdueJourneys();

        return view('content.JourneyManagement.Index', [
            'journeys' => $journeys,
            'statistics' => $statistics,
            'overdueJourneys' => $overdueJourneys,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new journey
     */
    public function create()
    {
        return view('content.JourneyManagement.Create');
    }

    /**
     * Store a newly created journey
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'title' => 'required|string|max:255',
            'purpose' => 'nullable|string',
            'destination' => 'required|string|max:255',
            'destination_address' => 'nullable|string',
            'destination_latitude' => 'nullable|numeric|between:-90,90',
            'destination_longitude' => 'nullable|numeric|between:-180,180',
            'planned_route' => 'nullable|array',
            'estimated_distance_km' => 'nullable|numeric|min:0',
            'estimated_duration_minutes' => 'nullable|integer|min:0',
            'planned_start_time' => 'required|date',
            'planned_end_time' => 'required|date|after:planned_start_time',
            'checkin_interval_minutes' => 'required|integer|min:15|max:480',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'hazards_identified' => 'nullable|string',
            'control_measures' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $journey = $this->journeyService->create($validated);

        return redirect()
            ->route('journey.show', $journey->id)
            ->with('success', 'Journey plan created successfully.');
    }

    /**
     * Display the specified journey
     */
    public function show(Journey $journey)
    {
        $journey->load(['user', 'vehicle', 'checkpoints' => function ($query) {
            $query->orderBy('checkin_time', 'desc');
        }]);

        return view('content.JourneyManagement.Show', [
            'journey' => $journey,
        ]);
    }

    /**
     * Show the form for editing the specified journey
     */
    public function edit(Journey $journey)
    {
        return view('content.JourneyManagement.Edit', [
            'journey' => $journey,
        ]);
    }

    /**
     * Update the specified journey
     */
    public function update(Request $request, Journey $journey): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'title' => 'required|string|max:255',
            'purpose' => 'nullable|string',
            'destination' => 'required|string|max:255',
            'destination_address' => 'nullable|string',
            'destination_latitude' => 'nullable|numeric|between:-90,90',
            'destination_longitude' => 'nullable|numeric|between:-180,180',
            'planned_route' => 'nullable|array',
            'estimated_distance_km' => 'nullable|numeric|min:0',
            'estimated_duration_minutes' => 'nullable|integer|min:0',
            'planned_start_time' => 'required|date',
            'planned_end_time' => 'required|date|after:planned_start_time',
            'checkin_interval_minutes' => 'required|integer|min:15|max:480',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'hazards_identified' => 'nullable|string',
            'control_measures' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $journey->update($validated);

        return redirect()
            ->route('journey.show', $journey->id)
            ->with('success', 'Journey plan updated successfully.');
    }

    /**
     * Remove the specified journey
     */
    public function destroy(Journey $journey): RedirectResponse
    {
        $journey->delete();

        return redirect()
            ->route('journey.index')
            ->with('success', 'Journey deleted successfully.');
    }

    /**
     * Start a journey
     */
    public function start(Journey $journey): RedirectResponse
    {
        if ($journey->status !== 'planned') {
            return back()->with('error', 'Only planned journeys can be started.');
        }

        $this->journeyService->startJourney($journey);

        return redirect()
            ->route('journey.show', $journey->id)
            ->with('success', 'Journey started. Stay safe!');
    }

    /**
     * Complete a journey
     */
    public function complete(Request $request, Journey $journey): RedirectResponse
    {
        if (!$journey->isActive()) {
            return back()->with('error', 'Only active journeys can be completed.');
        }

        $validated = $request->validate([
            'completion_notes' => 'nullable|string',
        ]);

        $this->journeyService->completeJourney(
            $journey,
            $validated['completion_notes'] ?? null
        );

        return redirect()
            ->route('journey.show', $journey->id)
            ->with('success', 'Journey completed successfully.');
    }

    /**
     * Record a check-in checkpoint
     */
    public function checkin(Request $request, Journey $journey): RedirectResponse
    {
        if (!$journey->isActive()) {
            return back()->with('error', 'Cannot check-in for inactive journeys.');
        }

        $validated = $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_name' => 'nullable|string|max:255',
            'status' => 'required|in:ok,assistance_needed,emergency',
            'notes' => 'nullable|string',
            'issues_reported' => 'nullable|string',
            'photo_paths' => 'nullable|array',
            'photo_paths.*' => 'string',
        ]);

        $validated['type'] = 'manual';

        $this->journeyService->recordCheckpoint($journey, $validated);

        return redirect()
            ->route('journey.show', $journey->id)
            ->with('success', 'Check-in recorded successfully.');
    }

    /**
     * Trigger emergency assistance
     */
    public function emergency(Request $request, Journey $journey): RedirectResponse
    {
        if (!$journey->isActive()) {
            return back()->with('error', 'Cannot trigger emergency for inactive journeys.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $this->journeyService->triggerEmergency(
            $journey,
            $validated['notes'] ?? null
        );

        // In production, this would trigger real-time notifications
        // to emergency contacts and supervisors

        return redirect()
            ->route('journey.show', $journey->id)
            ->with('success', 'Emergency assistance has been requested. Help is on the way.');
    }
}
