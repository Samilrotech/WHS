@php
  setcookie('contentLayout', 'wide', time() + 60 * 60 * 24 * 365, '/');
  $pageConfigs = ['contentLayout' => 'wide'];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Maintenance Scheduling')

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
  $filterPills = [
    ['label' => 'All schedules', 'active' => true],
    ['label' => 'Overdue', 'active' => $statistics['overdue'] > 0],
    ['label' => 'Due Soon', 'active' => $statistics['due_soon'] > 0],
    ['label' => 'Active', 'active' => $statistics['active'] > 0],
  ];
@endphp

<div class="whs-shell">
  <!-- Overdue Alert -->
  @if($overdueSchedules->count() > 0)
  <div class="alert alert-danger alert-dismissible mb-4" role="alert">
    <h5 class="alert-heading mb-2">
      <i class="icon-base ti ti-alert-circle"></i>
      {{ $overdueSchedules->count() }} Overdue Maintenance Schedule(s) Require Attention
    </h5>
    <p class="mb-0">The following schedules are overdue:</p>
    <ul class="mb-0 mt-2">
      @foreach($overdueSchedules as $schedule)
      <li>
        <strong>{{ $schedule->vehicle->registration_number ?? 'Equipment' }}</strong> - {{ $schedule->schedule_name }}
        (Due: {{ $schedule->next_due_date->format('d/m/Y') }})
      </li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <x-whs.hero
    eyebrow="Fleet Maintenance"
    title="Maintenance Scheduling"
    subtitle="Preventive maintenance scheduling with automated reminders, odometer-based intervals, and work order generation across all vehicles."
    :metric="true"
    metricLabel="Total schedules"
    :metricValue="$statistics['total']"
    metricCaption="Active maintenance schedules"
    :searchRoute="route('maintenance.index')"
    searchPlaceholder="Search schedules, vehicles, types…"
    :createRoute="route('maintenance.create')"
    createLabel="New schedule"
    :filters="$filterPills"
  />

  <section class="whs-metrics">
    <x-whs.metric-card
      icon="ti-calendar-check"
      iconVariant="brand"
      label="Total Schedules"
      :value="$statistics['total']"
      meta="All maintenance schedules"
    />

    <x-whs.metric-card
      icon="ti-circle-check"
      iconVariant="success"
      label="Active"
      :value="$statistics['active']"
      meta="Currently running"
      metaClass="text-success"
    />

    <x-whs.metric-card
      icon="ti-alert-triangle"
      iconVariant="critical"
      label="Overdue"
      :value="$statistics['overdue']"
      meta="Requires immediate attention"
      metaClass="text-danger"
    />

    <x-whs.metric-card
      icon="ti-clock"
      iconVariant="warning"
      label="Due Soon"
      :value="$statistics['due_soon']"
      meta="Due within 14 days"
      metaClass="text-warning"
    />
  </section>

  <div class="whs-layout">
    <div class="whs-main">
      <div class="whs-section-heading">
        <div>
          <h2>Maintenance schedule register</h2>
          <p>All maintenance schedules sorted by next due date (earliest first).</p>
        </div>
        <span class="whs-updated">Updated {{ now()->format('H:i') }}</span>
      </div>

      <div class="whs-card-list">
        @forelse ($schedules as $schedule)
          @php
            $severity = $schedule->isOverdue() ? 'critical' : ($schedule->isDueSoon() ? 'high' : 'low');
            $statusLabel = match($schedule->status) {
              'active' => 'Active',
              'paused' => 'Paused',
              'completed' => 'Completed',
              default => ucfirst($schedule->status)
            };
          @endphp

          <x-whs.card :severity="$severity">
            <div class="whs-card__header">
              <span class="whs-chip whs-chip--id">{{ $schedule->vehicle->registration_number ?? 'Equipment' }}</span>
              <span class="whs-chip whs-chip--status whs-chip--status-{{ strtolower($statusLabel) }}">
                {{ $statusLabel }}
              </span>
            </div>

            <div class="whs-card__body">
              <div>
                <h3>{{ $schedule->schedule_name }}</h3>
                <p>{{ ucfirst(str_replace('_', ' ', $schedule->schedule_type)) }} •
                  @if($schedule->recurrence_type === 'odometer_based')
                    Every {{ number_format($schedule->odometer_interval) }} km
                  @elseif($schedule->recurrence_type === 'engine_hours')
                    Every {{ number_format($schedule->engine_hours_interval) }} hrs
                  @else
                    {{ ucfirst(str_replace('_', ' ', $schedule->recurrence_type)) }}
                  @endif
                </p>
              </div>
              <div>
                <span class="whs-location-label">Next Due</span>
                <span @if($schedule->isOverdue()) class="text-danger" @elseif($schedule->isDueSoon()) class="text-warning" @endif>
                  {{ $schedule->next_due_date->format('d M Y') }}
                  @if($schedule->isOverdue())
                    <span class="whs-chip whs-chip--severity whs-chip--severity-critical ms-1">Overdue</span>
                  @elseif($schedule->isDueSoon())
                    <span class="whs-chip whs-chip--severity whs-chip--severity-high ms-1">Due Soon</span>
                  @endif
                </span>
              </div>
              <div>
                <span class="whs-location-label">Priority</span>
                <span class="whs-chip whs-chip--severity whs-chip--severity-{{ $schedule->priority === 'critical' ? 'critical' : ($schedule->priority === 'high' ? 'high' : ($schedule->priority === 'medium' ? 'medium' : 'low')) }}">
                  {{ ucfirst($schedule->priority) }}
                </span>
              </div>
              <div>
                <span class="whs-location-label">Recurrence</span>
                <span>
                  @if($schedule->recurrence_type === 'odometer_based')
                    Odometer-based
                  @elseif($schedule->recurrence_type === 'engine_hours')
                    Engine Hours
                  @else
                    {{ ucfirst(str_replace('_', ' ', $schedule->recurrence_type)) }}
                  @endif
                </span>
              </div>
            </div>

            <div class="whs-card__footer">
              <div class="whs-card__actions">
                <a href="{{ route('maintenance.show', $schedule) }}" class="whs-action-btn" aria-label="View schedule">
                  <i class="icon-base ti ti-external-link"></i>
                  <span>Open</span>
                </a>

                <a href="{{ route('maintenance.edit', $schedule) }}" class="whs-action-btn" aria-label="Edit schedule">
                  <i class="icon-base ti ti-edit"></i>
                  <span>Edit</span>
                </a>

                @if($schedule->status === 'active')
                  <form action="{{ route('maintenance.pause', $schedule) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="whs-action-btn whs-action-btn--warning">
                      <i class="icon-base ti ti-player-pause"></i>
                      <span>Pause</span>
                    </button>
                  </form>
                @endif

                @if($schedule->status === 'paused')
                  <form action="{{ route('maintenance.resume', $schedule) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="whs-action-btn whs-action-btn--success">
                      <i class="icon-base ti ti-player-play"></i>
                      <span>Resume</span>
                    </button>
                  </form>
                @endif

                <a href="{{ route('maintenance-logs.create', ['schedule_id' => $schedule->id]) }}" class="whs-action-btn">
                  <i class="icon-base ti ti-file-plus"></i>
                  <span>Work Order</span>
                </a>

                <button type="button" class="whs-action-btn whs-action-btn--danger" onclick="confirmDelete('{{ $schedule->id }}', '{{ $schedule->schedule_name }}')">
                  <i class="icon-base ti ti-trash"></i>
                  <span>Delete</span>
                </button>
              </div>
              <button class="whs-card__more">
                <i class="icon-base ti ti-dots"></i>
              </button>
            </div>
          </x-whs.card>
        @empty
          <div class="whs-empty">
            <div class="whs-empty__content">
              <i class="icon-base ti ti-calendar-check whs-empty__icon"></i>
              <h3>No maintenance schedules yet</h3>
              <p>No maintenance schedules have been created. Start tracking preventive maintenance for your fleet.</p>
              <a href="{{ route('maintenance.create') }}" class="whs-btn-primary whs-btn-primary--ghost">
                <i class="icon-base ti ti-plus me-2"></i>
                Create first schedule
              </a>
            </div>
          </div>
        @endforelse
      </div>
    </div>

    <aside class="whs-sidebar">
      <x-whs.sidebar-panel title="Schedule status">
        <ul class="whs-sidebar__stats">
          <li>
            <span>Active Schedules</span>
            <strong class="text-success">{{ $statistics['active'] }}</strong>
          </li>
          <li>
            <span>Overdue</span>
            <strong class="text-danger">{{ $statistics['overdue'] }}</strong>
          </li>
          <li>
            <span>Due Soon (14 days)</span>
            <strong class="text-warning">{{ $statistics['due_soon'] }}</strong>
          </li>
          <li>
            <span>Paused</span>
            <strong>{{ $statistics['paused'] }}</strong>
          </li>
        </ul>
        <p class="whs-sidebar__caption">
          Automated scheduling ensures preventive maintenance is performed on time, reducing breakdowns and extending vehicle life.
        </p>
      </x-whs.sidebar-panel>

      <x-whs.sidebar-panel title="Schedule types">
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
          <div style="padding: 0.75rem; background: linear-gradient(135deg, rgba(244, 246, 255, 0.96), rgba(228, 233, 255, 0.98)); border-radius: 12px; border: 1px solid rgba(0, 71, 255, 0.12);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Preventive</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Regular maintenance tasks</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Odometer-Based</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Triggered by distance traveled</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Engine Hours</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Based on engine running time</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Time-Based</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Monthly, quarterly, or annual</span>
          </div>
        </div>
      </x-whs.sidebar-panel>
    </aside>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Delete Maintenance Schedule</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="deleteForm" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <p>Are you sure you want to delete the schedule <strong id="deleteScheduleName"></strong>?</p>
          <div class="alert alert-warning mb-0">
            <i class="icon-base ti ti-alert-circle me-2"></i>
            This will also delete all associated maintenance logs and history. This action cannot be undone.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="icon-base ti ti-trash me-1"></i> Delete Schedule
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@section('page-script')
<script>
function confirmDelete(scheduleId, scheduleName) {
  document.getElementById('deleteScheduleName').textContent = scheduleName;
  document.getElementById('deleteForm').action = '/maintenance/' + scheduleId;
  new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endsection

