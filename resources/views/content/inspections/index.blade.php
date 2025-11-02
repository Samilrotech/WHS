@php
  setcookie('contentLayout', 'wide', time() + 60 * 60 * 24 * 365, '/');
  $pageConfigs = ['contentLayout' => 'wide'];
@endphp

@extends('layouts.layoutMaster')

@section('title', 'Inspection Management')

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
  $pendingApproval = $statistics['pending_approval'] ?? 0;
  $criticalDefects = $statistics['with_critical_defects'] ?? 0;
  $inProgress = $statistics['in_progress'] ?? 0;

  $filterPills = [
    ['label' => 'All inspections', 'active' => true],
    ['label' => 'Pending Approval', 'active' => $pendingApproval > 0],
    ['label' => 'Critical Defects', 'active' => $criticalDefects > 0],
    ['label' => 'In Progress', 'active' => $inProgress > 0],
  ];
@endphp

<div class="whs-shell">
  <x-whs.hero
    eyebrow="Fleet & Quality"
    title="Inspection Management"
    subtitle="Vehicle inspections with automated checklists, defect tracking, and approval workflows across all branches."
    :metric="true"
    metricLabel="Total inspections"
    :metricValue="$statistics['total'] ?? 0"
    metricCaption="Inspection registry across WHS4 network"
    :searchRoute="route('inspections.index')"
    searchPlaceholder="Search inspections, vehicles, types…"
    :createRoute="route('inspections.create')"
    createLabel="New inspection"
    :filters="$filterPills"
  />

  <section class="whs-metrics">
    <x-whs.metric-card
      icon="ti-clipboard-check"
      iconVariant="brand"
      label="Total Inspections"
      :value="$statistics['total'] ?? 0"
      meta="All vehicle inspections"
    />

    <x-whs.metric-card
      icon="ti-clock"
      iconVariant="warning"
      label="Pending Approval"
      :value="$pendingApproval"
      meta="Awaiting review"
      metaClass="text-warning"
    />

    <x-whs.metric-card
      icon="ti-alert-triangle"
      iconVariant="critical"
      label="Critical Defects"
      :value="$criticalDefects"
      meta="Requires immediate action"
      metaClass="text-danger"
    />

    <x-whs.metric-card
      icon="ti-circle-check"
      iconVariant="success"
      label="In Progress"
      :value="$inProgress"
      meta="Currently being inspected"
      metaClass="text-info"
    />
  </section>

  <div class="whs-layout whs-layout--full-width">
    <div class="whs-main">
      <div class="whs-section-heading">
        <div>
          <h2>Inspection register</h2>
          <p>Vehicle inspections sorted by date (most recent first).</p>
        </div>
        <span class="whs-updated">Updated {{ now()->format('H:i') }}</span>
      </div>

      {{-- Dense Table View (Default) --}}
      @include('content.inspections._table-view')

      {{-- Old Card View (Deprecated) --}}
      <div class="whs-card-list" style="display: none;">
        @forelse ($inspections as $inspection)
          @php
            $severity = $inspection->critical_defects > 0 ? 'critical' : ($inspection->major_defects > 0 ? 'high' : ($inspection->minor_defects > 0 ? 'medium' : 'low'));
            $statusLabel = match($inspection->status) {
              'pending' => 'Pending',
              'in_progress' => 'In Progress',
              'completed' => 'Completed',
              'approved' => 'Approved',
              'rejected' => 'Rejected',
              'failed' => 'Failed',
              default => ucfirst($inspection->status)
            };
            $typeLabel = match($inspection->inspection_type) {
              'monthly_routine' => 'Monthly',
              'pre_trip' => 'Pre-Trip',
              'post_incident' => 'Post-Incident',
              'annual_compliance' => 'Annual',
              'maintenance_followup' => 'Maintenance',
              'random_spot_check' => 'Spot Check',
              default => ucfirst($inspection->inspection_type)
            };
            $resultLabel = match($inspection->overall_result) {
              'pass' => 'Pass',
              'pass_minor' => 'Pass (Minor)',
              'fail_major' => 'Fail (Major)',
              'fail_critical' => 'Fail (Critical)',
              default => '-'
            };
          @endphp

          <x-whs.card :severity="$severity">
            <div class="whs-card__header">
              <span class="whs-chip whs-chip--id">{{ $inspection->inspection_number }}</span>
              <span class="whs-chip whs-chip--status whs-chip--status-{{ strtolower(str_replace(' ', '-', $statusLabel)) }}">
                {{ $statusLabel }}
              </span>
            </div>

            <div class="whs-card__body">
              <div>
                <h3 class="{{ $inspection->vehicle ? '' : 'text-muted' }}">
                  {{ $inspection->vehicle?->registration_number ?? 'Vehicle unavailable' }}
                </h3>
                <p class="mb-0">
                  {{ $typeLabel }} • {{ $inspection->inspection_date?->format('d M Y') ?? 'Not started' }}
                </p>
                @unless($inspection->vehicle)
                  <p class="text-muted mb-0 small">Vehicle record has been archived or removed.</p>
                @endunless
              </div>
              <div>
                <span class="whs-location-label">Overall Result</span>
                <span>
                  @if($inspection->overall_result === 'pass')
                    <span class="whs-chip whs-chip--severity whs-chip--severity-low">{{ $resultLabel }}</span>
                  @elseif($inspection->overall_result === 'pass_minor')
                    <span class="whs-chip whs-chip--severity whs-chip--severity-medium">{{ $resultLabel }}</span>
                  @elseif(in_array($inspection->overall_result, ['fail_major', 'fail_critical']))
                    <span class="whs-chip whs-chip--severity whs-chip--severity-critical">{{ $resultLabel }}</span>
                  @else
                    -
                  @endif
                </span>
              </div>
              <div>
                <span class="whs-location-label">Defects</span>
                <span>
                  @if($inspection->critical_defects > 0)
                    <span class="whs-chip whs-chip--severity whs-chip--severity-critical">{{ $inspection->critical_defects }} Critical</span>
                  @elseif($inspection->major_defects > 0)
                    <span class="whs-chip whs-chip--severity whs-chip--severity-high">{{ $inspection->major_defects }} Major</span>
                  @elseif($inspection->minor_defects > 0)
                    <span class="whs-chip whs-chip--severity whs-chip--severity-medium">{{ $inspection->minor_defects }} Minor</span>
                  @else
                    None
                  @endif
                </span>
              </div>
              <div>
                <span class="whs-location-label">Type</span>
                <span>{{ $typeLabel }}</span>
              </div>
            </div>

            <div class="whs-card__footer">
              <div class="whs-card__actions">
                <a href="{{ route('inspections.show', $inspection) }}" class="whs-action-btn" aria-label="View inspection">
                  <i class="icon-base ti ti-external-link"></i>
                  <span>Open</span>
                </a>

                @if(in_array($inspection->status, ['pending', 'in_progress']))
                  <a href="{{ route('inspections.edit', $inspection) }}" class="whs-action-btn" aria-label="Edit inspection">
                    <i class="icon-base ti ti-edit"></i>
                    <span>Edit</span>
                  </a>
                @endif

                @if($inspection->status === 'pending')
                  <form action="{{ route('inspections.start', $inspection) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="whs-action-btn whs-action-btn--success">
                      <i class="icon-base ti ti-player-play"></i>
                      <span>Start</span>
                    </button>
                  </form>
                @endif

                @if($inspection->status === 'completed')
                  <form action="{{ route('inspections.approve', $inspection) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="whs-action-btn whs-action-btn--success">
                      <i class="icon-base ti ti-circle-check"></i>
                      <span>Approve</span>
                    </button>
                  </form>
                  <form action="{{ route('inspections.reject', $inspection) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="whs-action-btn whs-action-btn--danger" onclick="return confirm('Provide rejection reason?')">
                      <i class="icon-base ti ti-x-circle"></i>
                      <span>Reject</span>
                    </button>
                  </form>
                @endif

                @if($inspection->status !== 'approved')
                  <form action="{{ route('inspections.destroy', $inspection) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="whs-action-btn whs-action-btn--danger" onclick="return confirm('Delete this inspection?')">
                      <i class="icon-base ti ti-trash"></i>
                      <span>Delete</span>
                    </button>
                  </form>
                @endif
              </div>
              <button class="whs-card__more">
                <i class="icon-base ti ti-dots"></i>
              </button>
            </div>
          </x-whs.card>
        @empty
          <div class="whs-empty">
            <div class="whs-empty__content">
              <i class="icon-base ti ti-clipboard-check whs-empty__icon"></i>
              <h3>No inspections yet</h3>
              <p>No vehicle inspections have been created. Start tracking your fleet inspection compliance.</p>
              <a href="{{ route('inspections.create') }}" class="whs-btn-primary whs-btn-primary--ghost">
                <i class="icon-base ti ti-plus me-2"></i>
                Create first inspection
              </a>
            </div>
          </div>
        @endforelse
      </div>
    </div>

    <aside class="whs-sidebar">
      <x-whs.sidebar-panel title="Inspection workflow">
        <ul class="whs-sidebar__stats">
          <li>
            <span>Pending Approval</span>
            <strong class="text-warning">{{ $pendingApproval }}</strong>
          </li>
          <li>
            <span>Critical Defects</span>
            <strong class="text-danger">{{ $criticalDefects }}</strong>
          </li>
          <li>
            <span>In Progress</span>
            <strong class="text-info">{{ $inProgress }}</strong>
          </li>
        </ul>
        <p class="whs-sidebar__caption">
          Automated inspection scheduling ensures fleet compliance with monthly routine checks and pre-trip safety verification.
        </p>
      </x-whs.sidebar-panel>

      <x-whs.sidebar-panel title="Inspection process">
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
          <div style="padding: 0.75rem; background: linear-gradient(135deg, rgba(244, 246, 255, 0.96), rgba(228, 233, 255, 0.98)); border-radius: 12px; border: 1px solid rgba(0, 71, 255, 0.12);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">1. Schedule</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Automated monthly reminders</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">2. Inspect</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Complete checklist with QR scan</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">3. Document</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Photo evidence for defects</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">4. Approve</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Supervisor review and approval</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">5. Remediate</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Track defect resolution</span>
          </div>
        </div>
      </x-whs.sidebar-panel>
    </aside>
  </div>
</div>
@endsection

