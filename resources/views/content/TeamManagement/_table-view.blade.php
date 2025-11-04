{{-- Dense Table View for Teams --}}
<div class="whs-dense-view" data-view-mode="table">

  {{-- Table Toolbar --}}
  <x-whs.table-toolbar
    title="Team members"
    :view-mode="request('view', 'cards')"
    :items-per-page="$paginator->perPage()"
    :search-action="route('teams.index')"
    search-placeholder="Search members, roles, branches..."
    :search-value="$filters['search'] ?? ''"
    :show-bulk-actions="true"
  >
    {{-- Toolbar description --}}
    {{ $paginator->total() }} total members across {{ $branches->count() }} branches

    {{-- Active Filters Slot --}}
    <x-slot:filters>
      <div class="whs-filter-chips">
        @if(!empty($filters['search']))
        <span class="whs-filter-chip">
          <i class="ti ti-search"></i>
          Search: "{{ $filters['search'] }}"
          <a href="{{ route('teams.index', array_diff_key(request()->all(), ['search' => ''])) }}" class="whs-filter-chip__remove" aria-label="Remove search filter">
            <i class="ti ti-x"></i>
          </a>
        </span>
        @endif

        @if(!empty($filters['branch']))
        <span class="whs-filter-chip">
          <i class="ti ti-building"></i>
          Branch: {{ $branches->firstWhere('id', $filters['branch'])->name ?? 'Unknown' }}
          <a href="{{ route('teams.index', array_diff_key(request()->all(), ['branch' => ''])) }}" class="whs-filter-chip__remove" aria-label="Remove branch filter">
            <i class="ti ti-x"></i>
          </a>
        </span>
        @endif

        @if(!empty($filters['role']))
        <span class="whs-filter-chip">
          <i class="ti ti-user-shield"></i>
          Role: {{ ucfirst($filters['role']) }}
          <a href="{{ route('teams.index', array_diff_key(request()->all(), ['role' => ''])) }}" class="whs-filter-chip__remove" aria-label="Remove role filter">
            <i class="ti ti-x"></i>
          </a>
        </span>
        @endif

        @if(!empty($filters['status']))
        <span class="whs-filter-chip">
          <i class="ti ti-circle-check"></i>
          Status: {{ ucfirst(str_replace('_', ' ', $filters['status'])) }}
          <a href="{{ route('teams.index', array_diff_key(request()->all(), ['status' => ''])) }}" class="whs-filter-chip__remove" aria-label="Remove status filter">
            <i class="ti ti-x"></i>
          </a>
        </span>
        @endif

        @if(!empty($filters['search']) || !empty($filters['branch']) || !empty($filters['role']) || !empty($filters['status']))
        <a href="{{ route('teams.index') }}" class="btn btn-sm btn-text whs-filter-clear">
          <i class="ti ti-x"></i> Clear all filters
        </a>
        @endif
      </div>
    </x-slot:filters>

    {{-- Bulk Actions Slot --}}
    <x-slot:bulkActions>
      <button type="button" class="btn btn-sm btn-primary" data-bulk-action="export">
        <i class="ti ti-download"></i> Export Selected
      </button>
      <button type="button" class="btn btn-sm btn-outline-secondary" data-bulk-action="assign-branch">
        <i class="ti ti-building"></i> Assign Branch
      </button>
      <button type="button" class="btn btn-sm btn-outline-secondary" data-bulk-action="mark-leave">
        <i class="ti ti-calendar-x"></i> Mark Leave
      </button>
      <button type="button" class="btn btn-sm btn-outline-danger" data-bulk-action="delete">
        <i class="ti ti-trash"></i> Delete
      </button>
    </x-slot:bulkActions>

    {{-- Additional Actions --}}
    <x-slot:actions>
      <a href="{{ route('teams.create') }}" class="btn btn-sm btn-primary">
        <i class="ti ti-plus"></i>
        <span class="d-none d-md-inline">Add member</span>
      </a>
    </x-slot:actions>
  </x-whs.table-toolbar>

  {{-- Dense Table --}}
  <x-whs.table
    density="normal"
    :striped="true"
    :hover="true"
    :responsive="true"
    :sticky-header="true"
    :sortable="true"
    :current-sort="request('sort', 'name')"
    :current-direction="request('direction', 'asc')"
  >
    <x-slot:header>
      <tr>
        <th class="whs-table__cell--checkbox">
          <div class="form-check">
            <input type="checkbox" class="form-check-input" id="selectAll" aria-label="Select all rows">
          </div>
        </th>
        <th data-sortable="employee_id" data-sort="{{ request('sort') === 'employee_id' ? request('direction', 'asc') : '' }}">
          Employee ID
        </th>
        <th data-sortable="name" data-sort="{{ request('sort') === 'name' ? request('direction', 'asc') : '' }}">
          Member
        </th>
        <th>Role</th>
        <th data-sortable="branch_id">Branch</th>
        <th data-sortable="employment_status" data-sort="{{ request('sort') === 'employment_status' ? request('direction', 'asc') : '' }}">
          Status
        </th>
        <th>Contact</th>
        <th>Vehicle</th>
        <th>Last Inspection</th>
        <th class="text-center">Certs</th>
        <th data-sortable="updated_at" data-sort="{{ request('sort') === 'updated_at' ? request('direction', 'asc') : '' }}">
          Last Active
        </th>
        <th class="text-end">Actions</th>
      </tr>
    </x-slot:header>

    <x-slot:body>
      {{-- DEBUG: Show count of members in data array --}}
      <tr><td colspan="12" style="background: #fff3cd; padding: 10px; font-weight: bold;">DEBUG: Total members in data array: {{ count($members['data']) }}</td></tr>
      @forelse($members['data'] as $member)
        @php
          $severityMap = [
            'suspended' => 'danger',
            'on_leave' => 'info',
            'inactive' => 'warning',
            'active' => 'normal',
          ];
          $severity = $severityMap[$member['status']] ?? 'normal';
        @endphp

        <x-whs.table-row
          :selectable="true"
          :row-id="$member['id']"
          :severity="$severity"
        >
          {{-- Employee ID --}}
          <x-whs.table-cell type="text" :value="$member['employee_id']" label="Employee ID" />

          {{-- Member Name with Avatar --}}
          <x-whs.table-cell
            type="avatar"
            :value="$member['name']"
            :meta="substr($member['name'], 0, 2)"
            :subtext="$member['email']"
            label="Member name"
          />

          {{-- Role --}}
          <x-whs.table-cell type="text" :value="ucfirst($member['role'])" label="Role" />

          {{-- Branch --}}
          <x-whs.table-cell type="text" :value="$member['branch_name']" label="Branch" />

          {{-- Employment Status --}}
          <x-whs.table-cell
            type="badge"
            :value="$member['status']"
            label="Employment status"
          />

          {{-- Contact --}}
          <td class="whs-table__cell">
            <div class="whs-table__contact">
              <a href="mailto:{{ $member['email'] }}" class="whs-table__link">{{ $member['email'] }}</a>
              @if($member['phone'] && $member['phone'] !== 'N/A')
              <span class="whs-table__meta">{{ $member['phone'] }}</span>
              @endif
            </div>
          </td>

          {{-- Current Vehicle --}}
          <td class="whs-table__cell">
            @if($member['current_vehicle'])
              @php $vehicle = $member['current_vehicle']; @endphp
              <div class="whs-table__vehicle">
                <strong class="whs-table__vehicle-reg">{{ $vehicle['registration_number'] }}</strong>
                <span class="whs-table__meta">{{ $vehicle['make'] }} {{ $vehicle['model'] }}</span>
                <span class="whs-table__meta">Since {{ $vehicle['assigned_human'] }}</span>
              </div>
            @else
              <span class="text-muted">Not assigned</span>
            @endif
          </td>

          {{-- Last Inspection --}}
          <td class="whs-table__cell">
            @if($member['latest_inspection'])
              @php
                $inspection = $member['latest_inspection'];
                $inspectionResult = $inspection['result'] ?? $inspection['status'];
                $badgeMap = [
                  'fail_major' => 'danger',
                  'fail_critical' => 'danger',
                  'pass' => 'success',
                  'pass_minor' => 'success',
                ];
                $badgeColor = $badgeMap[$inspectionResult] ?? 'info';
              @endphp
              <div class="whs-table__inspection">
                <span class="badge bg-{{ $badgeColor }} badge-sm">
                  {{ ucfirst(str_replace('_', ' ', $inspectionResult)) }}
                </span>
                <span class="whs-table__meta">{{ $inspection['date_human'] }}</span>
              </div>
            @else
              <span class="text-muted">No data</span>
            @endif
          </td>

          {{-- Certifications --}}
          <td class="whs-table__cell text-center">
            @if($member['certifications_count'] > 0)
              <div class="whs-table__certs">
                <strong class="whs-table__cert-count">{{ $member['certifications_count'] }}</strong>
                @if($member['has_expiring_certs'])
                  <span class="badge bg-warning badge-sm">Expiring</span>
                @endif
              </div>
            @else
              <span class="text-muted">—</span>
            @endif
          </td>

          {{-- Last Active --}}
          <x-whs.table-cell
            type="date"
            :value="$member['last_active']"
            :meta="true"
            label="Last activity"
          />

          {{-- Actions --}}
          <td class="whs-table__cell whs-table__cell--actions text-end">
            <div class="btn-group" role="group" aria-label="Member actions">
              <button
                type="button"
                class="btn btn-sm btn-icon btn-outline-secondary"
                data-quick-view
                data-member-id="{{ $member['id'] }}"
                title="Quick view"
                aria-label="Quick view {{ $member['name'] }}"
              >
                <i class="ti ti-eye"></i>
              </button>

              <a
                href="{{ route('teams.edit', $member['id']) }}"
                class="btn btn-sm btn-icon btn-outline-secondary"
                title="Edit member"
                aria-label="Edit {{ $member['name'] }}"
              >
                <i class="ti ti-edit"></i>
              </a>

              <div class="btn-group" role="group">
                <button
                  type="button"
                  class="btn btn-sm btn-icon btn-outline-secondary dropdown-toggle"
                  data-bs-toggle="dropdown"
                  aria-expanded="false"
                  aria-label="More actions for {{ $member['name'] }}"
                >
                  <i class="ti ti-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="{{ route('teams.show', $member['id']) }}">
                      <i class="ti ti-user me-2"></i> View Profile
                    </a>
                  </li>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <button type="button" class="dropdown-item" onclick="viewCertifications('{{ $member['id'] }}', '{{ $member['name'] }}')">
                      <i class="ti ti-certificate me-2"></i> Certifications
                    </button>
                  </li>
                  <li>
                    <button type="button" class="dropdown-item" onclick="viewTrainingHistory('{{ $member['id'] }}', '{{ $member['name'] }}')">
                      <i class="ti ti-book me-2"></i> Training
                    </button>
                  </li>
                  <li><hr class="dropdown-divider"></li>
                  @if($member['status'] === 'active')
                  <li>
                    <form action="{{ route('teams.on-leave', $member['id']) }}" method="POST" onsubmit="return confirm('Mark {{ $member['name'] }} as on leave?')">
                      @csrf
                      <button type="submit" class="dropdown-item text-warning">
                        <i class="ti ti-calendar-x me-2"></i> Mark on Leave
                      </button>
                    </form>
                  </li>
                  @elseif($member['status'] === 'on_leave')
                  <li>
                    <form action="{{ route('teams.activate', $member['id']) }}" method="POST" onsubmit="return confirm('Mark {{ $member['name'] }} as active?')">
                      @csrf
                      <button type="submit" class="dropdown-item text-success">
                        <i class="ti ti-check me-2"></i> Activate
                      </button>
                    </form>
                  </li>
                  @endif
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <form action="{{ route('teams.destroy', $member['id']) }}" method="POST" onsubmit="return confirm('Delete {{ $member['name'] }}? This action cannot be undone.')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="dropdown-item text-danger">
                        <i class="ti ti-trash me-2"></i> Delete
                      </button>
                    </form>
                  </li>
                </ul>
              </div>
            </div>
          </td>
        </x-whs.table-row>
      @empty
        <tr>
          <td colspan="12" class="whs-dense-table__empty">
            <div class="whs-dense-table__empty-icon">
              <i class="ti ti-users"></i>
            </div>
            <p class="whs-dense-table__empty-text">
              No team members found. Try adjusting your filters or add your first member.
            </p>
            <a href="{{ route('teams.create') }}" class="btn btn-primary mt-3">
              <i class="ti ti-plus me-2"></i> Add first member
            </a>
          </td>
        </tr>
      @endforelse
    </x-slot:body>
  </x-whs.table>

  {{-- Pagination --}}
  @if($paginator->hasPages())
  <div class="whs-table-pagination">
    <div class="whs-table-pagination__info">
      Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} members
    </div>
    <div class="whs-table-pagination__nav">
      {{ $paginator->links() }}
    </div>
  </div>
  @endif

</div>

@push('page-style')
<style>
.whs-dense-view {
  background: var(--sensei-surface);
  border-radius: var(--sensei-radius);
  padding: 0;
  border: 1px solid var(--sensei-border);
  box-shadow: var(--sensei-shadow-card);
  overflow: hidden;
}

/* Toolbar inside dense view - full width, no external margin */
.whs-dense-view > .whs-table-toolbar {
  margin: 0;
  border: none;
  border-radius: 0;
  border-bottom: 1px solid var(--sensei-border);
}

/* Table wrapper - full width, no border */
.whs-dense-view .table-responsive {
  border: none;
  border-radius: 0;
}

/* Pagination - add padding only here */
.whs-dense-view .whs-table-pagination {
  padding: 1.5rem;
  margin: 0;
}

.whs-filter-chips {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  align-items: center;
}

.whs-filter-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.375rem 0.75rem;
  background: var(--sensei-accent-soft);
  border: 1px solid color-mix(in srgb, var(--sensei-accent) 25%, transparent);
  border-radius: 20px;
  font-size: 0.8125rem;
  color: var(--sensei-text-primary);
  transition: all var(--sensei-transition);
}

.whs-filter-chip i {
  font-size: 0.875rem;
  color: var(--sensei-accent);
}

.whs-filter-chip__remove {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 18px;
  height: 18px;
  margin-left: 0.25rem;
  border-radius: 50%;
  background: color-mix(in srgb, var(--sensei-accent) 20%, transparent);
  color: var(--sensei-text-primary);
  transition: all var(--sensei-transition);
  text-decoration: none;
}

.whs-filter-chip__remove:hover {
  background: color-mix(in srgb, var(--sensei-accent) 30%, transparent);
  transform: scale(1.1);
}

.whs-filter-clear {
  font-size: 0.8125rem;
  padding: 0.375rem 0.75rem;
}

.whs-table__contact,
.whs-table__vehicle,
.whs-table__inspection,
.whs-table__certs {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.whs-table__link {
  color: var(--sensei-accent);
  text-decoration: none;
  font-weight: 500;
  transition: all var(--sensei-transition);
}

.whs-table__link:hover {
  text-decoration: underline;
  color: var(--sensei-accent-hover, var(--sensei-accent));
}

.whs-table__meta {
  font-size: 0.75rem;
  color: var(--sensei-text-metadata);
}

.whs-table__vehicle-reg {
  font-weight: 600;
  color: var(--sensei-text-primary);
}

.whs-table__cert-count {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--sensei-accent);
}

.whs-table__cell--actions {
  white-space: nowrap;
}

/* Action Buttons - Sensei Token Styling */
.whs-table__cell--actions .btn {
  padding: 0.5rem;
  border-radius: var(--sensei-radius-sm);
  font-size: 0.875rem;
  transition: all var(--sensei-transition);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.375rem;
}

.whs-table__cell--actions .btn-icon {
  width: 2rem;
  height: 2rem;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.whs-table__cell--actions .btn-outline-secondary {
  background: color-mix(in srgb, var(--sensei-surface) 100%, transparent);
  border: 1px solid var(--sensei-border);
  color: var(--sensei-text-secondary);
}

.whs-table__cell--actions .btn-outline-secondary:hover {
  background: color-mix(in srgb, var(--sensei-accent) 8%, transparent);
  border-color: color-mix(in srgb, var(--sensei-accent) 40%, transparent);
  color: var(--sensei-accent);
  transform: translateY(-1px);
}

.whs-table__cell--actions .btn-outline-secondary:focus-visible {
  outline: 2px solid var(--sensei-accent);
  outline-offset: 2px;
  box-shadow: 0 0 0 4px color-mix(in srgb, var(--sensei-accent) 20%, transparent);
}

.whs-table__cell--actions .btn-group {
  gap: 0.25rem;
}

.whs-table__cell--actions .dropdown-menu {
  background: var(--sensei-surface);
  border: 1px solid var(--sensei-border);
  border-radius: var(--sensei-radius);
  box-shadow: var(--sensei-shadow-hover);
  padding: 0.5rem 0;
}

.whs-table__cell--actions .dropdown-item {
  color: var(--sensei-text-primary);
  padding: 0.5rem 1rem;
  transition: all var(--sensei-transition);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.whs-table__cell--actions .dropdown-item:hover {
  background: color-mix(in srgb, var(--sensei-accent) 10%, transparent);
  color: var(--sensei-accent);
}

.whs-table__cell--actions .dropdown-divider {
  border-color: var(--sensei-border);
  margin: 0.5rem 0;
}

/* Light Theme Button Overrides */
[data-bs-theme='light'] .whs-table__cell--actions .btn-outline-secondary {
  background: rgba(255, 255, 255, 0.9);
  border-color: rgba(15, 23, 42, 0.15);
  color: rgba(15, 23, 42, 0.7);
}

[data-bs-theme='light'] .whs-table__cell--actions .btn-outline-secondary:hover {
  background: rgba(14, 165, 233, 0.08);
  border-color: rgba(14, 165, 233, 0.4);
  color: #0ea5e9;
}

[data-bs-theme='light'] .whs-table__cell--actions .dropdown-menu {
  background: white;
  border-color: rgba(15, 23, 42, 0.15);
  box-shadow: 0 4px 6px rgba(15, 23, 42, 0.1), 0 2px 4px rgba(15, 23, 42, 0.06);
}

[data-bs-theme='light'] .whs-table__cell--actions .dropdown-item {
  color: rgba(15, 23, 42, 0.9);
}

[data-bs-theme='light'] .whs-table__cell--actions .dropdown-item:hover {
  background: rgba(14, 165, 233, 0.1);
  color: #0ea5e9;
}

/* Drawer Content Styling - Sensei Tokens */
.employee-card {
  background: var(--sensei-surface);
}

.employee-name {
  color: var(--sensei-text-primary);
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.contact-email a,
.contact-phone a {
  color: var(--sensei-accent);
  text-decoration: none;
  transition: all var(--sensei-transition);
}

.contact-email a:hover {
  text-decoration: underline;
  color: var(--sensei-accent-hover, var(--sensei-accent));
}

.metadata-grid {
  display: grid;
  gap: 1rem;
}

.metadata-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid var(--sensei-border);
}

.metadata-row:last-child {
  border-bottom: none;
}

.metadata-label {
  color: var(--sensei-text-tertiary);
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.metadata-value {
  color: var(--sensei-text-primary);
  font-size: 0.875rem;
  font-weight: 500;
}

.section-title {
  color: var(--sensei-text-primary);
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.status-badge {
  padding: 0.375rem 0.875rem;
  border-radius: var(--sensei-radius-sm);
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  display: inline-block;
}

.status-active,
.status-badge.status-active {
  background: var(--sensei-success-soft);
  color: var(--sensei-success);
  border: 1px solid color-mix(in srgb, var(--sensei-success) 25%, transparent);
}

.status-on-leave,
.status-badge.status-on-leave {
  background: var(--sensei-warning-soft);
  color: var(--sensei-warning);
  border: 1px solid color-mix(in srgb, var(--sensei-warning) 25%, transparent);
}

.status-suspended,
.status-inactive,
.status-badge.status-suspended,
.status-badge.status-inactive {
  background: var(--sensei-alert-soft);
  color: var(--sensei-alert);
  border: 1px solid color-mix(in srgb, var(--sensei-alert) 25%, transparent);
}

.metric-pill {
  background: var(--sensei-surface-strong);
  border: 1px solid var(--sensei-border);
  border-radius: var(--sensei-radius);
  padding: 1rem;
  text-align: center;
  transition: all var(--sensei-transition);
}

.metric-pill:hover {
  border-color: var(--sensei-accent);
  box-shadow: 0 2px 8px color-mix(in srgb, var(--sensei-accent) 15%, transparent);
}

.metric-label {
  color: var(--sensei-text-tertiary);
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  display: block;
  margin-bottom: 0.5rem;
}

.metric-value {
  color: var(--sensei-accent);
  font-size: 1.75rem;
  font-weight: 700;
  display: block;
}

.whs-table-pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid var(--sensei-border);
}

.whs-table-pagination__info {
  font-size: 0.875rem;
  color: var(--sensei-text-secondary);
}

/* Light Theme - Sensei tokens automatically adapt */
[data-bs-theme='light'] .whs-dense-view {
  background: var(--sensei-surface-strong);
  border-color: var(--sensei-border);
  box-shadow: var(--sensei-shadow-card);
}

[data-bs-theme='light'] .whs-filter-chip {
  background: var(--sensei-accent-soft);
  border-color: color-mix(in srgb, var(--sensei-accent) 25%, transparent);
}

[data-bs-theme='light'] .whs-filter-chip__remove {
  background: color-mix(in srgb, var(--sensei-accent) 20%, transparent);
}

[data-bs-theme='light'] .whs-filter-chip__remove:hover {
  background: color-mix(in srgb, var(--sensei-accent) 30%, transparent);
}

@media (max-width: 768px) {
  .whs-table-pagination {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
}
</style>
@endpush
