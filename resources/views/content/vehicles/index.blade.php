@php
  setcookie('contentLayout', 'wide', time() + 60 * 60 * 24 * 365, '/');
  $pageConfigs = ['contentLayout' => 'wide'];
@endphp

@extends('layouts.layoutMaster')

@section('title', 'Vehicle Management')

@section('page-script')
<script>
  (function ensureWideLayout() {
    var expires = new Date();
    expires.setFullYear(expires.getFullYear() + 1);
    document.cookie = 'contentLayout=wide;path=/;expires=' + expires.toUTCString();
  })();
</script>
@endsection

@section('content')
@include('layouts.sections.flash-message')

@php
  $activeVehicles = $statistics['active'] ?? 0;
  $maintenanceVehicles = $statistics['maintenance'] ?? 0;
  $inspectionDue = $statistics['inspection_due'] ?? 0;

  $filterPills = [
    ['label' => 'All vehicles', 'active' => true],
    ['label' => 'Active', 'active' => $activeVehicles > 0],
    ['label' => 'Maintenance', 'active' => $maintenanceVehicles > 0],
    ['label' => 'Inspection Due', 'active' => $inspectionDue > 0],
  ];
@endphp

<div class="whs-shell">
  <x-whs.hero
    eyebrow="Fleet & Assets"
    title="Vehicle Management"
    subtitle="Fleet tracking with automated inspection reminders, service history, and cost analysis per vehicle across all branches."
    :metric="true"
    metricLabel="Total fleet"
    :metricValue="$statistics['total'] ?? 0"
    metricCaption="Vehicles tracked across WHS4 network"
    :searchRoute="route('vehicles.index')"
    searchPlaceholder="Search vehicles, registration, make…"
    :createRoute="route('vehicles.create')"
    createLabel="Add vehicle"
    :filters="$filterPills"
  />

  <section class="whs-metrics">
    <x-whs.metric-card
      icon="ti-car"
      iconVariant="brand"
      label="Total Vehicles"
      :value="$statistics['total'] ?? 0"
      meta="Complete fleet inventory"
    />

    <x-whs.metric-card
      icon="ti-circle-check"
      iconVariant="success"
      label="Active"
      :value="$activeVehicles"
      meta="Operational and ready"
      metaClass="text-success"
    />

    <x-whs.metric-card
      icon="ti-tool"
      iconVariant="warning"
      label="Maintenance"
      :value="$maintenanceVehicles"
      meta="Currently being serviced"
      metaClass="text-warning"
    />

    <x-whs.metric-card
      icon="ti-alert-triangle"
      iconVariant="critical"
      label="Inspection Due"
      :value="$inspectionDue"
      meta="Requires immediate attention"
      metaClass="text-danger"
    />
  </section>

  <div class="whs-layout">
    <div class="whs-main">
      <div class="whs-section-heading">
        <div>
          <h2>Vehicle fleet register</h2>
          <p>Fleet inventory sorted by registration number.</p>
        </div>
        <span class="whs-updated">Updated {{ now()->format('H:i') }}</span>
      </div>

      <form method="GET" class="card sensei-surface-card sensei-filter-card mb-4 border-0 p-3">
        <div class="row g-3 align-items-end">
          <div class="col-lg-3">
            <label for="filter_branch" class="form-label">Branch</label>
            <select id="filter_branch" name="branch" class="form-select">
              <option value="">All branches</option>
              @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ ($filters['branch'] ?? '') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-lg-3">
            <label for="filter_status" class="form-label">Status</label>
            <select id="filter_status" name="status" class="form-select">
              <option value="">All statuses</option>
              <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
              <option value="maintenance" {{ ($filters['status'] ?? '') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
              <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
              <option value="sold" {{ ($filters['status'] ?? '') === 'sold' ? 'selected' : '' }}>Sold</option>
            </select>
          </div>
          <div class="col-lg-3">
            <label for="filter_assigned" class="form-label">Assignment</label>
            <select id="filter_assigned" name="assigned" class="form-select">
              <option value="all" {{ ($filters['assigned'] ?? 'all') === 'all' ? 'selected' : '' }}>All vehicles</option>
              <option value="yes" {{ ($filters['assigned'] ?? '') === 'yes' ? 'selected' : '' }}>Assigned</option>
              <option value="no" {{ ($filters['assigned'] ?? '') === 'no' ? 'selected' : '' }}>Available</option>
            </select>
          </div>
          <div class="col-lg-3">
            <label for="filter_make" class="form-label">Make</label>
            <select id="filter_make" name="make" class="form-select">
              <option value="">All makes</option>
              @foreach($makes as $make)
                <option value="{{ $make }}" {{ ($filters['make'] ?? '') === $make ? 'selected' : '' }}>{{ $make }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-12 d-flex gap-2 justify-content-end">
            <button type="submit" class="btn btn-primary">Apply filters</button>
            <a href="{{ route('vehicles.index') }}" class="btn btn-outline-secondary">Reset</a>
          </div>
        </div>
      </form>

      <div class="whs-card-list">
        @forelse ($vehicles['data'] as $vehicle)
          @php
            $severity = $vehicle->isInspectionDue() || $vehicle->isRegistrationExpiring() ? 'critical' : ($vehicle->status === 'maintenance' ? 'high' : 'low');
            $statusLabel = match($vehicle->status) {
              'active' => 'Active',
              'maintenance' => 'Maintenance',
              'inactive' => 'Inactive',
              default => ucfirst($vehicle->status)
            };
            $latestInspection = $vehicle->latestInspection;
            if ($latestInspection && in_array($latestInspection->overall_result, ['fail_major', 'fail_critical'])) {
              $severity = 'critical';
            }
            $assignedDriver = $vehicle->currentAssignment?->user;
          @endphp

          <x-whs.card :severity="$severity" class="sensei-surface-card">
            <div class="whs-card__header">
              <span class="whs-chip whs-chip--id">
                @if($vehicle->registration_state)
                  <span class="text-uppercase fw-semibold me-1">{{ $vehicle->registration_state }}</span>
                @endif
                {{ $vehicle->registration_number }}
              </span>
              <span class="whs-chip whs-chip--status whs-chip--status-{{ strtolower($statusLabel) }}">
                {{ $statusLabel }}
              </span>
            </div>

            <div class="whs-card__body">
              <div>
                <h3>{{ $vehicle->make }} {{ $vehicle->model }}</h3>
                <p>{{ $vehicle->year }} • {{ number_format($vehicle->odometer_reading) }} km</p>
              </div>
              <div>
                <span class="whs-location-label">Registration Expiry</span>
                <span @if($vehicle->isRegistrationExpiring()) class="text-danger" @endif>
                  @if($vehicle->rego_expiry_date)
                    {{ $vehicle->rego_expiry_date->format('d M Y') }}
                    @if($vehicle->isRegistrationExpiring())
                      <span class="whs-chip whs-chip--severity whs-chip--severity-critical ms-1">Expiring</span>
                    @endif
                  @else
                    -
                  @endif
                </span>
              </div>
              <div>
                <span class="whs-location-label">Inspection Due</span>
                <span @if($vehicle->isInspectionDue()) class="text-danger" @endif>
                  @if($vehicle->inspection_due_date)
                    {{ $vehicle->inspection_due_date->format('d M Y') }}
                    @if($vehicle->isInspectionDue())
                      <span class="whs-chip whs-chip--severity whs-chip--severity-critical ms-1">Overdue</span>
                    @endif
                  @else
                    -
                  @endif
                </span>
              </div>
              <div>
                <span class="whs-location-label">Next Service</span>
                <span>
                  @php
                    $serviceOverdue = $vehicle->next_service_odometer !== null && $vehicle->odometer_reading >= $vehicle->next_service_odometer;
                  @endphp
                  @if($vehicle->next_service_odometer)
                    {{ number_format($vehicle->next_service_odometer) }} km
                    @if($serviceOverdue)
                      <span class="whs-chip whs-chip--severity whs-chip--severity-critical ms-1">Due now</span>
                    @endif
                  @else
                    -
                  @endif
                </span>
              </div>
              <div>
                <span class="whs-location-label">Assignment</span>
                <span>
                  @if($vehicle->isAssigned() && $assignedDriver)
                    <strong>{{ $assignedDriver->name }}</strong> &middot; since {{ optional($vehicle->currentAssignment->assigned_date)->diffForHumans() }}
                  @elseif($vehicle->isAssigned())
                    Assigned
                  @else
                    <span class="text-muted">Available for allocation</span>
                  @endif
                </span>
              </div>
              <div>
                <span class="whs-location-label">Last Inspection</span>
                <span>
                  @if($latestInspection)
                    @php
                      $inspectionResult = $latestInspection->overall_result ?? $latestInspection->status;
                      $badgeColor = in_array($inspectionResult, ['fail_major','fail_critical']) ? 'danger' : (in_array($inspectionResult, ['pass','pass_minor']) ? 'success' : 'info');
                    @endphp
                    {{ $latestInspection->inspection_date?->format('d M Y') }}
                    <span class="badge bg-label-{{ $badgeColor }} ms-1">{{ ucfirst(str_replace('_', ' ', $inspectionResult)) }}</span>
                  @else
                    <span class="text-muted">No inspections logged</span>
                  @endif
                </span>
              </div>
            </div>

            <div class="whs-card__footer">
              <div class="whs-card__actions">
                <a href="{{ route('vehicles.show', $vehicle) }}" class="whs-action-btn" aria-label="View vehicle">
                  <i class="icon-base ti ti-external-link"></i>
                  <span>Open</span>
                </a>

                <a href="{{ route('vehicles.edit', $vehicle) }}" class="whs-action-btn" aria-label="Edit vehicle">
                  <i class="icon-base ti ti-edit"></i>
                  <span>Edit</span>
                </a>

                @if(!$vehicle->isAssigned())
                  <button type="button" class="whs-action-btn" data-bs-toggle="modal" data-bs-target="#assignModal{{ $vehicle->id }}">
                    <i class="icon-base ti ti-user-plus"></i>
                    <span>Assign</span>
                  </button>
                @else
                  <button type="button" class="whs-action-btn whs-action-btn--warning" data-bs-toggle="modal" data-bs-target="#returnModal{{ $vehicle->id }}">
                    <i class="icon-base ti ti-arrow-back-up"></i>
                    <span>Return</span>
                  </button>
                @endif

                <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="whs-action-btn whs-action-btn--danger" onclick="return confirm('Delete this vehicle?')">
                    <i class="icon-base ti ti-trash"></i>
                    <span>Delete</span>
                  </button>
                </form>
              </div>
              <button class="whs-card__more">
                <i class="icon-base ti ti-dots"></i>
              </button>
            </div>
          </x-whs.card>
        @empty
          <div class="whs-empty">
            <div class="whs-empty__content">
              <i class="icon-base ti ti-car whs-empty__icon"></i>
              <h3>No vehicles yet</h3>
              <p>No vehicles have been added to the fleet. Start tracking your vehicle inventory.</p>
              <a href="{{ route('vehicles.create') }}" class="whs-btn-primary whs-btn-primary--ghost">
                <i class="icon-base ti ti-plus me-2"></i>
                Add first vehicle
              </a>
            </div>
          </div>
        @endforelse
      </div>
    </div>

    <aside class="whs-sidebar">
      <x-whs.sidebar-panel title="Fleet status">
        <ul class="whs-sidebar__stats">
          <li>
            <span>Active (Operational)</span>
            <strong class="text-success">{{ $activeVehicles }}</strong>
          </li>
          <li>
            <span>In Maintenance</span>
            <strong class="text-warning">{{ $maintenanceVehicles }}</strong>
          </li>
          <li>
            <span>Inspection Due</span>
            <strong class="text-danger">{{ $inspectionDue }}</strong>
          </li>
        </ul>
        <p class="whs-sidebar__caption">
          Monthly automated inspection reminders ensure compliance and safety across the fleet.
        </p>
      </x-whs.sidebar-panel>

      <x-whs.sidebar-panel title="Vehicle maintenance cycle">
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
          <div style="padding: 0.75rem; background: linear-gradient(135deg, rgba(244, 246, 255, 0.96), rgba(228, 233, 255, 0.98)); border-radius: 12px; border: 1px solid rgba(0, 71, 255, 0.12);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">1. QR Code Scanning</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Scan vehicle QR for quick access</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">2. Monthly Inspection</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Automated reminders sent</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">3. Service History</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Complete maintenance tracking</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">4. Cost Analysis</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Per-vehicle expense tracking</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">5. Preventive Scheduling</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Automated maintenance planning</span>
          </div>
        </div>
      </x-whs.sidebar-panel>
    </aside>
  </div>
</div>
@endsection

