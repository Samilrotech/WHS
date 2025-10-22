<?php

namespace App\Modules\ContractorManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ContractorManagement\Models\Visitor;
use App\Modules\ContractorManagement\Services\VisitorService;
use App\Modules\ContractorManagement\Services\SignInService;
use App\Modules\ContractorManagement\Requests\StoreVisitorRequest;
use App\Modules\ContractorManagement\Requests\UpdateVisitorRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\User;

class VisitorController extends Controller
{
    protected VisitorService $service;
    protected SignInService $signInService;

    public function __construct(VisitorService $service, SignInService $signInService)
    {
        $this->service = $service;
        $this->signInService = $signInService;
        $this->middleware('auth');
        $this->middleware('permission:view_visitors')->only(['index', 'show']);
        $this->middleware('permission:create_visitors')->only(['create', 'store']);
        $this->middleware('permission:edit_visitors')->only(['edit', 'update']);
        $this->middleware('permission:delete_visitors')->only(['destroy']);
    }

    /**
     * Display a listing of visitors
     */
    public function index(): View
    {
        $visitors = $this->service->getAllForBranch(auth()->user()->branch_id);
        $onSite = $this->service->getOnSite();
        $expected = $this->service->getExpectedToday();
        $overdue = $this->service->getOverdue();

        return view('content.contractor-management.visitors.index', compact(
            'visitors',
            'onSite',
            'expected',
            'overdue'
        ));
    }

    /**
     * Show the form for creating a new visitor
     */
    public function create(): View
    {
        $hosts = User::where('branch_id', auth()->user()->branch_id)
            ->where('status', 'active')
            ->get();

        return view('content.contractor-management.visitors.create', compact('hosts'));
    }

    /**
     * Store a newly created visitor
     */
    public function store(StoreVisitorRequest $request): RedirectResponse
    {
        try {
            $visitor = $this->service->create($request->validated());

            return redirect()
                ->route('visitors.show', $visitor)
                ->with('success', 'Visitor created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create visitor: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified visitor
     */
    public function show(Visitor $visitor): View
    {
        $visitor->load([
            'host',
            'briefer',
            'signInLogs' => function ($query) {
                $query->latest()->limit(10);
            },
        ]);

        return view('content.contractor-management.visitors.show', [
            'visitor' => $visitor,
        ]);
    }

    /**
     * Show the form for editing the specified visitor
     */
    public function edit(Visitor $visitor): View
    {
        $hosts = User::where('branch_id', auth()->user()->branch_id)
            ->where('status', 'active')
            ->get();

        return view('content.contractor-management.visitors.edit', [
            'visitor' => $visitor,
            'hosts' => $hosts,
        ]);
    }

    /**
     * Update the specified visitor
     */
    public function update(UpdateVisitorRequest $request, Visitor $visitor): RedirectResponse
    {
        try {
            $visitor = $this->service->update($visitor, $request->validated());

            return redirect()
                ->route('visitors.show', $visitor)
                ->with('success', 'Visitor updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update visitor: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified visitor
     */
    public function destroy(Visitor $visitor): RedirectResponse
    {
        try {
            $this->service->delete($visitor);

            return redirect()
                ->route('visitors.index')
                ->with('success', 'Visitor deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete visitor: ' . $e->getMessage());
        }
    }

    /**
     * Complete safety briefing for visitor
     */
    public function completeBriefing(Visitor $visitor): RedirectResponse
    {
        try {
            $this->service->completeSafetyBriefing($visitor);

            return redirect()
                ->route('visitors.show', $visitor)
                ->with('success', 'Safety briefing completed successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to complete safety briefing: ' . $e->getMessage());
        }
    }

    /**
     * Record visitor arrival
     */
    public function recordArrival(Visitor $visitor): RedirectResponse
    {
        try {
            $badgeNumber = request('badge_number');
            $this->service->recordArrival($visitor, $badgeNumber);

            return redirect()
                ->route('visitors.show', $visitor)
                ->with('success', 'Visitor arrival recorded successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to record arrival: ' . $e->getMessage());
        }
    }

    /**
     * Record visitor departure
     */
    public function recordDeparture(Visitor $visitor): RedirectResponse
    {
        try {
            $this->service->recordDeparture($visitor);

            return redirect()
                ->route('visitors.show', $visitor)
                ->with('success', 'Visitor departure recorded successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to record departure: ' . $e->getMessage());
        }
    }

    /**
     * Cancel visitor visit
     */
    public function cancel(Visitor $visitor): RedirectResponse
    {
        try {
            $reason = request('reason', 'No reason provided');
            $this->service->cancel($visitor, $reason);

            return redirect()
                ->route('visitors.show', $visitor)
                ->with('success', 'Visitor visit cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to cancel visit: ' . $e->getMessage());
        }
    }

    /**
     * Sign in visitor
     */
    public function signIn(Visitor $visitor): RedirectResponse
    {
        try {
            $signInData = request()->only([
                'location',
                'purpose',
                'work_description',
                'areas_accessed',
                'ppe_acknowledged',
                'emergency_procedures_acknowledged',
                'ppe_items',
                'entry_method',
            ]);

            $this->signInService->signInVisitor($visitor, $signInData);

            return redirect()
                ->route('visitors.show', $visitor)
                ->with('success', 'Visitor signed in successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to sign in visitor: ' . $e->getMessage());
        }
    }

    /**
     * Display visitor dashboard
     */
    public function dashboard(): View
    {
        $statistics = $this->service->getStatistics(auth()->user()->branch_id);
        $onSite = $this->service->getOnSite();
        $expected = $this->service->getExpectedToday();
        $overdue = $this->service->getOverdue();

        return view('content.contractor-management.visitors.dashboard', compact(
            'statistics',
            'onSite',
            'expected',
            'overdue'
        ));
    }
}
