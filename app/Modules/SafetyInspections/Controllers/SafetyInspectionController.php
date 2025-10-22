<?php

namespace App\Modules\SafetyInspections\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SafetyInspections\Models\SafetyChecklistItem;
use App\Modules\SafetyInspections\Models\SafetyInspection;
use App\Modules\SafetyInspections\Models\SafetyInspectionTemplate;
use App\Modules\SafetyInspections\Services\SafetyInspectionService;
use Illuminate\Http\Request;

class SafetyInspectionController extends Controller
{
    public function __construct(
        protected SafetyInspectionService $safetyInspectionService
    ) {}

    // ============================================================================
    // INSPECTIONS
    // ============================================================================

    /**
     * Display inspections index
     */
    public function index(Request $request)
    {
        $query = SafetyInspection::with(['inspector', 'template', 'branch'])
            ->latest();

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        if ($request->filled('inspector_id')) {
            $query->byInspector($request->inspector_id);
        }

        if ($request->filled('non_compliance')) {
            $query->withNonCompliance();
        }

        if ($request->filled('overdue')) {
            $query->overdue();
        }

        $inspections = $query->paginate(20)->withQueryString();

        // Statistics
        $stats = $this->safetyInspectionService->getInspectionStatistics();

        return view('content.SafetyInspections.Index', [
            'inspections' => $inspections,
            'statistics' => $stats,
            'filters' => $request->only(['status', 'type', 'inspector_id', 'non_compliance', 'overdue']),
        ]);
    }

    /**
     * Show create inspection form
     */
    public function create()
    {
        $templates = SafetyInspectionTemplate::active()->get();
        $inspectors = \App\Models\User::where('branch_id', auth()->user()->branch_id)->get();

        return view('content.SafetyInspections.Create', [
            'templates' => $templates,
            'inspectors' => $inspectors,
        ]);
    }

    /**
     * Store new inspection
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'nullable|exists:safety_inspection_templates,id',
            'inspection_type' => 'required|string',
            'scheduled_date' => 'nullable|date',
            'location' => 'nullable|string',
            'area' => 'nullable|string',
            'asset_tag' => 'nullable|string',
            'vehicle_id' => 'nullable|exists:vehicles,id',
        ]);

        // If template_id provided, use createFromTemplate, otherwise create basic inspection
        if (!empty($validated['template_id'])) {
            $template = SafetyInspectionTemplate::findOrFail($validated['template_id']);
            $inspection = $this->safetyInspectionService->createFromTemplate(
                $template,
                auth()->user(),
                $validated
            );
        } else {
            // Create basic inspection without template
            $inspection = SafetyInspection::create([
                'branch_id' => auth()->user()->branch_id,
                'inspector_id' => auth()->id(),
                'inspection_type' => $validated['inspection_type'],
                'inspection_number' => 'INS-' . date('Y') . '-' . str_pad(SafetyInspection::count() + 1, 4, '0', STR_PAD_LEFT),
                'scheduled_date' => $validated['scheduled_date'] ?? now(),
                'location' => $validated['location'] ?? null,
                'area' => $validated['area'] ?? null,
                'asset_tag' => $validated['asset_tag'] ?? null,
                'vehicle_id' => $validated['vehicle_id'] ?? null,
                'status' => 'scheduled',
            ]);
        }

        return redirect()->route('safety-inspections.show', $inspection)
            ->with('success', 'Inspection created successfully.');
    }

    /**
     * Show inspection details
     */
    public function show(SafetyInspection $inspection)
    {
        $inspection->load(['inspector', 'reviewer', 'template', 'checklistItems', 'vehicle']);

        return view('content.SafetyInspections.Show', [
            'inspection' => $inspection,
        ]);
    }

    /**
     * Show edit inspection form
     */
    public function edit(SafetyInspection $inspection)
    {
        $inspection->load(['template', 'inspector']);
        $templates = SafetyInspectionTemplate::active()->get();
        $inspectors = \App\Models\User::where('branch_id', auth()->user()->branch_id)->get();

        return view('content.SafetyInspections.Edit', [
            'inspection' => $inspection,
            'templates' => $templates,
            'inspectors' => $inspectors,
        ]);
    }

    /**
     * Update inspection
     */
    public function update(Request $request, SafetyInspection $inspection)
    {
        $validated = $request->validate([
            'inspection_type' => 'sometimes|string',
            'scheduled_date' => 'nullable|date',
            'location' => 'nullable|string',
            'area' => 'nullable|string',
            'asset_tag' => 'nullable|string',
            'inspector_notes' => 'nullable|string',
        ]);

        $inspection->update($validated);

        return redirect()->route('safety-inspections.show', $inspection)
            ->with('success', 'Inspection updated successfully.');
    }

    /**
     * Delete inspection
     */
    public function destroy(SafetyInspection $inspection)
    {
        $inspection->delete();

        return redirect()->route('safety-inspections.index')
            ->with('success', 'Inspection deleted successfully.');
    }

    /**
     * Create inspection from template
     */
    public function createFromTemplate(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:safety_inspection_templates,id',
            'scheduled_date' => 'nullable|date',
            'location' => 'nullable|string',
            'area' => 'nullable|string',
            'asset_tag' => 'nullable|string',
            'vehicle_id' => 'nullable|exists:vehicles,id',
        ]);

        $template = SafetyInspectionTemplate::findOrFail($validated['template_id']);

        $inspection = $this->safetyInspectionService->createFromTemplate(
            $template,
            auth()->user(),
            $validated
        );

        return redirect()->route('safety-inspections.show', $inspection)
            ->with('success', 'Inspection created successfully.');
    }

    /**
     * Start inspection
     */
    public function start(SafetyInspection $inspection)
    {
        $this->safetyInspectionService->startInspection($inspection);

        return back()->with('success', 'Inspection started.');
    }

    /**
     * Complete inspection
     */
    public function complete(Request $request, SafetyInspection $inspection)
    {
        $validated = $request->validate([
            'inspector_notes' => 'nullable|string',
            'inspector_signature_path' => 'nullable|string',
        ]);

        $this->safetyInspectionService->completeInspection(
            $inspection,
            $validated['inspector_notes'] ?? null,
            $validated['inspector_signature_path'] ?? null
        );

        return back()->with('success', 'Inspection completed successfully.');
    }

    /**
     * Submit inspection for review
     */
    public function submit(SafetyInspection $inspection)
    {
        $this->safetyInspectionService->submitForReview($inspection);

        return back()->with('success', 'Inspection submitted for review.');
    }

    /**
     * Approve inspection
     */
    public function approve(Request $request, SafetyInspection $inspection)
    {
        $validated = $request->validate([
            'reviewer_comments' => 'nullable|string',
        ]);

        $this->safetyInspectionService->approveInspection(
            $inspection,
            auth()->user(),
            $validated['reviewer_comments'] ?? null
        );

        return back()->with('success', 'Inspection approved.');
    }

    /**
     * Reject inspection
     */
    public function reject(Request $request, SafetyInspection $inspection)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $this->safetyInspectionService->rejectInspection(
            $inspection,
            auth()->user(),
            $validated['rejection_reason']
        );

        return back()->with('success', 'Inspection rejected.');
    }

    /**
     * Escalate inspection
     */
    public function escalate(Request $request, SafetyInspection $inspection)
    {
        $validated = $request->validate([
            'assigned_to_user_id' => 'nullable|exists:users,id',
        ]);

        $assignedTo = $validated['assigned_to_user_id']
            ? \App\Models\User::find($validated['assigned_to_user_id'])
            : null;

        $this->safetyInspectionService->escalateInspection($inspection, $assignedTo);

        return back()->with('success', 'Inspection escalated.');
    }

    // ============================================================================
    // CHECKLIST ITEMS
    // ============================================================================

    /**
     * Record item response
     */
    public function recordItemResponse(Request $request, SafetyChecklistItem $item)
    {
        $validated = $request->validate([
            'result' => 'required|in:pass,fail,na',
            'response_value' => 'nullable',
            'response_notes' => 'nullable|string',
            'photo_urls' => 'nullable|array',
        ]);

        $this->safetyInspectionService->recordItemResponse(
            $item,
            $validated['result'],
            $validated['response_value'] ?? null,
            $validated['response_notes'] ?? null,
            $validated['photo_urls'] ?? []
        );

        return back()->with('success', 'Response recorded.');
    }

    /**
     * Mark item as non-compliant
     */
    public function markItemNonCompliant(Request $request, SafetyChecklistItem $item)
    {
        $validated = $request->validate([
            'severity' => 'required|in:low,medium,high,critical',
            'non_compliance_notes' => 'required|string',
            'corrective_action_required' => 'nullable|string',
            'correction_due_date' => 'nullable|date',
        ]);

        $this->safetyInspectionService->markItemNonCompliant(
            $item,
            $validated['severity'],
            $validated['non_compliance_notes'],
            $validated['corrective_action_required'] ?? null,
            isset($validated['correction_due_date']) ? new \DateTime($validated['correction_due_date']) : null
        );

        return back()->with('success', 'Item marked as non-compliant.');
    }

    // ============================================================================
    // TEMPLATES
    // ============================================================================

    /**
     * Display templates index
     */
    public function templatesIndex(Request $request)
    {
        $query = SafetyInspectionTemplate::with(['creator', 'branch'])
            ->latest();

        // Filters
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('mandatory')) {
            $query->mandatory();
        }

        $templates = $query->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total_templates' => SafetyInspectionTemplate::active()->count(),
            'mandatory_templates' => SafetyInspectionTemplate::active()->mandatory()->count(),
            'total_inspections' => SafetyInspection::count(),
        ];

        return view('content.SafetyInspections/Templates.Index', [
            'templates' => $templates,
            'statistics' => $stats,
            'filters' => $request->only(['category', 'status', 'mandatory']),
        ]);
    }

    /**
     * Store template
     */
    public function templateStore(Request $request)
    {
        $validated = $request->validate([
            'template_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:workplace_safety,equipment_safety,contractor_induction,pre_start_checklist,safety_audit,adhoc_inspection,warehouse_safety,office_safety,vehicle_safety',
            'checklist_items' => 'nullable|array',
            'is_scored' => 'boolean',
            'pass_threshold' => 'nullable|integer|min:0|max:100',
            'is_mandatory' => 'boolean',
            'frequency' => 'nullable|in:daily,weekly,monthly,quarterly,annual,adhoc',
        ]);

        $validated['branch_id'] = auth()->user()->branch_id;
        $validated['created_by_user_id'] = auth()->id();

        $template = SafetyInspectionTemplate::create($validated);

        return back()->with('success', 'Template created successfully.');
    }

    /**
     * Update template
     */
    public function templateUpdate(Request $request, SafetyInspectionTemplate $template)
    {
        $validated = $request->validate([
            'template_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:active,archived,draft',
            'checklist_items' => 'nullable|array',
        ]);

        $template->update($validated);

        return back()->with('success', 'Template updated successfully.');
    }

    // ============================================================================
    // ANALYTICS & REPORTING
    // ============================================================================

    /**
     * Get inspection statistics
     */
    public function statistics(Request $request)
    {
        $filters = $request->only(['branch_id', 'date_from', 'date_to']);

        $stats = $this->safetyInspectionService->getInspectionStatistics($filters);

        return response()->json(['statistics' => $stats]);
    }

    /**
     * Get compliance trends
     */
    public function complianceTrends(Request $request)
    {
        $months = $request->input('months', 6);

        $trends = $this->safetyInspectionService->getComplianceTrends($months);

        return response()->json(['trends' => $trends]);
    }

    /**
     * Get non-compliance summary
     */
    public function nonComplianceSummary()
    {
        $summary = $this->safetyInspectionService->getNonComplianceSummary();

        return response()->json(['summary' => $summary]);
    }

    /**
     * Generate audit trail
     */
    public function auditTrail(SafetyInspection $inspection)
    {
        $trail = $this->safetyInspectionService->generateAuditTrail($inspection);

        return response()->json(['audit_trail' => $trail]);
    }

    /**
     * Schedule recurring inspections
     */
    public function scheduleRecurring(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:safety_inspection_templates,id',
            'inspector_user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'occurrences' => 'required|integer|min:1|max:52',
        ]);

        $template = SafetyInspectionTemplate::findOrFail($validated['template_id']);
        $inspector = \App\Models\User::findOrFail($validated['inspector_user_id']);

        $inspections = $this->safetyInspectionService->scheduleRecurringInspections(
            $template,
            $inspector,
            new \DateTime($validated['start_date']),
            $validated['occurrences']
        );

        return back()->with('success', $inspections->count() . ' inspections scheduled.');
    }
}
