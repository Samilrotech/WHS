@php
  setcookie('contentLayout', 'wide', time() + 60 * 60 * 24 * 365, '/');
  $pageConfigs = ['contentLayout' => 'wide'];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Permit to Work')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
@endsection

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

<!-- Expired Permits Alert -->
@if(($statistics['expired_today'] ?? 0) > 0)
<div class="alert alert-danger alert-dismissible mb-4" role="alert">
  <h5 class="alert-heading mb-2">
    <i class="icon-base ti ti-alert-octagon me-2"></i>
    {{ $statistics['expired_today'] }} Permit(s) Expired Today
  </h5>
  <p class="mb-0">Action required: Review and close out expired permits. Continuing work without valid permits violates safety protocols.</p>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@php
  $filterPills = [
    ['label' => 'All permits', 'active' => true],
    ['label' => 'Pending Approval', 'active' => ($statistics['pending_approval'] ?? 0) > 0],
    ['label' => 'Expired Today', 'active' => ($statistics['expired_today'] ?? 0) > 0],
    ['label' => 'Active', 'active' => ($statistics['active_permits'] ?? 0) > 0],
  ];
@endphp

<div class="whs-shell">
  <x-whs.hero
    eyebrow="High-Risk Work Management"
    title="Permit to Work"
    subtitle="High-risk work authorization system with hot work, confined space, height work, and electrical permits with approval workflows and expiry tracking."
    :metric="true"
    metricLabel="Total permits"
    :metricValue="$statistics['total_permits'] ?? 0"
    metricCaption="Work permit registry"
    :searchRoute="route('permit-to-work.index')"
    searchPlaceholder="Search permits, types, locations…"
    :createRoute="null"
    createLabel=""
    :filters="$filterPills"
  />

  <section class="whs-metrics">
    <x-whs.metric-card
      icon="ti-file-check"
      iconVariant="brand"
      label="Total Permits"
      :value="$statistics['total_permits'] ?? 0"
      meta="All work permits"
    />

    <x-whs.metric-card
      icon="ti-shield-check"
      iconVariant="success"
      label="Active Permits"
      :value="$statistics['active_permits'] ?? 0"
      meta="Currently active"
      metaClass="text-success"
    />

    <x-whs.metric-card
      icon="ti-clock"
      iconVariant="warning"
      label="Pending Approval"
      :value="$statistics['pending_approval'] ?? 0"
      meta="Awaiting authorization"
      metaClass="text-warning"
    />

    <x-whs.metric-card
      icon="ti-calendar-x"
      iconVariant="critical"
      label="Expired Today"
      :value="$statistics['expired_today'] ?? 0"
      meta="Require closeout"
      metaClass="text-danger"
    />
  </section>

  <div class="whs-layout">
    <div class="whs-main">
      <div class="whs-section-heading">
        <div>
          <h2>Permit register</h2>
          <p>Work permits sorted by validity period (most recent first).</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
          <span class="whs-updated">Updated {{ now()->format('H:i') }}</span>
          <button type="button" class="whs-btn-primary" data-bs-toggle="modal" data-bs-target="#createPermitModal">
            <i class="icon-base ti ti-plus"></i>
            New permit
          </button>
        </div>
      </div>

      <div class="whs-card-list">
        @forelse ($permits['data'] as $permit)
          @php
            $isExpired = \Carbon\Carbon::parse($permit['end_date'])->isPast();
            $severity = $isExpired ? 'critical' :
                       ($permit['status'] === 'suspended' ? 'high' :
                       ($permit['risk_level'] === 'critical' || $permit['risk_level'] === 'high' ? 'medium' : 'low'));
            $statusLabel = match($permit['status']) {
              'draft' => 'Draft',
              'pending_approval' => 'Pending Approval',
              'approved' => 'Approved',
              'active' => 'Active',
              'suspended' => 'Suspended',
              'closed' => 'Closed',
              'rejected' => 'Rejected',
              default => ucfirst(str_replace('_', ' ', $permit['status']))
            };
            $typeLabel = ucfirst(str_replace('_', ' ', $permit['permit_type']));
          @endphp

          <x-whs.card :severity="$severity">
            <div class="whs-card__header">
              <span class="whs-chip whs-chip--id">{{ $permit['permit_number'] }}</span>
              <span class="whs-chip whs-chip--status whs-chip--status-{{ strtolower(str_replace(' ', '-', $statusLabel)) }}">
                {{ $statusLabel }}
              </span>
            </div>

            <div class="whs-card__body">
              <div>
                <h3>{{ $typeLabel }}</h3>
                <p class="text-truncate" style="max-width: 400px;">{{ $permit['work_description'] }}</p>
              </div>
              <div>
                <span class="whs-location-label">Location</span>
                <span>{{ $permit['location'] }}</span>
              </div>
              <div>
                <span class="whs-location-label">Valid Period</span>
                <span>
                  {{ \Carbon\Carbon::parse($permit['start_date'])->format('d M Y H:i') }} →
                  {{ \Carbon\Carbon::parse($permit['end_date'])->format('d M Y H:i') }}
                  @if($isExpired)
                    <span class="whs-chip whs-chip--severity whs-chip--severity-critical ms-1">Expired</span>
                  @endif
                </span>
              </div>
              <div>
                <span class="whs-location-label">Risk Level</span>
                <span class="whs-chip whs-chip--severity whs-chip--severity-{{ match($permit['risk_level']) {
                  'low' => 'low',
                  'medium' => 'medium',
                  'high' => 'high',
                  'critical' => 'critical',
                  default => 'low'
                } }}">
                  {{ ucfirst($permit['risk_level']) }}
                </span>
              </div>
              <div>
                <span class="whs-location-label">Requester</span>
                <span>{{ $permit['requester_name'] }}</span>
              </div>
            </div>

            <div class="whs-card__footer">
              <div class="whs-card__actions">
                <a href="{{ route('permit-to-work.show', $permit['id']) }}" class="whs-action-btn" aria-label="View permit">
                  <i class="icon-base ti ti-external-link"></i>
                  <span>Open</span>
                </a>

                <a href="{{ route('permit-to-work.edit', $permit['id']) }}" class="whs-action-btn" aria-label="Edit permit">
                  <i class="icon-base ti ti-edit"></i>
                  <span>Edit</span>
                </a>

                @if($permit['status'] === 'pending_approval')
                  <button type="button" class="whs-action-btn whs-action-btn--success" onclick="approvePermit('{{ $permit['id'] }}', '{{ $permit['permit_number'] }}')">
                    <i class="icon-base ti ti-check"></i>
                    <span>Approve</span>
                  </button>
                @endif

                @if(in_array($permit['status'], ['approved', 'active']))
                  <button type="button" class="whs-action-btn" onclick="closePermit('{{ $permit['id'] }}', '{{ $permit['permit_number'] }}')">
                    <i class="icon-base ti ti-lock"></i>
                    <span>Close</span>
                  </button>
                @endif

                @if($permit['status'] === 'draft')
                  <form action="{{ route('permit-to-work.destroy', $permit['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this permit?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="whs-action-btn whs-action-btn--danger">
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
              <i class="icon-base ti ti-file-check whs-empty__icon"></i>
              <h3>No permits yet</h3>
              <p>No work permits have been created. Start managing high-risk work with permit-to-work authorization.</p>
              <button type="button" class="whs-btn-primary whs-btn-primary--ghost" data-bs-toggle="modal" data-bs-target="#createPermitModal">
                <i class="icon-base ti ti-plus me-2"></i>
                Create first permit
              </button>
            </div>
          </div>
        @endforelse
      </div>
    </div>

    <aside class="whs-sidebar">
      <x-whs.sidebar-panel title="Permit status">
        <ul class="whs-sidebar__stats">
          <li>
            <span>Active Permits</span>
            <strong class="text-success">{{ $statistics['active_permits'] ?? 0 }}</strong>
          </li>
          <li>
            <span>Pending Approval</span>
            <strong class="text-warning">{{ $statistics['pending_approval'] ?? 0 }}</strong>
          </li>
          <li>
            <span>Expired Today</span>
            <strong class="text-danger">{{ $statistics['expired_today'] ?? 0 }}</strong>
          </li>
        </ul>
        <p class="whs-sidebar__caption">
          Permit-to-work system ensures high-risk operations are authorized, controlled, and tracked with mandatory approval workflows.
        </p>
      </x-whs.sidebar-panel>

      <x-whs.sidebar-panel title="Permit types">
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
          <div style="padding: 0.75rem; background: linear-gradient(135deg, rgba(244, 246, 255, 0.96), rgba(228, 233, 255, 0.98)); border-radius: 12px; border: 1px solid rgba(0, 71, 255, 0.12);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Hot Work</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Welding, cutting, grinding operations</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Confined Space</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Entry into restricted areas</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Height Work</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Elevated work platforms</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Electrical Work</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Live electrical systems</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Excavation</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Ground works and trenching</span>
          </div>
        </div>
      </x-whs.sidebar-panel>
    </aside>
  </div>
</div>

<!-- Create Permit Modal -->
<div class="modal fade" id="createPermitModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create New Work Permit</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('permit-to-work.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="row g-3">
            <!-- Permit Type -->
            <div class="col-md-6">
              <label for="permit_type" class="form-label">Permit Type *</label>
              <select id="permit_type" name="permit_type" class="form-select" required>
                <option value="">Select type</option>
                <option value="hot_work">Hot Work Permit</option>
                <option value="confined_space">Confined Space Entry</option>
                <option value="working_at_height">Working at Height</option>
                <option value="electrical">Electrical Work</option>
                <option value="excavation">Excavation Work</option>
                <option value="lifting">Lifting Operations</option>
                <option value="isolation">Energy Isolation</option>
                <option value="general">General Work Permit</option>
              </select>
            </div>

            <!-- Risk Level -->
            <div class="col-md-6">
              <label for="risk_level" class="form-label">Risk Level *</label>
              <select id="risk_level" name="risk_level" class="form-select" required>
                <option value="">Select risk level</option>
                <option value="low">Low Risk</option>
                <option value="medium">Medium Risk</option>
                <option value="high">High Risk</option>
                <option value="critical">Critical Risk</option>
              </select>
            </div>

            <!-- Work Description -->
            <div class="col-12">
              <label for="work_description" class="form-label">Work Description *</label>
              <textarea id="work_description" name="work_description" class="form-control" rows="3" required placeholder="Describe the work to be performed"></textarea>
            </div>

            <!-- Location -->
            <div class="col-md-6">
              <label for="location" class="form-label">Work Location *</label>
              <input type="text" id="location" name="location" class="form-control" required placeholder="e.g., Warehouse Section A">
            </div>

            <!-- Requester -->
            <div class="col-md-6">
              <label for="requester_name" class="form-label">Requester Name *</label>
              <input type="text" id="requester_name" name="requester_name" class="form-control" required placeholder="Person requesting permit">
            </div>

            <!-- Start Date -->
            <div class="col-md-6">
              <label for="start_date" class="form-label">Start Date & Time *</label>
              <input type="text" id="start_date" name="start_date" class="form-control flatpickr-datetime" required placeholder="Select date & time">
            </div>

            <!-- End Date -->
            <div class="col-md-6">
              <label for="end_date" class="form-label">End Date & Time *</label>
              <input type="text" id="end_date" name="end_date" class="form-control flatpickr-datetime" required placeholder="Select date & time">
            </div>

            <!-- Equipment Required -->
            <div class="col-12">
              <label for="equipment_required" class="form-label">Equipment/Tools Required</label>
              <textarea id="equipment_required" name="equipment_required" class="form-control" rows="2" placeholder="List any special equipment or tools needed"></textarea>
            </div>

            <!-- Safety Precautions -->
            <div class="col-12">
              <label for="safety_precautions" class="form-label">Safety Precautions *</label>
              <textarea id="safety_precautions" name="safety_precautions" class="form-control" rows="3" required placeholder="List all safety measures and precautions"></textarea>
            </div>
          </div>

          <div class="alert alert-info mt-3 mb-0">
            <small>
              <i class="icon-base ti ti-info-circle me-1"></i>
              After creation, the permit will be in Draft status. You can complete details and submit for approval.
            </small>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create Permit</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Approve Permit Modal -->
<div class="modal fade" id="approvePermitModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Approve Work Permit</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="approvePermitForm" method="POST">
        @csrf
        <div class="modal-body">
          <p>Are you sure you want to approve permit <strong id="approvePermitNumber"></strong>?</p>

          <div class="mb-3">
            <label for="approver_name" class="form-label">Approver Name *</label>
            <input type="text" id="approver_name" name="approver_name" class="form-control" required value="{{ auth()->user()->name ?? '' }}">
          </div>

          <div class="mb-3">
            <label for="approval_notes" class="form-label">Approval Notes</label>
            <textarea id="approval_notes" name="approval_notes" class="form-control" rows="3" placeholder="Any conditions or notes"></textarea>
          </div>

          <div class="alert alert-success mb-0">
            <small>
              <i class="icon-base ti ti-shield-check me-1"></i>
              Once approved, the permit will be valid for the specified period and workers can commence work.
            </small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class="icon-base ti ti-check me-1"></i> Approve Permit
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Close Permit Modal -->
<div class="modal fade" id="closePermitModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Close Work Permit</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="closePermitForm" method="POST">
        @csrf
        <div class="modal-body">
          <p>Close permit <strong id="closePermitNumber"></strong>?</p>

          <div class="mb-3">
            <label for="work_completed" class="form-label">Work Completion Status *</label>
            <select id="work_completed" name="work_completed" class="form-select" required>
              <option value="">Select status</option>
              <option value="completed">Work Completed Successfully</option>
              <option value="partially_completed">Partially Completed</option>
              <option value="cancelled">Work Cancelled</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="closeout_notes" class="form-label">Closeout Notes *</label>
            <textarea id="closeout_notes" name="closeout_notes" class="form-control" rows="3" required placeholder="Describe work performed and any observations"></textarea>
          </div>

          <div class="mb-3">
            <label for="closed_by" class="form-label">Closed By *</label>
            <input type="text" id="closed_by" name="closed_by" class="form-control" required value="{{ auth()->user()->name ?? '' }}">
          </div>

          <div class="alert alert-warning mb-0">
            <small>
              <i class="icon-base ti ti-alert-circle me-1"></i>
              Closing a permit is final. Ensure all work is complete and the area is safe before closing.
            </small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-info">
            <i class="icon-base ti ti-lock me-1"></i> Close Permit
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@section('page-script')
<script>
'use strict';

$(document).ready(function() {
  // Initialize Flatpickr for datetime
  if (typeof flatpickr !== 'undefined') {
    $('.flatpickr-datetime').flatpickr({
      enableTime: true,
      dateFormat: "Y-m-d H:i",
      time_24hr: true,
      minuteIncrement: 15
    });
  }
});

// Approve permit
function approvePermit(permitId, permitNumber) {
  $('#approvePermitNumber').text(permitNumber);
  $('#approvePermitForm').attr('action', '/permit-to-work/' + permitId + '/approve');
  $('#approvePermitModal').modal('show');
}

// Close permit
function closePermit(permitId, permitNumber) {
  $('#closePermitNumber').text(permitNumber);
  $('#closePermitForm').attr('action', '/permit-to-work/' + permitId + '/close');
  $('#closePermitModal').modal('show');
}
</script>
@endsection

