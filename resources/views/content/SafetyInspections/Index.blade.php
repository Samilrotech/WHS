@php
  setcookie('contentLayout', 'wide', time() + 60 * 60 * 24 * 365, '/');
  $pageConfigs = ['contentLayout' => 'wide'];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Safety Inspections')

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

<!-- Critical Issues Alert -->
@if($statistics['critical_issues'] > 0)
<div class="alert alert-danger alert-dismissible mb-4" role="alert">
  <h5 class="alert-heading mb-2">
    <i class="icon-base ti ti-alert-octagon me-2"></i>
    {{ $statistics['critical_issues'] }} Critical Safety Issue(s) Require Immediate Attention
  </h5>
  <p class="mb-0">Critical non-compliance items have been identified and require escalation.</p>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@php
  $filterPills = [
    ['label' => 'All inspections', 'active' => true],
    ['label' => 'Critical Issues', 'active' => $statistics['critical_issues'] > 0],
    ['label' => 'Overdue', 'active' => $statistics['overdue'] > 0],
    ['label' => 'In Progress', 'active' => $statistics['in_progress'] > 0],
  ];
@endphp

<div class="whs-shell">
  <x-whs.hero
    eyebrow="Safety & Compliance"
    title="Safety Inspections"
    subtitle="Workplace safety inspections, equipment checks, and compliance audits with automated scoring, non-conformance management, and escalation workflows."
    :metric="true"
    metricLabel="Total inspections"
    :metricValue="$statistics['total_inspections']"
    metricCaption="Safety inspection registry"
    :searchRoute="route('safety-inspections.index')"
    searchPlaceholder="Search inspections, types, locations…"
    :createRoute="null"
    createLabel=""
    :filters="$filterPills"
  />

  <section class="whs-metrics">
    <x-whs.metric-card
      icon="ti-clipboard-check"
      iconVariant="brand"
      label="Total Inspections"
      :value="$statistics['total_inspections']"
      meta="All safety inspections"
    />

    <x-whs.metric-card
      icon="ti-circle-check"
      iconVariant="success"
      label="Pass Rate"
      :value="$statistics['pass_rate'] . '%'"
      meta="Average score: {{ $statistics['average_score'] ?? 0 }}%"
      :metaClass="$statistics['pass_rate'] >= 80 ? 'text-success' : ($statistics['pass_rate'] >= 60 ? 'text-warning' : 'text-danger')"
    />

    <x-whs.metric-card
      icon="ti-alert-triangle"
      iconVariant="critical"
      label="Critical Issues"
      :value="$statistics['critical_issues']"
      meta="Non-compliance items"
      metaClass="text-danger"
    />

    <x-whs.metric-card
      icon="ti-clock"
      iconVariant="warning"
      label="Overdue"
      :value="$statistics['overdue']"
      meta="Past scheduled date"
      metaClass="text-warning"
    />
  </section>

  <div class="whs-layout">
    <div class="whs-main">
      <div class="whs-section-heading">
        <div>
          <h2>Inspection register</h2>
          <p>Safety inspections sorted by scheduled date (most recent first).</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
          <span class="whs-updated">Updated {{ now()->format('H:i') }}</span>
          <button type="button" class="whs-btn-primary" data-bs-toggle="modal" data-bs-target="#createInspectionModal">
            <i class="icon-base ti ti-plus"></i>
            New inspection
          </button>
        </div>
      </div>

      <div class="whs-card-list">
        @forelse ($inspections as $inspection)
          @php
            $severity = $inspection->has_non_compliance && $inspection->critical_issues_count > 0 ? 'critical' :
                       ($inspection->isOverdue() ? 'high' :
                       ($inspection->has_non_compliance ? 'medium' : 'low'));
            $statusLabel = match($inspection->status) {
              'scheduled' => 'Scheduled',
              'in_progress' => 'In Progress',
              'completed' => 'Completed',
              'submitted' => 'Submitted',
              'approved' => 'Approved',
              'rejected' => 'Rejected',
              'cancelled' => 'Cancelled',
              default => ucfirst(str_replace('_', ' ', $inspection->status))
            };
            $typeLabel = ucfirst(str_replace('_', ' ', $inspection->inspection_type));
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
                <h3>{{ $typeLabel }}</h3>
                <p>{{ $inspection->location ?? 'Location not specified' }} •
                  Inspector: {{ $inspection->inspector->name ?? 'Unassigned' }}</p>
              </div>
              <div>
                <span class="whs-location-label">Scheduled Date</span>
                <span>
                  @if($inspection->scheduled_date)
                    @if($inspection->isOverdue())
                      <span class="text-danger fw-bold">
                        {{ $inspection->scheduled_date->format('d M Y') }}
                        <span class="whs-chip whs-chip--severity whs-chip--severity-critical ms-1">Overdue</span>
                      </span>
                    @else
                      {{ $inspection->scheduled_date->format('d M Y') }}
                    @endif
                  @else
                    <span class="text-muted">Not scheduled</span>
                  @endif
                </span>
              </div>
              <div>
                <span class="whs-location-label">Score</span>
                <span>
                  @if($inspection->inspection_score !== null)
                    <strong class="me-2">{{ $inspection->inspection_score }}%</strong>
                    @if($inspection->passed)
                      <span class="whs-chip whs-chip--severity whs-chip--severity-low">Passed</span>
                    @else
                      <span class="whs-chip whs-chip--severity whs-chip--severity-critical">Failed</span>
                    @endif
                  @else
                    <span class="text-muted">N/A</span>
                  @endif
                </span>
              </div>
              <div>
                <span class="whs-location-label">Non-Compliance</span>
                <span>
                  @if($inspection->has_non_compliance)
                    <span class="whs-chip whs-chip--severity whs-chip--severity-{{ $inspection->critical_issues_count > 0 ? 'critical' : 'high' }}">
                      {{ $inspection->non_compliance_count }} issue(s)
                    </span>
                  @else
                    None
                  @endif
                </span>
              </div>
            </div>

            <div class="whs-card__footer">
              <div class="whs-card__actions">
                <a href="{{ route('safety-inspections.show', $inspection) }}" class="whs-action-btn" aria-label="View inspection">
                  <i class="icon-base ti ti-external-link"></i>
                  <span>Open</span>
                </a>

                @if($inspection->status === 'scheduled')
                  <form action="{{ route('safety-inspections.start', $inspection) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="whs-action-btn whs-action-btn--success">
                      <i class="icon-base ti ti-player-play"></i>
                      <span>Start</span>
                    </button>
                  </form>
                @endif

                @if($inspection->status === 'in_progress')
                  <button type="button" class="whs-action-btn" onclick="openCompleteModal('{{ $inspection->id }}', '{{ $inspection->inspection_number }}')">
                    <i class="icon-base ti ti-circle-check"></i>
                    <span>Complete</span>
                  </button>
                @endif

                @if($inspection->status === 'completed')
                  <form action="{{ route('safety-inspections.submit', $inspection) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="whs-action-btn">
                      <i class="icon-base ti ti-send"></i>
                      <span>Submit</span>
                    </button>
                  </form>
                @endif

                @if($inspection->status === 'submitted')
                  <button type="button" class="whs-action-btn whs-action-btn--success" onclick="openApproveModal('{{ $inspection->id }}', '{{ $inspection->inspection_number }}')">
                    <i class="icon-base ti ti-check"></i>
                    <span>Approve</span>
                  </button>
                  <button type="button" class="whs-action-btn whs-action-btn--warning" onclick="openRejectModal('{{ $inspection->id }}', '{{ $inspection->inspection_number }}')">
                    <i class="icon-base ti ti-x"></i>
                    <span>Reject</span>
                  </button>
                @endif

                @if($inspection->has_non_compliance && in_array($inspection->status, ['completed', 'submitted', 'approved']))
                  <button type="button" class="whs-action-btn whs-action-btn--danger" onclick="openEscalateModal('{{ $inspection->id }}', '{{ $inspection->inspection_number }}')">
                    <i class="icon-base ti ti-alert-triangle"></i>
                    <span>Escalate</span>
                  </button>
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
              <p>No safety inspections have been created. Start tracking workplace safety compliance and equipment checks.</p>
              <button type="button" class="whs-btn-primary whs-btn-primary--ghost" data-bs-toggle="modal" data-bs-target="#createInspectionModal">
                <i class="icon-base ti ti-plus me-2"></i>
                Create first inspection
              </button>
            </div>
          </div>
        @endforelse
      </div>
    </div>

    <aside class="whs-sidebar">
      <x-whs.sidebar-panel title="Inspection status">
        <ul class="whs-sidebar__stats">
          <li>
            <span>Completed</span>
            <strong class="text-success">{{ $statistics['completed'] }}</strong>
          </li>
          <li>
            <span>In Progress</span>
            <strong class="text-warning">{{ $statistics['in_progress'] }}</strong>
          </li>
          <li>
            <span>Overdue</span>
            <strong class="text-danger">{{ $statistics['overdue'] }}</strong>
          </li>
          <li>
            <span>Non-Compliance</span>
            <strong class="text-danger">{{ $statistics['with_non_compliance'] }}</strong>
          </li>
          <li>
            <span>Pass Rate</span>
            <strong class="{{ $statistics['pass_rate'] >= 80 ? 'text-success' : 'text-warning' }}">{{ $statistics['pass_rate'] }}%</strong>
          </li>
        </ul>
        <p class="whs-sidebar__caption">
          Automated safety inspections with checklist scoring, non-conformance tracking, and escalation workflows ensure continuous compliance.
        </p>
      </x-whs.sidebar-panel>

      <x-whs.sidebar-panel title="Inspection types">
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
          <div style="padding: 0.75rem; background: linear-gradient(135deg, rgba(244, 246, 255, 0.96), rgba(228, 233, 255, 0.98)); border-radius: 12px; border: 1px solid rgba(0, 71, 255, 0.12);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Workplace Safety</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">General workplace hazard checks</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Equipment Safety</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Machinery and equipment checks</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Pre-Start Checklist</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Daily operational readiness</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Monthly Audit</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Comprehensive compliance review</span>
          </div>
        </div>
      </x-whs.sidebar-panel>
    </aside>
  </div>
</div>

<!-- Create Inspection Modal -->
<div class="modal fade" id="createInspectionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create New Inspection</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('safety-inspections.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label for="inspection_type" class="form-label">Inspection Type *</label>
              <select id="inspection_type" name="inspection_type" class="form-select" required>
                <option value="">Select type</option>
                <option value="workplace_safety">Workplace Safety Inspection</option>
                <option value="equipment_safety">Equipment Safety Checklist</option>
                <option value="contractor_induction">Contractor Induction</option>
                <option value="pre_start_checklist">Pre-Start Safety Check</option>
                <option value="safety_audit">Monthly Safety Audit</option>
                <option value="warehouse_safety">Warehouse Safety</option>
                <option value="office_safety">Office Safety</option>
                <option value="vehicle_safety">Vehicle Safety</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="scheduled_date" class="form-label">Scheduled Date</label>
              <input type="date" id="scheduled_date" name="scheduled_date" class="form-control" min="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-6">
              <label for="location" class="form-label">Location</label>
              <input type="text" id="location" name="location" class="form-control" placeholder="e.g., Warehouse 3">
            </div>
            <div class="col-md-6">
              <label for="area" class="form-label">Area/Zone</label>
              <input type="text" id="area" name="area" class="form-control" placeholder="e.g., Loading Bay">
            </div>
            <div class="col-md-6">
              <label for="asset_tag" class="form-label">Asset Tag (Optional)</label>
              <input type="text" id="asset_tag" name="asset_tag" class="form-control" placeholder="e.g., EQ-001">
            </div>
            <div class="col-12">
              <div class="alert alert-info mb-0">
                <i class="icon-base ti ti-info-circle me-2"></i>
                The inspection will be created and you can add checklist items on the details page.
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="icon-base ti ti-plus me-1"></i> Create Inspection
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Complete Inspection Modal -->
<div class="modal fade" id="completeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Complete Inspection</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="completeForm" method="POST">
        @csrf
        <div class="modal-body">
          <p>Complete inspection <strong id="completeInspectionNumber"></strong>?</p>
          <div class="mb-3">
            <label for="inspector_notes" class="form-label">Inspector Notes</label>
            <textarea id="inspector_notes" name="inspector_notes" class="form-control" rows="3" placeholder="Overall observations and comments..."></textarea>
          </div>
          <div class="alert alert-info mb-0">
            <i class="icon-base ti ti-info-circle me-2"></i>
            This will calculate the final inspection score and mark as completed.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="icon-base ti ti-circle-check me-1"></i> Complete
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Approve Inspection Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Approve Inspection</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="approveForm" method="POST">
        @csrf
        <div class="modal-body">
          <p>Approve inspection <strong id="approveInspectionNumber"></strong>?</p>
          <div class="mb-3">
            <label for="reviewer_comments" class="form-label">Reviewer Comments</label>
            <textarea id="reviewer_comments" name="reviewer_comments" class="form-control" rows="3" placeholder="Approval comments..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class="icon-base ti ti-check me-1"></i> Approve
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Reject Inspection Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reject Inspection</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="rejectForm" method="POST">
        @csrf
        <div class="modal-body">
          <p>Reject inspection <strong id="rejectInspectionNumber"></strong>?</p>
          <div class="mb-3">
            <label for="rejection_reason" class="form-label">Rejection Reason *</label>
            <textarea id="rejection_reason" name="rejection_reason" class="form-control" rows="3" required placeholder="Explain why this inspection is being rejected..."></textarea>
          </div>
          <div class="alert alert-warning mb-0">
            <i class="icon-base ti ti-alert-circle me-2"></i>
            The inspector will be notified and may need to re-submit.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">
            <i class="icon-base ti ti-x me-1"></i> Reject
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Escalate Issue Modal -->
<div class="modal fade" id="escalateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Escalate Safety Issue</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="escalateForm" method="POST">
        @csrf
        <div class="modal-body">
          <p>Escalate safety issues from <strong id="escalateInspectionNumber"></strong>?</p>
          <div class="mb-3">
            <label for="assigned_to_user_id" class="form-label">Assign To</label>
            <select id="assigned_to_user_id" name="assigned_to_user_id" class="form-select">
              <option value="">Select user</option>
              <!-- Users would be loaded dynamically -->
            </select>
          </div>
          <div class="alert alert-danger mb-0">
            <i class="icon-base ti ti-alert-circle me-2"></i>
            This will escalate non-compliance issues to management.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="icon-base ti ti-alert-triangle me-1"></i> Escalate
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@section('page-script')
<script>
// Open Complete Modal
function openCompleteModal(id, inspectionNumber) {
  document.getElementById('completeInspectionNumber').textContent = inspectionNumber;
  document.getElementById('completeForm').action = '/safety-inspections/' + id + '/complete';

  var completeModal = new bootstrap.Modal(document.getElementById('completeModal'));
  completeModal.show();
}

// Open Approve Modal
function openApproveModal(id, inspectionNumber) {
  document.getElementById('approveInspectionNumber').textContent = inspectionNumber;
  document.getElementById('approveForm').action = '/safety-inspections/' + id + '/approve';

  var approveModal = new bootstrap.Modal(document.getElementById('approveModal'));
  approveModal.show();
}

// Open Reject Modal
function openRejectModal(id, inspectionNumber) {
  document.getElementById('rejectInspectionNumber').textContent = inspectionNumber;
  document.getElementById('rejectForm').action = '/safety-inspections/' + id + '/reject';

  var rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
  rejectModal.show();
}

// Open Escalate Modal
function openEscalateModal(id, inspectionNumber) {
  document.getElementById('escalateInspectionNumber').textContent = inspectionNumber;
  document.getElementById('escalateForm').action = '/safety-inspections/' + id + '/escalate';

  var escalateModal = new bootstrap.Modal(document.getElementById('escalateModal'));
  escalateModal.show();
}
</script>
@endsection

