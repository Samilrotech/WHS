<?php

namespace App\Modules\CAPAManagement\Controllers;

use App\Modules\CAPAManagement\Models\CAPA;
use App\Modules\CAPAManagement\Models\CAPAAction;
use App\Modules\CAPAManagement\Services\CAPAService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class CAPAController
{
    public function __construct(
        private CAPAService $capaService
    ) {}

    /**
     * Display a listing of CAPAs
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'type', 'priority', 'assigned_to_user_id']);
        $perPage = $request->input('per_page', 15);

        $capas = $this->capaService->index($filters, $perPage);
        $statistics = $this->capaService->getStatistics();
        $metrics = $this->capaService->getMetrics();
        $overdueCAPAs = $this->capaService->getOverdueCAPAs();
        $pendingApproval = $this->capaService->getPendingApproval();

        return view('content.CAPAManagement.Index', [
            'capas' => $capas,
            'statistics' => $statistics,
            'metrics' => $metrics,
            'overdueCAPAs' => $overdueCAPAs,
            'pendingApproval' => $pendingApproval,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new CAPA
     */
    public function create()
    {
        return view('content.CAPAManagement.Create');
    }

    /**
     * Store a newly created CAPA
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'incident_id' => 'nullable|exists:incidents,id',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'type' => 'required|in:corrective,preventive',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'problem_statement' => 'nullable|string',
            'root_cause_analysis' => 'nullable|string',
            'five_whys' => 'nullable|string',
            'contributing_factors' => 'nullable|string',
            'proposed_action' => 'required|string',
            'implementation_steps' => 'nullable|array',
            'resources_required' => 'nullable|string',
            'estimated_cost' => 'nullable|numeric|min:0',
            'target_completion_date' => 'required|date|after:today',
            'estimated_hours' => 'nullable|integer|min:0',
            'priority' => 'required|in:low,medium,high,critical',
            'notes' => 'nullable|string',
        ]);

        $capa = $this->capaService->create($validated);

        return redirect()
            ->route('capa.show', $capa->id)
            ->with('success', 'CAPA created successfully.');
    }

    /**
     * Display the specified CAPA
     */
    public function show(CAPA $capa)
    {
        $capa->load([
            'raisedBy',
            'assignedTo',
            'verifiedBy',
            'approvedBy',
            'closedBy',
            'incident',
            'actions' => function ($query) {
                $query->orderBy('sequence_order', 'asc');
            },
            'actions.assignedTo',
            'actions.completedBy',
        ]);

        return view('content.CAPAManagement.Show', [
            'capa' => $capa,
        ]);
    }

    /**
     * Show the form for editing the specified CAPA
     */
    public function edit(CAPA $capa)
    {
        return view('content.CAPAManagement.Edit', [
            'capa' => $capa,
        ]);
    }

    /**
     * Update the specified CAPA
     */
    public function update(Request $request, CAPA $capa): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'problem_statement' => 'nullable|string',
            'root_cause_analysis' => 'nullable|string',
            'five_whys' => 'nullable|string',
            'contributing_factors' => 'nullable|string',
            'proposed_action' => 'required|string',
            'implementation_steps' => 'nullable|array',
            'resources_required' => 'nullable|string',
            'estimated_cost' => 'nullable|numeric|min:0',
            'target_completion_date' => 'required|date',
            'estimated_hours' => 'nullable|integer|min:0',
            'priority' => 'required|in:low,medium,high,critical',
            'notes' => 'nullable|string',
        ]);

        $capa->update($validated);

        return redirect()
            ->route('capa.show', $capa->id)
            ->with('success', 'CAPA updated successfully.');
    }

    /**
     * Remove the specified CAPA
     */
    public function destroy(CAPA $capa): RedirectResponse
    {
        $capa->delete();

        return redirect()
            ->route('capa.index')
            ->with('success', 'CAPA deleted successfully.');
    }

    /**
     * Submit CAPA for approval
     */
    public function submit(CAPA $capa): RedirectResponse
    {
        if ($capa->status !== 'draft') {
            return back()->with('error', 'Only draft CAPAs can be submitted.');
        }

        $this->capaService->submit($capa);

        return redirect()
            ->route('capa.show', $capa->id)
            ->with('success', 'CAPA submitted for approval.');
    }

    /**
     * Approve CAPA
     */
    public function approve(Request $request, CAPA $capa): RedirectResponse
    {
        if ($capa->status !== 'submitted') {
            return back()->with('error', 'Only submitted CAPAs can be approved.');
        }

        $validated = $request->validate([
            'approval_notes' => 'nullable|string',
        ]);

        $this->capaService->approve($capa, $validated['approval_notes'] ?? null);

        return redirect()
            ->route('capa.show', $capa->id)
            ->with('success', 'CAPA approved successfully.');
    }

    /**
     * Reject CAPA
     */
    public function reject(Request $request, CAPA $capa): RedirectResponse
    {
        if ($capa->status !== 'submitted') {
            return back()->with('error', 'Only submitted CAPAs can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $this->capaService->reject($capa, $validated['rejection_reason']);

        return redirect()
            ->route('capa.show', $capa->id)
            ->with('success', 'CAPA rejected.');
    }

    /**
     * Start CAPA implementation
     */
    public function start(CAPA $capa): RedirectResponse
    {
        if ($capa->status !== 'approved') {
            return back()->with('error', 'Only approved CAPAs can be started.');
        }

        $this->capaService->startImplementation($capa);

        return redirect()
            ->route('capa.show', $capa->id)
            ->with('success', 'CAPA implementation started.');
    }

    /**
     * Complete CAPA implementation
     */
    public function complete(CAPA $capa): RedirectResponse
    {
        if ($capa->status !== 'in_progress') {
            return back()->with('error', 'Only in-progress CAPAs can be completed.');
        }

        $this->capaService->completeImplementation($capa);

        return redirect()
            ->route('capa.show', $capa->id)
            ->with('success', 'CAPA implementation completed. Awaiting verification.');
    }

    /**
     * Verify CAPA effectiveness
     */
    public function verify(Request $request, CAPA $capa): RedirectResponse
    {
        if ($capa->status !== 'completed') {
            return back()->with('error', 'Only completed CAPAs can be verified.');
        }

        $validated = $request->validate([
            'verification_method' => 'nullable|string',
            'verification_results' => 'required|string',
            'effectiveness_confirmed' => 'required|boolean',
        ]);

        $this->capaService->verify($capa, $validated);

        return redirect()
            ->route('capa.show', $capa->id)
            ->with('success', 'CAPA verified successfully.');
    }

    /**
     * Close CAPA
     */
    public function close(Request $request, CAPA $capa): RedirectResponse
    {
        if ($capa->status !== 'verified') {
            return back()->with('error', 'Only verified CAPAs can be closed.');
        }

        $validated = $request->validate([
            'closure_notes' => 'nullable|string',
        ]);

        $this->capaService->close($capa, $validated['closure_notes'] ?? null);

        return redirect()
            ->route('capa.show', $capa->id)
            ->with('success', 'CAPA closed successfully.');
    }

    /**
     * Create action for CAPA
     */
    public function createAction(Request $request, CAPA $capa): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'sequence_order' => 'nullable|integer|min:1',
        ]);

        $this->capaService->createAction($capa, $validated);

        return redirect()
            ->route('capa.show', $capa->id)
            ->with('success', 'Action created successfully.');
    }

    /**
     * Complete action
     */
    public function completeAction(Request $request, CAPAAction $action): RedirectResponse
    {
        if ($action->is_completed) {
            return back()->with('error', 'Action is already completed.');
        }

        $validated = $request->validate([
            'completion_notes' => 'nullable|string',
            'evidence_paths' => 'nullable|array',
            'evidence_paths.*' => 'string',
        ]);

        $this->capaService->completeAction($action, $validated);

        return redirect()
            ->route('capa.show', $action->capa_id)
            ->with('success', 'Action completed successfully.');
    }
}
