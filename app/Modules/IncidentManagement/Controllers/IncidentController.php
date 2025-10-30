<?php

namespace App\Modules\IncidentManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\IncidentManagement\Models\Incident;
use App\Modules\IncidentManagement\Requests\StoreIncidentRequest;
use App\Modules\IncidentManagement\Requests\UpdateIncidentRequest;
use App\Modules\IncidentManagement\Resources\IncidentResource;
use App\Modules\IncidentManagement\Services\IncidentService;
use App\Modules\IncidentManagement\Repositories\IncidentRepository;
use Illuminate\Http\Request;

class IncidentController extends Controller
{
    public function __construct(
        private IncidentService $service,
        private IncidentRepository $repository
    ) {}

    /**
     * Display a listing of incidents
     */
    public function index(Request $request)
    {
        $filters = $request->only(['type', 'severity', 'status', 'search', 'date_from', 'date_to']);
        $incidents = $this->repository->paginate(15, $filters);

        return view('content.incidents.index', [
            'incidents' => IncidentResource::collection($incidents),
            'filters' => $filters,
            'statistics' => $this->service->getStatistics(auth()->user()->branch_id),
            'mobileNavActive' => 'incidents',
        ]);
    }

    /**
     * Show the form for creating a new incident
     */
    public function create()
    {
        return view('content.incidents.create');
    }

    /**
     * Store a newly created incident
     */
    public function store(StoreIncidentRequest $request)
    {
        $incident = $this->service->createIncident($request->validated());

        return redirect()
            ->route('incidents.show', $incident)
            ->with('success', 'Incident reported successfully.');
    }

    /**
     * Display the specified incident
     */
    public function show(Incident $incident)
    {
        return view('content.incidents.show', [
            'incident' => new IncidentResource(
                $incident->load(['user', 'branch', 'assignedTo', 'witnesses', 'photos'])
            ),
        ]);
    }

    /**
     * Show the form for editing the specified incident
     */
    public function edit(Incident $incident)
    {
        return view('content.incidents.edit', [
            'incident' => new IncidentResource(
                $incident->load(['user', 'branch', 'witnesses', 'photos'])
            ),
        ]);
    }

    /**
     * Update the specified incident
     */
    public function update(UpdateIncidentRequest $request, Incident $incident)
    {
        $incident = $this->service->updateIncident($incident, $request->validated());

        return redirect()
            ->route('incidents.show', $incident)
            ->with('success', 'Incident updated successfully.');
    }

    /**
     * Remove the specified incident
     */
    public function destroy(Incident $incident)
    {
        $this->repository->delete($incident);

        return redirect()
            ->route('incidents.index')
            ->with('success', 'Incident deleted successfully.');
    }

    /**
     * Assign incident to user for investigation
     */
    public function assign(Request $request, Incident $incident)
    {
        $request->validate([
            'assigned_to' => ['required', 'uuid', 'exists:users,id'],
        ]);

        $this->service->assignIncident($incident, $request->assigned_to);

        return back()->with('success', 'Incident assigned successfully.');
    }

    /**
     * Close incident with root cause
     */
    public function close(Request $request, Incident $incident)
    {
        $request->validate([
            'root_cause' => ['required', 'string', 'min:10'],
        ]);

        $this->service->closeIncident($incident, $request->root_cause);

        return back()->with('success', 'Incident closed successfully.');
    }

    /**
     * Delete incident photo
     */
    public function deletePhoto(string $photoId)
    {
        $this->service->deletePhoto($photoId);

        return back()->with('success', 'Photo deleted successfully.');
    }

    /**
     * Get incident statistics (API endpoint)
     */
    public function statistics(Request $request)
    {
        $branchId = $request->user()->branch_id;
        return response()->json($this->service->getStatistics($branchId));
    }

    /**
     * Get recent incidents (API endpoint)
     */
    public function recent(Request $request)
    {
        $incidents = $this->repository->getRecent(10);
        return IncidentResource::collection($incidents);
    }

    /**
     * Get critical incidents (API endpoint)
     */
    public function critical(Request $request)
    {
        $incidents = $this->repository->getCritical();
        return IncidentResource::collection($incidents);
    }
}
