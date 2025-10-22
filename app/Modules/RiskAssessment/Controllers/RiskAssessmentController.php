<?php

namespace App\Modules\RiskAssessment\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RiskAssessment\Models\RiskAssessment;
use App\Modules\RiskAssessment\Requests\StoreRiskAssessmentRequest;
use App\Modules\RiskAssessment\Requests\UpdateRiskAssessmentRequest;
use App\Modules\RiskAssessment\Resources\RiskAssessmentResource;
use App\Modules\RiskAssessment\Services\RiskAssessmentService;
use App\Modules\RiskAssessment\Services\RiskMatrixService;
use Illuminate\Http\Request;

class RiskAssessmentController extends Controller
{
    public function __construct(
        private RiskAssessmentService $service,
        private RiskMatrixService $matrixService
    ) {}

    /**
     * Display a listing of risk assessments
     */
    public function index(Request $request)
    {
        $assessments = $this->service->getPaginated($request->all());
        $statistics = $this->service->getStatistics(auth()->user()->branch_id);

        return view('content.risk.index', [
            'risks' => $assessments,
            'statistics' => $statistics,
            'filters' => $request->only(['category', 'status', 'risk_level', 'search']),
        ]);
    }

    /**
     * Show the risk matrix
     */
    public function matrix()
    {
        $matrixData = $this->matrixService->getMatrixData(auth()->user()->branch_id);
        $statistics = $this->service->getStatistics(auth()->user()->branch_id);

        return view('content.risk-assessment.matrix', [
            'matrixData' => $matrixData,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Show the form for creating a new risk assessment
     */
    public function create()
    {
        return view('content.risk.create', [
            'categories' => [
                'warehouse' => 'Warehouse',
                'pos-installation' => 'POS Installation',
                'on-road' => 'On-Road',
                'office' => 'Office',
                'contractor' => 'Contractor',
            ],
            'hierarchies' => [
                'elimination' => 'Elimination',
                'substitution' => 'Substitution',
                'engineering' => 'Engineering Controls',
                'administrative' => 'Administrative Controls',
                'ppe' => 'Personal Protective Equipment',
            ],
        ]);
    }

    /**
     * Store a newly created risk assessment
     */
    public function store(StoreRiskAssessmentRequest $request)
    {
        $assessment = $this->service->create($request->validated());

        return redirect()->route('risk.show', $assessment)
            ->with('success', 'Risk assessment created successfully');
    }

    /**
     * Display the specified risk assessment
     */
    public function show(RiskAssessment $riskAssessment)
    {
        $riskAssessment->load(['user', 'branch', 'hazards.controlMeasures.responsiblePerson', 'approver']);

        return view('content.risk.show', [
            'assessment' => new RiskAssessmentResource($riskAssessment),
        ]);
    }

    /**
     * Show the form for editing the risk assessment
     */
    public function edit(RiskAssessment $riskAssessment)
    {
        $riskAssessment->load(['hazards.controlMeasures']);

        return view('content.risk.edit', [
            'assessment' => new RiskAssessmentResource($riskAssessment),
            'categories' => [
                'warehouse' => 'Warehouse',
                'pos-installation' => 'POS Installation',
                'on-road' => 'On-Road',
                'office' => 'Office',
                'contractor' => 'Contractor',
            ],
            'hierarchies' => [
                'elimination' => 'Elimination',
                'substitution' => 'Substitution',
                'engineering' => 'Engineering Controls',
                'administrative' => 'Administrative Controls',
                'ppe' => 'Personal Protective Equipment',
            ],
        ]);
    }

    /**
     * Update the specified risk assessment
     */
    public function update(UpdateRiskAssessmentRequest $request, RiskAssessment $riskAssessment)
    {
        $assessment = $this->service->update($riskAssessment, $request->validated());

        return redirect()->route('risk.show', $assessment)
            ->with('success', 'Risk assessment updated successfully');
    }

    /**
     * Remove the specified risk assessment
     */
    public function destroy(RiskAssessment $riskAssessment)
    {
        $riskAssessment->delete();

        return redirect()->route('risk.index')
            ->with('success', 'Risk assessment deleted successfully');
    }

    /**
     * Submit risk assessment for approval
     */
    public function submit(RiskAssessment $riskAssessment)
    {
        $this->service->submit($riskAssessment);

        return back()->with('success', 'Risk assessment submitted for approval');
    }

    /**
     * Approve risk assessment
     */
    public function approve(RiskAssessment $riskAssessment)
    {
        $this->service->approve($riskAssessment);

        return back()->with('success', 'Risk assessment approved');
    }

    /**
     * Reject risk assessment
     */
    public function reject(RiskAssessment $riskAssessment)
    {
        $this->service->reject($riskAssessment);

        return back()->with('success', 'Risk assessment rejected');
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
     * API: Get high risk assessments
     */
    public function highRisk()
    {
        $assessments = $this->service->getHighRisk(auth()->user()->branch_id);

        return RiskAssessmentResource::collection($assessments);
    }
}
