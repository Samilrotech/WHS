@php
  setcookie('contentLayout', 'wide', time() + 60 * 60 * 24 * 365, '/');
  $pageConfigs = ['contentLayout' => 'wide'];
@endphp

@extends('layouts.layoutMaster')

@section('title', 'CAPA Management')

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
  $inProgressCapas = $statistics['in_progress'] ?? 0;
  $overdueCapas = $statistics['overdue'] ?? 0;
  $closedCapas = $statistics['closed'] ?? 0;

  $filterPills = [
    ['label' => 'All CAPAs', 'active' => true],
    ['label' => 'In Progress', 'active' => $inProgressCapas > 0],
    ['label' => 'Overdue', 'active' => $overdueCapas > 0],
    ['label' => 'Pending Approval', 'active' => $pendingApproval->count() > 0],
  ];
@endphp

<div class="whs-shell">
  <x-whs.hero
    eyebrow="Quality & Compliance"
    title="CAPA Management"
    subtitle="Corrective and Preventive Action tracking system with workflow automation, approval chains, and effectiveness verification."
    :metric="true"
    metricLabel="Active CAPAs"
    :metricValue="$statistics['total'] ?? 0"
    metricCaption="Live CAPA register across WHS4 network"
    :searchRoute="route('capa.index')"
    searchPlaceholder="Search CAPAs, assignees, actions…"
    :createRoute="route('capa.create')"
    createLabel="Create CAPA"
    :filters="$filterPills"
  />

  <section class="whs-metrics">
    <x-whs.metric-card
      icon="ti-checklist"
      iconVariant="brand"
      label="Total CAPAs"
      :value="$statistics['total'] ?? 0"
      meta="All corrective/preventive actions"
    />

    <x-whs.metric-card
      icon="ti-loader"
      iconVariant="warning"
      label="In Progress"
      :value="$inProgressCapas"
      meta="Active implementation"
      metaClass="text-warning"
    />

    <x-whs.metric-card
      icon="ti-alert-triangle"
      iconVariant="critical"
      label="Overdue"
      :value="$overdueCapas"
      meta="Past target completion date"
      metaClass="text-danger"
    />

    <x-whs.metric-card
      icon="ti-circle-check"
      iconVariant="success"
      label="Closed"
      :value="$closedCapas"
      meta="Verified and completed"
      metaClass="text-success"
    />
  </section>

  <div class="whs-layout">
    <div class="whs-main">
      <div class="whs-section-heading">
        <div>
          <h2>CAPA register</h2>
          <p>Corrective and Preventive Actions sorted by due date (most urgent first).</p>
        </div>
        <span class="whs-updated">Updated {{ now()->format('H:i') }}</span>
      </div>

      <div class="whs-card-list">
        @forelse ($capas as $capa)
          @php
            $severity = $capa->isOverdue() ? 'critical' : ($capa->priority === 'critical' || $capa->priority === 'high' ? 'high' : 'low');
            $statusLabel = match($capa->status) {
              'draft' => 'Draft',
              'submitted' => 'Pending Approval',
              'approved' => 'Approved',
              'in_progress' => 'In Progress',
              'completed' => 'Pending Verification',
              'verified' => 'Verified',
              'closed' => 'Closed',
              default => ucfirst($capa->status)
            };
          @endphp

          <x-whs.card :severity="$severity">
            <div class="whs-card__header">
              <span class="whs-chip whs-chip--id">{{ $capa->capa_number }}</span>
              <span class="whs-chip whs-chip--status whs-chip--status-{{ strtolower(str_replace(' ', '-', $statusLabel)) }}">
                {{ $statusLabel }}
              </span>
            </div>

            <div class="whs-card__body">
              <div>
                <h3>{{ $capa->title }}</h3>
                <p>{{ ucfirst($capa->type) }} • Due {{ $capa->target_completion_date->format('d M Y') }}
                  @if($capa->isOverdue())
                    • <span class="text-danger">Overdue</span>
                  @endif
                </p>
              </div>
              <div>
                <span class="whs-location-label">Priority</span>
                <span class="whs-chip whs-chip--severity whs-chip--severity-{{ $capa->priority === 'critical' ? 'critical' : ($capa->priority === 'high' ? 'high' : 'low') }}">
                  {{ ucfirst($capa->priority) }}
                </span>
              </div>
              <div>
                <span class="whs-location-label">Assigned To</span>
                <span>{{ $capa->assignedTo?->name ?? 'Unassigned' }}</span>
              </div>
              <div>
                <span class="whs-location-label">Progress</span>
                <div class="d-flex align-items-center" style="gap: 0.5rem;">
                  <div class="progress flex-grow-1" style="height: 6px;">
                    <div class="progress-bar bg-{{ $capa->completion_percentage >= 100 ? 'success' : ($capa->completion_percentage >= 50 ? 'warning' : 'danger') }}"
                         role="progressbar"
                         style="width: {{ $capa->completion_percentage }}%">
                    </div>
                  </div>
                  <span style="font-size: 0.75rem; color: rgba(51, 65, 85, 0.75);">{{ $capa->completion_percentage }}%</span>
                </div>
              </div>
            </div>

            <div class="whs-card__footer">
              <div class="whs-card__actions">
                <a href="{{ route('capa.show', $capa) }}" class="whs-action-btn" aria-label="View CAPA">
                  <i class="icon-base ti ti-external-link"></i>
                  <span>Open</span>
                </a>

                @if($capa->status === 'draft')
                  <a href="{{ route('capa.edit', $capa) }}" class="whs-action-btn" aria-label="Edit CAPA">
                    <i class="icon-base ti ti-edit"></i>
                    <span>Edit</span>
                  </a>
                  <form action="{{ route('capa.submit', $capa) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="whs-action-btn whs-action-btn--success">
                      <i class="icon-base ti ti-send"></i>
                      <span>Submit</span>
                    </button>
                  </form>
                @endif

                @if($capa->status === 'approved')
                  <form action="{{ route('capa.start', $capa) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="whs-action-btn whs-action-btn--warning">
                      <i class="icon-base ti ti-player-play"></i>
                      <span>Start</span>
                    </button>
                  </form>
                @endif

                @if($capa->status === 'in_progress')
                  <form action="{{ route('capa.complete', $capa) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="whs-action-btn whs-action-btn--success">
                      <i class="icon-base ti ti-check"></i>
                      <span>Complete</span>
                    </button>
                  </form>
                @endif

                <form action="{{ route('capa.destroy', $capa) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="whs-action-btn whs-action-btn--danger" onclick="return confirm('Delete this CAPA?')">
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
              <i class="icon-base ti ti-checklist whs-empty__icon"></i>
              <h3>No CAPAs yet</h3>
              <p>No Corrective or Preventive Actions have been created. Start tracking quality improvements.</p>
              <a href="{{ route('capa.create') }}" class="whs-btn-primary whs-btn-primary--ghost">
                <i class="icon-base ti ti-plus me-2"></i>
                Create first CAPA
              </a>
            </div>
          </div>
        @endforelse
      </div>

      @if($pendingApproval->count() > 0)
      <div class="whs-section-heading" style="margin-top: 2.5rem;">
        <div>
          <h2>Pending approval ({{ $pendingApproval->count() }})</h2>
          <p>CAPAs awaiting management review and approval.</p>
        </div>
      </div>

      <div class="whs-card-list">
        @foreach($pendingApproval as $capa)
          @php
            $severity = $capa->priority === 'critical' || $capa->priority === 'high' ? 'high' : 'low';
          @endphp

          <x-whs.card :severity="$severity">
            <div class="whs-card__header">
              <span class="whs-chip whs-chip--id">{{ $capa->capa_number }}</span>
              <span class="whs-chip whs-chip--status whs-chip--status-pending">
                Pending Approval
              </span>
            </div>

            <div class="whs-card__body">
              <div>
                <h3>{{ $capa->title }}</h3>
                <p>{{ ucfirst($capa->type) }} • Raised by {{ $capa->raisedBy->name }}</p>
              </div>
              <div>
                <span class="whs-location-label">Priority</span>
                <span class="whs-chip whs-chip--severity whs-chip--severity-{{ $capa->priority === 'critical' ? 'critical' : ($capa->priority === 'high' ? 'high' : 'low') }}">
                  {{ ucfirst($capa->priority) }}
                </span>
              </div>
            </div>

            <div class="whs-card__footer">
              <div class="whs-card__actions">
                <a href="{{ route('capa.show', $capa) }}" class="whs-action-btn whs-action-btn--success" aria-label="Review CAPA">
                  <i class="icon-base ti ti-external-link"></i>
                  <span>Review</span>
                </a>
              </div>
            </div>
          </x-whs.card>
        @endforeach
      </div>
      @endif
    </div>

    <aside class="whs-sidebar">
      <x-whs.sidebar-panel title="CAPA workflow">
        <ul class="whs-sidebar__stats">
          <li>
            <span>In Progress</span>
            <strong class="text-warning">{{ $inProgressCapas }}</strong>
          </li>
          <li>
            <span>Overdue</span>
            <strong class="text-danger">{{ $overdueCapas }}</strong>
          </li>
          <li>
            <span>Closed (Verified)</span>
            <strong class="text-success">{{ $closedCapas }}</strong>
          </li>
        </ul>
        <p class="whs-sidebar__caption">
          CAPAs track quality improvements with approval workflow and effectiveness verification.
        </p>
      </x-whs.sidebar-panel>

      <x-whs.sidebar-panel title="CAPA lifecycle">
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
          <div style="padding: 0.75rem; background: linear-gradient(135deg, rgba(244, 246, 255, 0.96), rgba(228, 233, 255, 0.98)); border-radius: 12px; border: 1px solid rgba(0, 71, 255, 0.12);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">1. Draft</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Initial creation and documentation</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">2. Approval</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Management review and sign-off</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">3. Implementation</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Execute corrective/preventive actions</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">4. Verification</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Confirm effectiveness of actions</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">5. Closure</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Close with documented evidence</span>
          </div>
        </div>
      </x-whs.sidebar-panel>
    </aside>
  </div>
</div>
@endsection

