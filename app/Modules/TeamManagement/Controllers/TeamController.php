<?php

namespace App\Modules\TeamManagement\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use App\Modules\InspectionManagement\Models\Inspection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeamController extends Controller
{
    /**
     * Map of request role keys to Spatie role names.
     *
     * @var array<string, string>
     */
    protected array $roleMap = [
        'employee' => 'Employee',
        'supervisor' => 'Supervisor',
        'manager' => 'Manager',
        'admin' => 'Admin',
    ];

    public function __construct()
    {
        $this->middleware(['role:Admin|Manager']);
    }

    /**
     * Display team members index.
     */
    public function index(Request $request): View
    {
        $currentUser = $request->user();

        $filters = [
            'search' => trim((string) $request->input('search')),
            'branch' => $request->input('branch'),
            'role' => $request->input('role'),
            'status' => $request->input('status'),
        ];

        // Define allowed sort columns (whitelist) - SQL injection protection
        $allowedSortColumns = [
            'name',
            'email',
            'phone',
            'employee_id',
            'employment_status',
            'created_at',
            'updated_at',
            'branch_id',
        ];

        // Get sort parameters with validation
        $sortColumn = $request->input('sort', 'name');
        $sortDirection = strtolower($request->input('direction', 'asc'));

        // Validate against whitelist (strict type checking prevents type juggling attacks)
        $sortColumn = in_array($sortColumn, $allowedSortColumns, true) ? $sortColumn : 'name';
        $sortDirection = in_array($sortDirection, ['asc', 'desc'], true) ? $sortDirection : 'asc';

        // Pagination: Support 25, 50, 100 items per page with default of 25
        $perPage = (int) $request->input('per_page', 25);
        $perPage = in_array($perPage, [25, 50, 100], true) ? $perPage : 25;

        $query = User::query()
            ->with(['branch', 'roles', 'currentVehicleAssignment.vehicle'])
            ->withCount(['incidents']);

        if (!$currentUser->isAdmin()) {
            $query->where('branch_id', $currentUser->branch_id);
        }

        if ($filters['search'] !== '') {
            $query->where(function ($builder) use ($filters) {
                $builder->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%")
                    ->orWhere('employee_id', 'like', "%{$filters['search']}%")
                    ->orWhere('phone', 'like', "%{$filters['search']}%" );
            });
        }

        if ($filters['branch']) {
            $query->where('branch_id', $filters['branch']);
        }

        if ($filters['status'] && in_array($filters['status'], ['active', 'inactive', 'on_leave'], true)) {
            $query->where('employment_status', $filters['status']);
        }

        if ($filters['role'] && array_key_exists(strtolower($filters['role']), $this->roleMap)) {
            $roleName = $this->roleMap[strtolower($filters['role'])];
            $query->whereHas('roles', fn ($roleQuery) => $roleQuery->where('name', $roleName));
        }

        /** @var LengthAwarePaginator $users */
        $users = $query->orderBy($sortColumn, $sortDirection)
            ->paginate($perPage)->withQueryString();

        $userIds = $users->getCollection()->pluck('id')->all();

        $latestDriverInspections = empty($userIds)
            ? collect()
            : Inspection::with(['vehicle', 'vehicle.branch'])
                ->whereIn('inspector_user_id', $userIds)
                ->orderByDesc('inspection_date')
                ->orderByDesc('created_at')
                ->get()
                ->unique('inspector_user_id')
                ->keyBy('inspector_user_id');

        $memberCollection = $users->getCollection()->map(fn (User $member) => $this->formatMemberSummary($member, $latestDriverInspections->get($member->id)));

        // Debug: Log collection count
        \Log::info('TeamManagement Index Debug', [
            'total_from_paginator' => $users->total(),
            'collection_count' => $memberCollection->count(),
            'per_page' => $users->perPage(),
            'current_page' => $users->currentPage(),
        ]);

        $members = [
            'data' => $memberCollection->all(),
            'total' => $users->total(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
        ];

        $statisticsQuery = User::query();
        if (!$currentUser->isAdmin()) {
            $statisticsQuery->where('branch_id', $currentUser->branch_id);
        }

        $statistics = [
            'total_members' => (clone $statisticsQuery)->count(),
            'active_members' => (clone $statisticsQuery)->where('employment_status', 'active')->count(),
            'on_leave' => (clone $statisticsQuery)->where('employment_status', 'on_leave')->count(),
            'certifications_expiring' => 0,
        ];

        $branches = Branch::active()->orderBy('name')->get(['id', 'name']);

        // Feature flag: Dense Table UI (3-phase rollout)
        $useDenseTable = \Laravel\Pennant\Feature::for($currentUser)->active('dense-table');

        return view('content.TeamManagement.Index', [
            'members' => $members,
            'statistics' => $statistics,
            'filters' => $filters,
            'branches' => $branches,
            'paginator' => $users,
            'useDenseTable' => $useDenseTable,
        ]);
    }

    /**
     * Show the form for creating a new team member.
     */
    public function create(): View
    {
        return view('content.TeamManagement.Create', [
            'branches' => Branch::active()->orderBy('name')->get(['id', 'name']),
            'roles' => $this->roleOptions(),
        ]);
    }

    /**
     * Store a newly created team member.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateMember($request);

        $roleName = $this->resolveRoleName($validated['role']);
        $shouldSendWelcome = $request->boolean('send_welcome_email');

        $user = DB::transaction(function () use ($validated, $roleName) {
            $user = User::create([
                'branch_id' => $validated['branch_id'] ?: null,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'],
                'employee_id' => $validated['employee_id'],
                'position' => $validated['position'],
                'is_active' => (bool) $validated['is_active'],
                'employment_status' => $validated['is_active'] ? 'active' : 'inactive',
                'employment_start_date' => $validated['employment_start_date'] ?? null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $user->syncRoles([$roleName]);

            return $user;
        });

        if ($shouldSendWelcome) {
            Password::sendResetLink(['email' => $user->email]);
        }

        return redirect()
            ->route('teams.show', $user)
            ->with('success', 'Team member created successfully.');
    }

    /**
     * Display the specified team member.
     */
    public function show(User $team): View
    {
        $this->ensureBranchAccess($team);

        $team->loadMissing(['branch', 'roles', 'currentVehicleAssignment.vehicle'])->loadCount(['incidents']);

        $latestInspection = Inspection::with(['vehicle', 'vehicle.branch'])
            ->where('inspector_user_id', $team->id)
            ->orderByDesc('inspection_date')
            ->orderByDesc('created_at')
            ->first();

        $recentInspections = Inspection::with(['vehicle'])
            ->where('inspector_user_id', $team->id)
            ->orderByDesc('inspection_date')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function (Inspection $inspection) {
                return [
                    'id' => $inspection->id,
                    'number' => $inspection->inspection_number,
                    'date' => optional($inspection->inspection_date)->format('d/m/Y'),
                    'date_human' => optional($inspection->inspection_date)->diffForHumans(),
                    'result' => $inspection->overall_result,
                    'status' => $inspection->status,
                    'vehicle_registration' => $inspection->vehicle?->registration_number,
                ];
            });

        return view('content.TeamManagement.Show', [
            'member' => $this->formatMemberDetail($team, $latestInspection),
            'recent_inspections' => $recentInspections,
        ]);
    }

    /**
     * Show the form for editing the specified team member.
     */
    public function edit(User $team): View
    {
        $this->ensureBranchAccess($team);

        $team->loadMissing(['branch', 'roles']);

        return view('content.TeamManagement.Edit', [
            'member' => $this->formatMemberForm($team),
            'branches' => Branch::active()->orderBy('name')->get(['id', 'name']),
            'roles' => $this->roleOptions(),
        ]);
    }

    /**
     * Update the specified team member.
     */
    public function update(Request $request, User $team): RedirectResponse
    {
        $this->ensureBranchAccess($team);

        $validated = $this->validateMember($request, $team);
        $roleName = $this->resolveRoleName($validated['role']);

        // Optimistic locking check (PWA sync conflict detection)
        if (isset($validated['version']) && $team->version !== $validated['version']) {
            // AJAX request from PWA - return JSON with conflict data
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Conflict detected',
                    'message' => 'This record was modified by another user',
                    'server_version' => $team->version,
                    'server_data' => $team->toArray(),
                    'client_version' => $validated['version'],
                ], 409); // HTTP 409 Conflict
            }

            // Web form submission - redirect with error
            return redirect()
                ->back()
                ->withErrors(['version' => 'This record was modified by another user. Please refresh and try again.'])
                ->withInput();
        }

        DB::transaction(function () use ($team, $validated, $roleName) {
            $attributes = [
                'branch_id' => $validated['branch_id'] ?: null,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'employee_id' => $validated['employee_id'],
                'position' => $validated['position'],
                'is_active' => (bool) $validated['is_active'],
                'employment_status' => $validated['is_active'] ? 'active' : ($team->employment_status === 'on_leave' ? 'on_leave' : 'inactive'),
                'employment_start_date' => $validated['employment_start_date'] ?? null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ];

            if (!empty($validated['password'])) {
                $attributes['password'] = Hash::make($validated['password']);
            }

            // Increment version for optimistic locking
            $team->update($attributes);
            $team->increment('version');
            $team->syncRoles([$roleName]);
        });

        return redirect()
            ->route('teams.show', $team)
            ->with('success', 'Team member updated successfully.');
    }

    /**
     * Remove the specified team member.
     */
    public function destroy(User $team): RedirectResponse
    {
        $this->ensureBranchAccess($team);

        if ($team->id === auth()->id()) {
            return redirect()->route('teams.show', $team)
                ->with('error', 'You cannot delete your own account.');
        }

        if ($team->vehicleAssignments()->whereNull('returned_date')->exists()) {
            return redirect()->route('teams.show', $team)
                ->with('error', 'Cannot delete member while they have an active vehicle assignment.');
        }

        $team->delete();

        return redirect()->route('teams.index')
            ->with('success', 'Team member deleted successfully.');
    }

    /**
     * Mark the specified team member as on leave.
     */
    public function markOnLeave(User $team): RedirectResponse
    {
        $this->ensureBranchAccess($team);

        if ($team->vehicleAssignments()->whereNull('returned_date')->exists()) {
            return back()->with('error', 'Cannot mark member on leave while they have an active vehicle assignment.');
        }

        $team->update([
            'is_active' => false,
            'employment_status' => 'on_leave',
        ]);

        return back()->with('success', 'Team member marked as on leave.');
    }

    /**
     * Activate the specified team member.
     */
    public function activate(User $team): RedirectResponse
    {
        $this->ensureBranchAccess($team);

        $team->update([
            'is_active' => true,
            'employment_status' => 'active',
        ]);

        return back()->with('success', 'Team member activated.');
    }

    /**
     * Deactivate the specified team member.
     */
    public function deactivate(User $team): RedirectResponse
    {
        $this->ensureBranchAccess($team);

        $team->update([
            'is_active' => false,
            'employment_status' => 'inactive',
        ]);

        return back()->with('success', 'Team member deactivated.');
    }

    /**
     * Send a password reset link to the specified team member.
     */
    public function sendResetLink(User $team): RedirectResponse
    {
        $this->ensureBranchAccess($team);

        $status = Password::sendResetLink(['email' => $team->email]);

        return back()->with($status === Password::RESET_LINK_SENT ? 'success' : 'error', __($status));
    }

    /**
     * Get employee quick view data via AJAX
     */
    public function quickView(User $team): JsonResponse
    {
        $this->ensureBranchAccess($team);

        // Eager load relationships and counts
        $team->loadMissing(['branch', 'roles', 'currentVehicleAssignment.vehicle'])
             ->loadCount(['incidents']);

        // Fetch latest inspection for this user
        $latestInspection = Inspection::with(['vehicle', 'vehicle.branch'])
            ->where('inspector_user_id', $team->id)
            ->orderByDesc('inspection_date')
            ->orderByDesc('created_at')
            ->first();

        // Use existing formatMemberSummary logic
        $memberData = $this->formatMemberSummary($team, $latestInspection);

        // Add human-readable last active
        $memberData['last_active_human'] = $team->last_login_at
            ? $team->last_login_at->diffForHumans()
            : ($team->updated_at ? $team->updated_at->diffForHumans() : 'No data');

        // Render the partial view
        $html = view('content.TeamManagement.partials.quick-view', [
            'member' => $memberData
        ])->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'member_id' => $team->id,
            'view_url' => route('teams.show', $team),
            'edit_url' => route('teams.edit', $team)
        ]);
    }

    /**
     * Validate request data for create/update actions.
     */
    protected function validateMember(Request $request, ?User $team = null): array
    {
        $roleKeys = array_keys($this->roleMap);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'employee_id' => ['required', 'string', 'max:50', Rule::unique('users', 'employee_id')->ignore($team?->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($team?->id)],
            'phone' => ['required', 'string', 'max:20'],
            'position' => ['required', 'string', 'max:255'],
            'branch_id' => ['nullable', 'uuid', Rule::exists('branches', 'id')],
            'role' => ['required', Rule::in($roleKeys)],
            'is_active' => ['required', Rule::in(['0', '1', 0, 1, true, false])],
            'employment_start_date' => ['nullable', 'date'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ];

        if ($team) {
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
            $rules['version'] = ['nullable', 'integer']; // For optimistic locking (PWA sync)
        } else {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        if (!$request->filled('branch_id')) {
            $request->merge(['branch_id' => null]);
        }

        if (!$request->filled('employment_start_date')) {
            $request->merge(['employment_start_date' => null]);
        }

        $data = $request->validate($rules);
        $data['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);

        return $data;
    }

    /**
     * Format summary payload for index cards.
     */
    protected function formatMemberSummary(User $member, ?Inspection $latestInspection = null): array
    {
        $role = strtolower($member->getRoleNames()->first() ?? 'employee');
        $assignment = $member->currentVehicleAssignment;
        $vehicle = $assignment?->vehicle;

        $currentVehicle = $vehicle ? [
            'id' => $vehicle->id,
            'registration_number' => $vehicle->registration_number,
            'make' => $vehicle->make,
            'model' => $vehicle->model,
            'branch' => $vehicle->branch?->name,
            'assigned_date' => optional($assignment->assigned_date)->format('Y-m-d'),
            'assigned_human' => optional($assignment->assigned_date)->diffForHumans(),
        ] : null;

        $inspectionData = null;
        if ($latestInspection) {
            $inspectionData = [
                'id' => $latestInspection->id,
                'number' => $latestInspection->inspection_number,
                'date' => optional($latestInspection->inspection_date)->format('Y-m-d'),
                'date_human' => optional($latestInspection->inspection_date)->diffForHumans(),
                'status' => $latestInspection->status,
                'result' => $latestInspection->overall_result,
                'vehicle_registration' => $latestInspection->vehicle?->registration_number,
            ];
        }

        return [
            'id' => $member->id,
            'employee_id' => $member->employee_id ?? 'N/A',
            'name' => $member->name,
            'email' => $member->email,
            'phone' => $member->phone ?? 'N/A',
            'branch_name' => $member->branch?->name ?? 'Unassigned',
            'role' => $role,
            'status' => $member->employment_status ?? ($member->is_active ? 'active' : 'inactive'),
            'is_active' => (bool) $member->is_active,
            'certifications_count' => 0,
            'has_expiring_certs' => false,
            'last_active' => optional($member->updated_at)->toIso8601String() ?? now()->toIso8601String(),
            'current_vehicle' => $currentVehicle,
            'latest_inspection' => $inspectionData,
        ];
    }

    /**
     * Format detailed payload for the profile view.
     */
    protected function formatMemberDetail(User $member, ?Inspection $latestInspection = null): array
    {
        $summary = $this->formatMemberSummary($member, $latestInspection);
        $lastActivity = $member->updated_at ?? $member->created_at ?? now();
        $roleName = $member->getRoleNames()->first() ?? 'Employee';
        $employmentStatus = $member->employment_status ?? ($member->is_active ? 'active' : 'inactive');

        return array_merge($summary, [
            'position' => $member->position,
            'role_label' => $roleName,
            'employment_status' => $employmentStatus,
            'employment_start_date' => optional($member->employment_start_date)->format('d/m/Y'),
            'notes' => $member->notes,
            'created_at' => optional($member->created_at)->format('d/m/Y H:i'),
            'updated_at' => optional($member->updated_at)->format('d/m/Y H:i'),
            'last_login' => $lastActivity->format('d/m/Y H:i'),
            'days_since_login' => $lastActivity->diffInDays(now()),
            'last_active' => $lastActivity->toIso8601String(),
            'incidents_count' => $member->incidents_count ?? $member->incidents()->count(),
            'training_count' => 0,
            'certifications_count' => 0,
        ]);
    }

    /**
     * Format payload for edit form consumption.
     */
    protected function formatMemberForm(User $member): array
    {
        $roleKey = strtolower($member->getRoleNames()->first() ?? 'employee');
        $lastActivity = $member->updated_at ?? $member->created_at ?? now();

        return [
            'id' => $member->id,
            'name' => $member->name,
            'employee_id' => $member->employee_id,
            'email' => $member->email,
            'phone' => $member->phone,
            'position' => $member->position,
            'branch_id' => $member->branch_id,
            'branch_name' => $member->branch?->name ?? 'Unassigned',
            'role' => $roleKey,
            'role_label' => $this->roleMap[$roleKey] ?? ucfirst($roleKey),
            'is_active' => (int) $member->is_active,
            'employment_status' => $member->employment_status ?? ($member->is_active ? 'active' : 'inactive'),
            'employment_start_date' => optional($member->employment_start_date)->format('Y-m-d'),
            'emergency_contact_name' => $member->emergency_contact_name,
            'emergency_contact_phone' => $member->emergency_contact_phone,
            'notes' => $member->notes,
            'created_at' => optional($member->created_at)->format('d/m/Y H:i'),
            'updated_at' => optional($member->updated_at)->format('d/m/Y H:i'),
            'last_login' => $lastActivity->format('d/m/Y H:i'),
        ];
    }

    /**
     * Ensure the current user can manage the provided member.
     */
    protected function ensureBranchAccess(User $member): void
    {
        $currentUser = auth()->user();

        if ($currentUser->isAdmin()) {
            return;
        }

        abort_if($member->branch_id !== $currentUser->branch_id, 403);
    }

    /**
     * Resolve role options for select inputs.
     */
    protected function roleOptions(): array
    {
        return collect($this->roleMap)
            ->map(fn ($label, $key) => ['key' => $key, 'label' => $label])
            ->values()
            ->all();
    }

    /**
     * Resolve the stored role name from a request key.
     */
    protected function resolveRoleName(string $key): string
    {
        $key = strtolower($key);

        return $this->roleMap[$key] ?? $this->roleMap['employee'];
    }

    /**
     * Export employees to CSV with GDPR compliance
     */
    public function export(Request $request)
    {
        // RBAC check (throws 403 if unauthorized)
        $this->authorize('team.export');

        $validated = $request->validate([
            'format' => 'required|in:csv,xlsx',
            'fields' => 'array',
        ]);

        // Log export for GDPR audit trail
        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'format' => $validated['format'],
                'fields' => $validated['fields'] ?? 'all',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log('team_export_initiated');

        // Build filtered query (reuse index filters)
        $query = $this->buildExportQuery($request);

        return $this->generateCsvExport($query, $validated['fields'] ?? []);
    }

    /**
     * Build export query with same filters as index
     */
    private function buildExportQuery(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->input('search')),
            'branch' => $request->input('branch'),
            'status' => $request->input('status'),
        ];

        $query = User::query()->with(['branch']);

        if ($filters['search'] !== '') {
            $query->where(function ($builder) use ($filters) {
                $builder->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        if ($filters['branch']) {
            $query->where('branch_id', $filters['branch']);
        }

        if ($filters['status']) {
            $query->where('employment_status', $filters['status']);
        }

        return $query;
    }

    /**
     * Generate CSV export with data minimization
     */
    private function generateCsvExport($query, array $fields)
    {
        $exportableFields = [
            'employee_id' => ['label' => 'Employee ID', 'pii' => false],
            'name' => ['label' => 'Full Name', 'pii' => true],
            'email' => ['label' => 'Email', 'pii' => true],
            'phone' => ['label' => 'Phone', 'pii' => true],
            'position' => ['label' => 'Position', 'pii' => false],
            'employment_status' => ['label' => 'Status', 'pii' => false],
        ];

        // Default to all fields if none specified
        if (empty($fields)) {
            $fields = array_keys($exportableFields);
            activity()->causedBy(auth()->user())->log('team_export_all_pii_warning');
        }

        $filename = 'employees_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Cache-Control' => 'no-store, no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ];

        return response()->stream(function () use ($query, $fields, $exportableFields) {
            $handle = fopen('php://output', 'w');

            // CSV header row
            $headerRow = array_map(fn($field) => $exportableFields[$field]['label'], $fields);
            fputcsv($handle, $headerRow);

            // Stream data rows (memory-efficient)
            $query->chunk(100, function ($users) use ($handle, $fields) {
                foreach ($users as $user) {
                    $row = [];
                    foreach ($fields as $field) {
                        $row[] = $user->{$field} ?? '';
                    }
                    fputcsv($handle, $row);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}

