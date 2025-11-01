@php
  setcookie('contentLayout', 'wide', time() + 60 * 60 * 24 * 365, '/');
  $pageConfigs = ['contentLayout' => 'wide'];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Team Management')

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

<div class="whs-shell">

  {{-- Dense Table Feature Flag Notice (Phase 1-3 Rollout) --}}
  @if($useDenseTable ?? false)
  <div class="alert alert-info alert-dismissible mb-4" role="alert">
    <h6 class="alert-heading mb-2">
      <i class="bx bx-test-tube me-2"></i>
      New Dense Table UI Active
    </h6>
    <p class="mb-0">
      You're using the new high-density table interface as part of our gradual rollout.
      <a href="#" class="alert-link" data-bs-toggle="modal" data-bs-target="#denseTableInfoModal">Learn more</a>
    </p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <!-- Certifications Expiring Alert -->
  @if(($statistics['certifications_expiring'] ?? 0) > 0)
  <div class="alert alert-warning alert-dismissible mb-4" role="alert">
    <h5 class="alert-heading mb-2">
      <i class="icon-base ti ti-alert-triangle"></i>
      {{ $statistics['certifications_expiring'] }} Certification(s) Expiring Soon
    </h5>
    <p class="mb-0">Review and renew certifications within 30 days to maintain compliance and safety standards.</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <x-whs.hero
    eyebrow="Workforce Management"
    title="Team Management"
    subtitle="Centralized employee management with certifications tracking, leave management, and compliance monitoring across all branches."
    :metric="true"
    metricLabel="Total members"
    :metricValue="$statistics['total_members'] ?? 0"
    metricCaption="All team members"
    :searchRoute="route('teams.index')"
    searchPlaceholder="Search members, roles, branches…"
    :createRoute="route('teams.create')"
    createLabel="Add member"
    createModal="addMemberModal"
  />

  <section class="whs-metrics">
    <x-whs.metric-card
      icon="ti-users"
      iconVariant="brand"
      label="Total Members"
      :value="$statistics['total_members'] ?? 0"
      meta="All team members"
    />

    <x-whs.metric-card
      icon="ti-user-check"
      iconVariant="success"
      label="Active"
      :value="$statistics['active_members'] ?? 0"
      meta="Currently working"
      metaClass="text-success"
    />

    <x-whs.metric-card
      icon="ti-calendar-x"
      iconVariant="info"
      label="On Leave"
      :value="$statistics['on_leave'] ?? 0"
      meta="Temporarily unavailable"
      metaClass="text-info"
    />

    <x-whs.metric-card
      icon="ti-certificate"
      iconVariant="warning"
      label="Certs Expiring"
      :value="$statistics['certifications_expiring'] ?? 0"
      meta="Within 30 days"
      metaClass="text-warning"
    />
  </section>

  <div class="whs-layout">
    <div class="whs-main">
      <div class="whs-section-heading">
        <div>
          <h2>Team member directory</h2>
          <p>All employees with roles, certifications, and status tracking.</p>
        </div>
        <span class="whs-updated">Updated {{ now()->format('H:i') }}</span>
      </div>

      {{-- Export Button (GDPR Compliant) --}}
      @can('team.export')
      <div class="mb-3">
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#exportModal">
          <i class="bx bx-download"></i> Export Employees
        </button>
      </div>
      @endcan

      <div class="whs-card-list">
        @forelse ($members['data'] as $member)
          @php
            $severity = $member['status'] === 'suspended' ? 'critical' :
                       ($member['has_expiring_certs'] ? 'high' :
                       ($member['status'] === 'on_leave' ? 'medium' : 'low'));
          @endphp

          <x-whs.card :severity="$severity">
            <div class="whs-card__header">
              <span class="whs-chip whs-chip--id">{{ $member['employee_id'] }}</span>
              <span class="whs-chip whs-chip--status whs-chip--status-{{ strtolower($member['status']) }}">
                {{ ucfirst(str_replace('_', ' ', $member['status'])) }}
              </span>
            </div>

            <div class="whs-card__body">
              <div class="mb-3">
                <h3 class="mb-1">{{ $member['name'] }}</h3>
                <p class="sensei-micro-copy mb-0">{{ ucfirst($member['role']) }} &middot; {{ $member['branch_name'] }}</p>
              </div>
              <div class="sensei-meta-grid">
                <div>
                  <span>Contact</span>
                  <span>{{ $member['email'] }} &middot; {{ $member['phone'] }}</span>
                </div>
                <div>
                  <span>Certifications</span>
                  <span>
                    @if($member['certifications_count'] > 0)
                      <strong class="me-2">{{ $member['certifications_count'] }} Cert{{ $member['certifications_count'] > 1 ? 's' : '' }}</strong>
                      @if($member['has_expiring_certs'])
                        <span class="whs-chip whs-chip--severity whs-chip--severity-high">Expiring Soon</span>
                      @else
                        <span class="whs-chip whs-chip--severity whs-chip--severity-low">Current</span>
                      @endif
                    @else
                      <span class="text-muted">None</span>
                    @endif
                  </span>
                </div>
                <div>
                  <span>Assigned Vehicle</span>
                  @if($member['current_vehicle'])
                    @php $vehicle = $member['current_vehicle']; @endphp
                    <span>
                      <strong>{{ $vehicle['registration_number'] }}</strong> &middot; {{ $vehicle['make'] }} {{ $vehicle['model'] }}
                      <span class="d-block text-muted small">Since {{ $vehicle['assigned_human'] }}</span>
                    </span>
                  @else
                    <span class="text-muted">Not assigned</span>
                  @endif
                </div>
                <div>
                  <span>Last Inspection</span>
                  @if($member['latest_inspection'])
                    @php $inspection = $member['latest_inspection']; @endphp
                    @php $inspectionResult = $inspection['result'] ?? $inspection['status']; @endphp
                    @php $inspectionBadge = in_array($inspectionResult, ['fail_major','fail_critical']) ? 'danger' : (in_array($inspectionResult, ['pass','pass_minor']) ? 'success' : 'info'); @endphp
                    <span>
                      <strong class="me-2">{{ strtoupper(str_replace('_', ' ', $inspectionResult)) }}</strong>
                      <span class="badge bg-label-{{ $inspectionBadge }}">{{ ucfirst(str_replace('_', ' ', $inspectionResult)) }}</span>
                      <span class="d-block text-muted small">{{ $inspection['date_human'] }}</span>
                    </span>
                  @else
                    <span class="text-muted">No submissions yet</span>
                  @endif
                </div>
                <div>
                  <span>Last Active</span>
                  <span>{{ \Carbon\Carbon::parse($member['last_active'])->diffForHumans() }}</span>
                </div>
              </div>
            </div>

            <div class="whs-card__footer">
              <div class="whs-card__actions">
                <button type="button" class="whs-action-btn" data-drawer-target="employeeDrawer{{ $member['id'] }}" aria-label="Quick view {{ $member['name'] }}">
                  <i class="icon-base ti ti-eye"></i>
                  <span>Quick View</span>
                </button>

                <a href="{{ route('teams.edit', $member['id']) }}" class="whs-action-btn" aria-label="Edit details">
                  <i class="icon-base ti ti-edit"></i>
                  <span>Edit</span>
                </a>

                <button type="button" class="whs-action-btn" onclick="viewCertifications('{{ $member['id'] }}', '{{ $member['name'] }}')">
                  <i class="icon-base ti ti-certificate"></i>
                  <span>Certs</span>
                </button>

                <button type="button" class="whs-action-btn" onclick="viewTrainingHistory('{{ $member['id'] }}', '{{ $member['name'] }}')">
                  <i class="icon-base ti ti-book"></i>
                  <span>Training</span>
                </button>

                @if($member['status'] === 'active')
                  <form action="{{ route('teams.on-leave', $member['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Mark {{ $member['name'] }} as on leave?')">
                    @csrf
                    <button type="submit" class="whs-action-btn whs-action-btn--warning">
                      <i class="icon-base ti ti-calendar-x"></i>
                      <span>Leave</span>
                    </button>
                  </form>
                @endif

                @if($member['status'] === 'on_leave')
                  <form action="{{ route('teams.activate', $member['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Mark {{ $member['name'] }} as active?')">
                    @csrf
                    <button type="submit" class="whs-action-btn whs-action-btn--success">
                      <i class="icon-base ti ti-check"></i>
                      <span>Activate</span>
                    </button>
                  </form>
                @endif

                <form action="{{ route('teams.destroy', $member['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this team member?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="whs-action-btn whs-action-btn--danger">
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
              <i class="icon-base ti ti-users whs-empty__icon"></i>
              <h3>No team members yet</h3>
              <p>No team members have been added to the system. Start building your team directory.</p>
              <button type="button" class="whs-btn-primary whs-btn-primary--ghost" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                <i class="icon-base ti ti-plus me-2"></i>
                Add first member
              </button>
            </div>
          </div>
        @endforelse
      </div>
    </div>

    <aside class="whs-sidebar">
      <x-whs.sidebar-panel title="Member status">
        <ul class="whs-sidebar__stats">
          <li>
            <span>Active Members</span>
            <strong class="text-success">{{ $statistics['active_members'] ?? 0 }}</strong>
          </li>
          <li>
            <span>On Leave</span>
            <strong class="text-info">{{ $statistics['on_leave'] ?? 0 }}</strong>
          </li>
          <li>
            <span>Certifications Expiring</span>
            <strong class="text-warning">{{ $statistics['certifications_expiring'] ?? 0 }}</strong>
          </li>
        </ul>
        <p class="whs-sidebar__caption">
          Track employee availability, certification compliance, and workforce readiness across all branches.
        </p>
      </x-whs.sidebar-panel>

      <x-whs.sidebar-panel title="Common roles">
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
          <div style="padding: 0.75rem; background: linear-gradient(135deg, rgba(244, 246, 255, 0.96), rgba(228, 233, 255, 0.98)); border-radius: 12px; border: 1px solid rgba(0, 71, 255, 0.12);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Manager</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Strategic oversight and team leadership</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Supervisor</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Daily operations and team management</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Safety Officer</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">WHS compliance and safety oversight</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Operator</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Equipment operation and field work</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Technician/Driver</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Technical support and transportation</span>
          </div>
        </div>
      </x-whs.sidebar-panel>
    </aside>
  </div>
</div>

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Team Member</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('teams.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="row g-3">
            <!-- Full Name -->
            <div class="col-md-6">
              <label for="name" class="form-label">Full Name *</label>
              <input type="text" id="name" name="name" class="form-control" required placeholder="John Doe">
            </div>

            <!-- Employee ID -->
            <div class="col-md-6">
              <label for="employee_id" class="form-label">Employee ID *</label>
              <input type="text" id="employee_id" name="employee_id" class="form-control" required placeholder="EMP-001">
            </div>

            <!-- Email -->
            <div class="col-md-6">
              <label for="email" class="form-label">Email Address *</label>
              <input type="email" id="email" name="email" class="form-control" required placeholder="john.doe@company.com">
            </div>

            <!-- Phone -->
            <div class="col-md-6">
              <label for="phone" class="form-label">Phone Number *</label>
              <input type="tel" id="phone" name="phone" class="form-control" required placeholder="+61 400 000 000">
            </div>

            <!-- Role -->
            <div class="col-md-6">
              <label for="role" class="form-label">Role *</label>
              <select id="role" name="role" class="form-select" required>
                <option value="">Select role</option>
                <option value="manager">Manager</option>
                <option value="supervisor">Supervisor</option>
                <option value="safety_officer">Safety Officer</option>
                <option value="operator">Operator</option>
                <option value="technician">Technician</option>
                <option value="driver">Driver</option>
                <option value="administrator">Administrator</option>
              </select>
            </div>

            <!-- Branch -->
            <div class="col-md-6">
              <label for="branch_id" class="form-label">Branch *</label>
              <select id="branch_id" name="branch_id" class="form-select" required>
                <option value="">Select branch</option>
                @foreach($branches as $branch)
                  <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
              </select>
            </div>

            <!-- Emergency Contact -->
            <div class="col-md-6">
              <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
              <input type="text" id="emergency_contact_name" name="emergency_contact_name" class="form-control" placeholder="Jane Doe">
            </div>

            <!-- Emergency Phone -->
            <div class="col-md-6">
              <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone</label>
              <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" class="form-control" placeholder="+61 400 000 001">
            </div>

            <!-- Start Date -->
            <div class="col-md-6">
              <label for="start_date" class="form-label">Start Date *</label>
              <input type="date" id="start_date" name="start_date" class="form-control" required>
            </div>

            <!-- Status -->
            <div class="col-md-6">
              <label for="status" class="form-label">Status *</label>
              <select id="status" name="status" class="form-select" required>
                <option value="active" selected>Active</option>
                <option value="on_leave">On Leave</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <!-- Notes -->
            <div class="col-12">
              <label for="notes" class="form-label">Notes</label>
              <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Any additional information"></textarea>
            </div>
          </div>

          <div class="alert alert-info mt-3 mb-0">
            <small>
              <i class="bx bx-info-circle me-1"></i>
              After creation, you can add certifications and training records from the member's profile page.
            </small>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Member</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Certifications Modal -->
<div class="modal fade" id="certificationsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Certifications - <span id="certMemberName"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted">Certification management will be available in the full implementation.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Training History Modal -->
<div class="modal fade" id="trainingHistoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Training History - <span id="trainingMemberName"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted">Training history will be available in the full implementation.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- Export Modal (GDPR Compliant) --}}
@can('team.export')
<div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Export Employee Data</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('teams.export') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="alert alert-warning">
            <i class="bx bx-info-circle"></i>
            <strong>GDPR Notice:</strong> This export contains personal data. All exports are logged.
          </div>

          <input type="hidden" name="format" value="csv">

          <label class="form-label">Fields to Export</label>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="fields[]" value="employee_id" checked>
            <label class="form-check-label">Employee ID</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="fields[]" value="name" checked>
            <label class="form-check-label">Name <span class="badge bg-warning">PII</span></label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="fields[]" value="email" checked>
            <label class="form-check-label">Email <span class="badge bg-warning">PII</span></label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="fields[]" value="phone">
            <label class="form-check-label">Phone <span class="badge bg-warning">PII</span></label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Export CSV</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endcan

{{-- Dense Table Component Demo (Blocker #5) --}}
@if($useDenseTable ?? false)
<div class="card mt-4">
  <div class="card-header">
    <h5 class="card-title mb-0">
      <i class="bx bx-table me-2"></i>
      Dense Table Preview (New Component)
    </h5>
    <p class="text-muted small mb-0 mt-2">Demonstrating the new table-cell component with accessibility features</p>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Employee</th>
            <th>Status</th>
            <th>Branch</th>
            <th>Last Active</th>
            <th class="text-end">Incidents</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($members['data'] as $member)
          <tr>
            <x-whs.table-cell
              type="avatar"
              :value="$member['name']"
              :meta="substr($member['name'], 0, 2)"
              label="Employee name"
            />

            <x-whs.table-cell
              type="badge"
              :value="$member['status']"
              label="Employment status"
            />

            <x-whs.table-cell
              type="text"
              :value="$member['branch_name']"
              label="Branch location"
            />

            <x-whs.table-cell
              type="date"
              :value="$member['last_active']"
              :meta="true"
              label="Last activity date"
            />

            <x-whs.table-cell
              type="numeric"
              :value="$member['incidents_count'] ?? 0"
              align="right"
              label="Incident count"
            />

            <x-whs.table-cell type="actions" label="Available actions">
              <button
                type="button"
                class="btn btn-sm btn-icon btn-label-primary"
                data-drawer-target="employeeDrawer{{ $member['id'] }}"
                aria-label="View {{ $member['name'] }}"
              >
                <i class="bx bx-show"></i>
              </button>
              <a
                href="{{ route('teams.edit', $member['id']) }}"
                class="btn btn-sm btn-icon btn-label-secondary"
                aria-label="Edit {{ $member['name'] }}"
              >
                <i class="bx bx-edit"></i>
              </a>
            </x-whs.table-cell>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif

{{-- Side Drawer Component Demo (Blocker #5) - Employee Quick View --}}
@foreach($members['data'] as $member)
<x-whs.side-drawer
  id="employeeDrawer{{ $member['id'] }}"
  title="Employee Quick View"
  size="lg"
>
  <div class="mb-4">
    <div class="d-flex align-items-center gap-3 mb-3">
      <div class="avatar avatar-lg">
        <span class="avatar-initial rounded bg-label-primary fs-4">
          {{ substr($member['name'], 0, 2) }}
        </span>
      </div>
      <div>
        <h4 class="mb-1">{{ $member['name'] }}</h4>
        <p class="text-muted mb-0">{{ $member['email'] }}</p>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-6">
      <label class="form-label small text-muted">Employee ID</label>
      <p class="fw-semibold">{{ $member['employee_id'] }}</p>
    </div>
    <div class="col-6">
      <label class="form-label small text-muted">Status</label>
      <p>
        <span class="badge bg-{{ $member['status'] === 'active' ? 'success' : 'secondary' }}">
          {{ ucfirst($member['status']) }}
        </span>
      </p>
    </div>
    <div class="col-6">
      <label class="form-label small text-muted">Branch</label>
      <p class="fw-semibold">{{ $member['branch_name'] }}</p>
    </div>
    <div class="col-6">
      <label class="form-label small text-muted">Role</label>
      <p class="fw-semibold">{{ ucfirst($member['role']) }}</p>
    </div>
  </div>

  <div class="mb-4">
    <h6 class="mb-3">Contact Information</h6>
    <div class="row g-3">
      <div class="col-12">
        <label class="form-label small text-muted">Email</label>
        <p>{{ $member['email'] }}</p>
      </div>
      @if($member['phone'])
      <div class="col-12">
        <label class="form-label small text-muted">Phone</label>
        <p>{{ $member['phone'] }}</p>
      </div>
      @endif
    </div>
  </div>

  <div class="mb-4">
    <h6 class="mb-3">Activity Summary</h6>
    <div class="row g-3">
      <div class="col-6">
        <label class="form-label small text-muted">Incidents</label>
        <p class="fw-semibold fs-5 text-primary">{{ $member['incidents_count'] ?? 0 }}</p>
      </div>
      <div class="col-6">
        <label class="form-label small text-muted">Last Active</label>
        <p class="fw-semibold">{{ \Carbon\Carbon::parse($member['last_active'])->diffForHumans() }}</p>
      </div>
    </div>
  </div>

  @if($member['current_vehicle'])
  <div class="alert alert-info">
    <strong>Currently Assigned Vehicle:</strong><br>
    {{ $member['current_vehicle']['registration_number'] }} -
    {{ $member['current_vehicle']['make'] }} {{ $member['current_vehicle']['model'] }}
  </div>
  @endif

  <x-slot:footer>
    <button type="button" class="btn btn-label-secondary" data-drawer-close="employeeDrawer{{ $member['id'] }}">
      Close
    </button>
    <a href="{{ route('teams.show', $member['id']) }}" class="btn btn-primary">
      <i class="bx bx-external-link me-2"></i>
      View Full Profile
    </a>
    <a href="{{ route('teams.edit', $member['id']) }}" class="btn btn-label-primary">
      <i class="bx bx-edit me-2"></i>
      Edit Details
    </a>
  </x-slot:footer>
</x-whs.side-drawer>
@endforeach

{{-- Dense Table Info Modal --}}
@if($useDenseTable ?? false)
<div class="modal fade" id="denseTableInfoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Dense Table UI - Phase {{ now()->isBefore('2025-11-08') ? '1' : (now()->isBefore('2025-11-22') ? '2' : '3') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>You're part of our gradual rollout!</strong></p>
        <p>The new dense table interface features:</p>
        <ul>
          <li>✅ WCAG 2.1 AA accessibility compliance</li>
          <li>✅ Keyboard navigation support</li>
          <li>✅ Responsive design for mobile</li>
          <li>✅ Improved data density</li>
          <li>✅ Side drawer for quick actions</li>
        </ul>
        <hr>
        <p class="small text-muted mb-0">
          <strong>Rollout Schedule:</strong><br>
          Phase 1 (Nov 1-7): Sydney Operations Centre<br>
          Phase 2 (Nov 8-21): 50% A/B test<br>
          Phase 3 (Nov 22+): 100% rollout
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endif

@endsection

@section('page-script')
<script>
// View certifications
function viewCertifications(memberId, memberName) {
  document.getElementById('certMemberName').textContent = memberName;
  new bootstrap.Modal(document.getElementById('certificationsModal')).show();
}

// View training history
function viewTrainingHistory(memberId, memberName) {
  document.getElementById('trainingMemberName').textContent = memberName;
  new bootstrap.Modal(document.getElementById('trainingHistoryModal')).show();
}

</script>
@endsection



