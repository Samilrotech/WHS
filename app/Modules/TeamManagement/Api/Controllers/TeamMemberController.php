<?php

namespace App\Modules\TeamManagement\Api\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\TeamManagement\Api\Resources\UserCollection;
use App\Modules\TeamManagement\Api\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TeamMemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * List team members with pagination and filters.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive,on_leave',
            'branch' => 'nullable|uuid|exists:branches,id',
            'role' => 'nullable|string|in:employee,supervisor,manager,admin',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = User::query()
            ->with(['branch', 'roles']);

        // Branch-level access control
        if (!$request->user()->tokenCan('*')) {
            // Non-admin users can only see their branch
            $query->where('branch_id', $request->user()->branch_id);
        }

        // Apply search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Apply employment status filter
        if ($status = $request->input('status')) {
            $query->where('employment_status', $status);
        }

        // Apply branch filter (admin only)
        if ($branchId = $request->input('branch')) {
            if ($request->user()->tokenCan('*')) {
                $query->where('branch_id', $branchId);
            }
        }

        // Apply role filter
        if ($roleKey = $request->input('role')) {
            $roleName = $this->mapRoleKeyToName($roleKey);
            $query->whereHas('roles', function ($q) use ($roleName) {
                $q->where('name', $roleName);
            });
        }

        // Pagination
        $perPage = $request->input('per_page', 25);
        $members = $query->orderBy('name', 'asc')->paginate($perPage);

        return (new UserCollection($members))->response();
    }

    /**
     * Show single team member details.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $member = User::with(['branch', 'roles', 'currentVehicleAssignment.vehicle'])
            ->findOrFail($id);

        // Branch-level access control
        if (!$request->user()->tokenCan('*')) {
            if ($member->branch_id !== $request->user()->branch_id) {
                abort(403, 'You can only view members from your branch.');
            }
        }

        return (new UserResource($member))->response();
    }

    /**
     * Map role key to Spatie role name.
     */
    protected function mapRoleKeyToName(string $key): string
    {
        $roleMap = [
            'employee' => 'Employee',
            'supervisor' => 'Supervisor',
            'manager' => 'Manager',
            'admin' => 'Admin',
        ];

        return $roleMap[$key] ?? 'Employee';
    }
}
