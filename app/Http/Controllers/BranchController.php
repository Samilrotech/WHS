<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Modules\VehicleManagement\Services\VehicleService;
use App\Modules\InspectionManagement\Models\Inspection;
use Illuminate\Support\Carbon;

class BranchController extends Controller
{
    public function __construct(private VehicleService $vehicleService)
    {
    }


    /**
     * Display a listing of branches
     */
    public function index(Request $request)
    {
        $query = Branch::withCount('users');

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('state', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('postcode', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            if (in_array($status, ['active', 'inactive'], true)) {
                $query->where('is_active', $status === 'active');
            }
        }

        $branches = $query
            ->orderBy('name')
            ->paginate(25) // Increased for dense table view
            ->withQueryString();

        $branchIds = $branches->getCollection()->pluck('id')->filter()->all();

        $inspectionAggregates = empty($branchIds)
            ? collect()
            : Inspection::selectRaw("branch_id,
                COUNT(*) as total,
                SUM(status = 'pending') as pending,
                SUM(status = 'completed') as completed,
                SUM(overall_result IN ('fail_major','fail_critical')) as failed,
                SUM(overall_result IN ('pass','pass_minor')) as passed,
                MAX(inspection_date) as latest_at")
                ->whereIn('branch_id', $branchIds)
                ->groupBy('branch_id')
                ->get()
                ->keyBy('branch_id');

        $branches->getCollection()->transform(function ($branch) use ($inspectionAggregates) {
            $vehicleStats = $this->vehicleService->getStatistics($branch->id);
            $inspectionData = $inspectionAggregates->get($branch->id);

            $total = (int) ($inspectionData->total ?? 0);
            $passed = (int) ($inspectionData->passed ?? 0);
            $failed = (int) ($inspectionData->failed ?? 0);
            $pending = (int) ($inspectionData->pending ?? 0);
            $latestAt = $inspectionData && $inspectionData->latest_at
                ? Carbon::parse($inspectionData->latest_at)
                : null;
            $complianceRate = $total > 0 ? round(($passed / $total) * 100) : null;

            $branch->setAttribute('vehicle_compliance', [
                'vehicles' => $vehicleStats,
                'inspections' => [
                    'total' => $total,
                    'passed' => $passed,
                    'failed' => $failed,
                    'pending' => $pending,
                    'compliance_rate' => $complianceRate,
                    'latest_at' => $latestAt,
                ],
            ]);

            return $branch;
        });

        $statistics = [
            'total' => Branch::count(),
            'active' => Branch::where('is_active', true)->count(),
            'inactive' => Branch::where('is_active', false)->count(),
            'total_employees' => \App\Models\User::whereNotNull('branch_id')->count(),
        ];

        return view('content.branches.index', compact('branches', 'statistics'));
    }


    /**
     * Show the form for creating a new branch
     */
    public function create()
    {
        return view('content.branches.create');
    }

    /**
     * Store a newly created branch
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $validated['is_active'] = $request->has('is_active');

        Branch::create($validated);

        return redirect()->route('branches.index')
            ->with('success', 'Branch created successfully.');
    }

    /**
     * Display the specified branch
     */
    public function show(Branch $branch)
    {
        $branch->loadCount('users')->load(['users' => function ($query) {
            $query->orderBy('name');
        }]);

        $statistics = [
            'total_employees' => $branch->users_count,
            'active_employees' => $branch->users()->where('is_active', true)->count(),
            'managers' => $branch->users()->role('Manager')->count(),
            'employees' => $branch->users()->role('Employee')->count(),
        ];

        return view('content.branches.show', compact('branch', 'statistics'));
    }

    /**
     * Show the form for editing the specified branch
     */
    public function edit(Branch $branch)
    {
        return view('content.branches.edit', compact('branch'));
    }

    /**
     * Update the specified branch
     */
    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate($this->rules($branch));

        $validated['is_active'] = $request->has('is_active');

        $branch->update($validated);

        return redirect()->route('branches.index')
            ->with('success', 'Branch updated successfully.');
    }

    /**
     * Remove the specified branch
     */
    public function destroy(Branch $branch)
    {
        // Check if branch has employees
        if ($branch->users()->count() > 0) {
            return redirect()->route('branches.index')
                ->with('error', 'Cannot delete branch with assigned employees. Please reassign employees first.');
        }

        $branch->delete();

        return redirect()->route('branches.index')
            ->with('success', 'Branch deleted successfully.');
    }

    /**
     * Toggle branch active status
     */
    public function toggleStatus(Branch $branch)
    {
        $branch->update(['is_active' => !$branch->is_active]);

        $status = $branch->is_active ? 'activated' : 'deactivated';

        return redirect()->route('branches.index')
            ->with('success', "Branch {$status} successfully.");
    }

    /**
     * Validation rules shared between store and update actions.
     */
    protected function rules(?Branch $branch = null): array
    {
        $stateKeys = array_keys(config('branch.states', []));

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches', 'name')->ignore($branch?->id),
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('branches', 'code')->ignore($branch?->id),
            ],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => [
                'required',
                'string',
                'max:50',
                Rule::in($stateKeys),
            ],
            'postcode' => ['required', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}



