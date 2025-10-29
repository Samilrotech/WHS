@php
  setcookie('contentLayout', 'wide', time() + 60 * 60 * 24 * 365, '/');
  $pageConfigs = ['contentLayout' => 'wide'];
  $queryParams = array_filter(['q' => request('q')]);
  $statusFilters = [
    [
      'label' => 'All',
      'url' => route('branches.index', $queryParams),
      'active' => !request()->filled('status'),
    ],
    [
      'label' => 'Active',
      'url' => route('branches.index', array_merge($queryParams, ['status' => 'active'])),
      'active' => request('status') === 'active',
    ],
    [
      'label' => 'Inactive',
      'url' => route('branches.index', array_merge($queryParams, ['status' => 'inactive'])),
      'active' => request('status') === 'inactive',
    ],
  ];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Branch Management')

@section('content')
<div class="whs-shell">
  <x-whs.hero
    eyebrow="Administration"
    title="Branch Management"
    subtitle="Enterprise-grade command centre for organizational locations. Centralized branch oversight, employee allocation, and geographic operations management across your entire network."
    :metric="true"
    metricLabel="Total Locations"
    :metricValue="$statistics['total'] ?? 0"
    metricCaption="Operational facilities"
    :searchRoute="route('branches.index')"
    searchPlaceholder="Search branches by name, code, or location..."
    :createRoute="route('branches.create')"
    createLabel="Add Branch"
    :filters="$statusFilters"
  />

  <section class="whs-metrics">
    <x-whs.metric-card
      icon="ti-building"
      iconVariant="brand"
      label="Total Branches"
      :value="$statistics['total'] ?? 0"
      meta="All locations"
    />

    <x-whs.metric-card
      icon="ti-circle-check"
      iconVariant="success"
      label="Active Branches"
      :value="$statistics['active'] ?? 0"
      meta="Operational facilities"
    />

    <x-whs.metric-card
      icon="ti-circle-x"
      iconVariant="warning"
      label="Inactive Branches"
      :value="$statistics['inactive'] ?? 0"
      meta="Non-operational"
    />

    <x-whs.metric-card
      icon="ti-users"
      iconVariant="brand"
      label="Total Employees"
      :value="$statistics['total_employees'] ?? 0"
      meta="Across all branches"
    />
  </section>

  <div class="whs-main">
    <div class="whs-section-heading">
      <div>
        <h2>Branch Directory</h2>
        <p>Comprehensive list of all organizational locations with employee allocation and operational status.</p>
      </div>
    </div>

    @if($branches->isEmpty())
      <div class="whs-empty">
        <div class="whs-empty__icon">
          <i class="icon-base ti ti-building"></i>
        </div>
        <h3>No Branches Found</h3>
        <p>Get started by creating your first organizational branch location.</p>
        <a href="{{ route('branches.create') }}" class="whs-btn-primary mt-3">
          <i class="icon-base ti ti-plus"></i>
          <span>Create First Branch</span>
        </a>
      </div>
    @else
      <div class="whs-stack">
        @foreach($branches as $branch)
          <x-whs.card class="whs-branch-card sensei-surface-card">
            <div class="whs-chip-group">
              <span class="whs-chip whs-chip--id">
                <i class="icon-base ti ti-hash"></i>
                {{ $branch->code }}
              </span>
              @if($branch->is_active)
                <span class="whs-chip whs-chip--status-resolved">
                  <i class="icon-base ti ti-circle-check"></i>
                  Active
                </span>
              @else
                <span class="whs-chip whs-chip--status">
                  <i class="icon-base ti ti-circle-x"></i>
                  Inactive
                </span>
              @endif
            </div>

            <h3 class="whs-branch-card__title">{{ $branch->name }}</h3>

            <div class="whs-kv-grid">
              <div class="whs-kv">
                <span class="whs-kv__label">Location</span>
                <span class="whs-kv__value">
                  <i class="icon-base ti ti-map-pin"></i>
                  {{ $branch->city }}, {{ $branch->state }}
                </span>
              </div>

              <div class="whs-kv">
                <span class="whs-kv__label">Manager</span>
                <span class="whs-kv__value">
                  <i class="icon-base ti ti-user"></i>
                  {{ $branch->manager_name ?? 'Not Assigned' }}
                </span>
              </div>

              <div class="whs-kv">
                <span class="whs-kv__label">Employees</span>
                <span class="whs-kv__value">
                  <i class="icon-base ti ti-users"></i>
                  <strong>{{ $branch->users_count }}</strong> employee{{ $branch->users_count !== 1 ? 's' : '' }}
                </span>
              </div>

              @if($branch->phone)
                <div class="whs-kv">
                  <span class="whs-kv__label">Contact</span>
                  <span class="whs-kv__value">
                    <i class="icon-base ti ti-phone"></i>
                    {{ $branch->phone }}
                  </span>
                </div>
              @endif
            </div>

            @php
              $compliance = $branch->vehicle_compliance ?? ['vehicles' => [], 'inspections' => []];
              $vehicleStats = $compliance['vehicles'] ?? [];
              $inspectionStats = $compliance['inspections'] ?? [];
              $latestInspection = $inspectionStats['latest_at'] ?? null;
            @endphp

            <div class="sensei-surface-card sensei-compliance-card mb-3">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="d-flex align-items-center gap-2">
                  <span class="sensei-badge-dot" data-variant="{{ $branch->is_active ? 'success' : '' }}">Fleet</span>
                  <h6 class="mb-0">Vehicle Compliance Snapshot</h6>
                </div>
                <span class="text-muted small">
                  @if($latestInspection)
                    Last inspection {{ $latestInspection?->diffForHumans() }}
                  @else
                    No inspections recorded yet
                  @endif
                </span>
              </div>

              <div class="sensei-stat-grid mb-3">
                <div class="sensei-stat">
                  <small>Fleet Size</small>
                  <span>{{ $vehicleStats['total'] ?? 0 }}</span>
                </div>
                <div class="sensei-stat">
                  <small>Assigned Drivers</small>
                  <span>{{ $vehicleStats['assigned'] ?? 0 }}</span>
                </div>
                <div class="sensei-stat">
                  <small>Upcoming Compliance</small>
                  <span class="text-warning">{{ ($vehicleStats['inspection_due'] ?? 0) + ($vehicleStats['rego_expiring'] ?? 0) + ($vehicleStats['insurance_expiring'] ?? 0) }}</span>
                </div>
                <div class="sensei-stat">
                  <small>Inspection Pass Rate</small>
                  @if(!is_null($inspectionStats['compliance_rate'] ?? null))
                    <span class="text-success">{{ $inspectionStats['compliance_rate'] }}%</span>
                  @else
                    <span class="text-muted">No data</span>
                  @endif
                </div>
              </div>

              <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('vehicles.index', ['branch' => $branch->id]) }}" class="btn btn-sm btn-outline-primary">
                  <i class="icon-base ti ti-car me-1"></i> Fleet view
                </a>
                <a href="{{ route('inspections.index', ['branch' => $branch->id]) }}" class="btn btn-sm btn-outline-secondary">
                  <i class="icon-base ti ti-file-analytics me-1"></i> Inspection log
                </a>
              </div>
            </div>

            <div class="whs-card__footer">
              <div class="whs-card__actions">
                <x-whs.action-button
                  :href="route('branches.show', $branch->id)"
                  icon="ti-eye"
                >
                  View Details
                </x-whs.action-button>

                <x-whs.action-button
                  :href="route('branches.edit', $branch->id)"
                  icon="ti-edit"
                >
                  Edit Branch
                </x-whs.action-button>

                <form action="{{ route('branches.toggleStatus', $branch->id) }}" method="POST" class="whs-inline-form">
                  @csrf
                  <x-whs.action-button
                    type="submit"
                    :icon="$branch->is_active ? 'ti-circle-x' : 'ti-circle-check'"
                  >
                    {{ $branch->is_active ? 'Deactivate' : 'Activate' }}
                  </x-whs.action-button>
                </form>

                @if($branch->users_count === 0)
                  <form action="{{ route('branches.destroy', $branch->id) }}" method="POST" class="whs-inline-form" onsubmit="return confirm('Are you sure you want to delete this branch? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <x-whs.action-button type="submit" variant="danger" icon="ti-trash">
                      Delete
                    </x-whs.action-button>
                  </form>
                @else
                  <x-whs.action-button variant="danger" icon="ti-trash" disabled>
                    Delete
                  </x-whs.action-button>
                @endif
              </div>

              @if($branch->updated_at)
                <span class="whs-updated">
                  Updated {{ $branch->updated_at->diffForHumans() }}
                </span>
              @endif
            </div>
          </x-whs.card>
        @endforeach
      </div>

      <div class="whs-pagination">
        {{ $branches->links('vendor.pagination.whs') }}
      </div>
    @endif
  </div>
</div>
@endsection

