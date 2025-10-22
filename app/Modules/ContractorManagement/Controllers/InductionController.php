<?php

namespace App\Modules\ContractorManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ContractorManagement\Models\InductionModule;
use App\Modules\ContractorManagement\Models\ContractorInduction;
use App\Modules\ContractorManagement\Models\Contractor;
use App\Modules\ContractorManagement\Services\InductionService;
use App\Modules\ContractorManagement\Requests\StoreInductionModuleRequest;
use App\Modules\ContractorManagement\Requests\UpdateInductionModuleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class InductionController extends Controller
{
    protected InductionService $service;

    public function __construct(InductionService $service)
    {
        $this->service = $service;
        $this->middleware('auth');
        $this->middleware('permission:view_induction_modules')->only(['index', 'show']);
        $this->middleware('permission:create_induction_modules')->only(['create', 'store']);
        $this->middleware('permission:edit_induction_modules')->only(['edit', 'update']);
        $this->middleware('permission:delete_induction_modules')->only(['destroy']);
    }

    /**
     * Display a listing of induction modules
     */
    public function index(): View
    {
        $modules = $this->service->getAllForBranch(auth()->user()->branch_id);

        return view('content.contractor-management.inductions.index', compact('modules'));
    }

    /**
     * Show the form for creating a new induction module
     */
    public function create(): View
    {
        return view('content.contractor-management.inductions.create');
    }

    /**
     * Store a newly created induction module
     */
    public function store(StoreInductionModuleRequest $request): RedirectResponse
    {
        try {
            $module = $this->service->createModule($request->validated());

            return redirect()
                ->route('inductions.show', $module)
                ->with('success', 'Induction module created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create induction module: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified induction module
     */
    public function show(InductionModule $induction): View
    {
        $induction->load('contractorInductions.contractor');
        $statistics = $this->service->getModuleStatistics($induction);

        return view('content.contractor-management.inductions.show', [
            'module' => $induction,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Show the form for editing the specified induction module
     */
    public function edit(InductionModule $induction): View
    {
        return view('content.contractor-management.inductions.edit', [
            'module' => $induction,
        ]);
    }

    /**
     * Update the specified induction module
     */
    public function update(UpdateInductionModuleRequest $request, InductionModule $induction): RedirectResponse
    {
        try {
            $module = $this->service->updateModule($induction, $request->validated());

            return redirect()
                ->route('inductions.show', $module)
                ->with('success', 'Induction module updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update induction module: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified induction module
     */
    public function destroy(InductionModule $induction): RedirectResponse
    {
        try {
            $induction->delete();

            return redirect()
                ->route('inductions.index')
                ->with('success', 'Induction module deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete induction module: ' . $e->getMessage());
        }
    }

    /**
     * Start induction for a contractor
     */
    public function start(InductionModule $induction, Contractor $contractor): RedirectResponse
    {
        try {
            $contractorInduction = $this->service->startInduction($contractor, $induction);

            return redirect()
                ->route('contractor-inductions.show', $contractorInduction)
                ->with('success', 'Induction started successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to start induction: ' . $e->getMessage());
        }
    }

    /**
     * Display contractor induction progress
     */
    public function showProgress(ContractorInduction $contractorInduction): View
    {
        $contractorInduction->load(['contractor', 'inductionModule']);

        return view('content.contractor-management.inductions.progress', [
            'induction' => $contractorInduction,
        ]);
    }

    /**
     * Update video progress (AJAX endpoint)
     */
    public function updateVideoProgress(ContractorInduction $contractorInduction): JsonResponse
    {
        try {
            $percentage = request('percentage', 0);
            $induction = $this->service->updateVideoProgress($contractorInduction, $percentage);

            return response()->json([
                'success' => true,
                'induction' => $induction,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Submit quiz attempt (AJAX endpoint)
     */
    public function submitQuiz(ContractorInduction $contractorInduction): JsonResponse
    {
        try {
            $score = request('score');
            $totalQuestions = request('total_questions');

            $induction = $this->service->submitQuiz($contractorInduction, $score, $totalQuestions);

            return response()->json([
                'success' => true,
                'passed' => $induction->quiz_passed,
                'score' => $induction->quiz_score,
                'induction' => $induction,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Complete induction
     */
    public function complete(ContractorInduction $contractorInduction): RedirectResponse
    {
        try {
            $induction = $this->service->completeInduction($contractorInduction);

            return redirect()
                ->route('contractors.show', $induction->contractor)
                ->with('success', 'Induction completed successfully. Certificate generated.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to complete induction: ' . $e->getMessage());
        }
    }

    /**
     * Download certificate
     */
    public function downloadCertificate(ContractorInduction $contractorInduction): RedirectResponse
    {
        // TODO: Implement PDF certificate generation
        return redirect()
            ->back()
            ->with('info', 'Certificate download feature coming soon.');
    }
}
