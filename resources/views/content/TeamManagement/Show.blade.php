@extends('layouts/layoutMaster')

@section('title', 'Team Member Profile - Team Management')

@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
@endsection

@section('content')
<div class="row">
  <!-- Main Content Column -->
  <div class="col-xl-9 col-lg-8 col-md-8">
    <!-- Member Profile Header -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex align-items-start">
          <!-- Avatar -->
          <div class="me-4">
            <div class="avatar avatar-xl">
              <span class="avatar-initial rounded-circle bg-label-primary fs-1">
                {{ substr($member['name'] ?? 'JS', 0, 1) }}
              </span>
            </div>
          </div>

          <!-- Member Info -->
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h4 class="mb-1">{{ $member['name'] ?? 'John Smith' }}</h4>
                <p class="mb-2 text-muted">{{ $member['position'] ?? 'Safety Officer' }}</p>
                <div class="d-flex flex-wrap gap-2">
                  @php
                    $isActive = $member['is_active'] ?? true;
                    $role = $member['role'] ?? 'employee';
                  @endphp
                  <span class="badge bg-{{ $isActive ? 'success' : 'secondary' }}">
                    <i class='bx {{ $isActive ? "bx-check-circle" : "bx-x-circle" }} me-1'></i>
                    {{ $isActive ? 'Active' : 'Inactive' }}
                  </span>
                  <span class="badge bg-primary">
                    <i class='bx bx-shield me-1'></i>
                    {{ ucfirst($role) }}
                  </span>
                </div>
              </div>
            </div>

            <div class="mt-3">
              <div class="row">
                <div class="col-md-3 mb-2">
                  <small class="text-muted">Employee ID</small>
                  <p class="mb-0"><strong>{{ $member['employee_id'] ?? 'EMP-001' }}</strong></p>
                </div>
                <div class="col-md-3 mb-2">
                  <small class="text-muted">Branch</small>
                  <p class="mb-0"><strong>{{ $member['branch_name'] ?? 'Sydney Office' }}</strong></p>
                </div>
                <div class="col-md-3 mb-2">
                  <small class="text-muted">Email</small>
                  <p class="mb-0"><strong>{{ $member['email'] ?? 'john.smith@company.com' }}</strong></p>
                </div>
                <div class="col-md-3 mb-2">
                  <small class="text-muted">Phone</small>
                  <p class="mb-0"><strong>{{ $member['phone'] ?? '0412 345 678' }}</strong></p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Recent Activity</h5>
      </div>
      <div class="card-body">
        @php
          // In a real implementation, this would come from the database
          $activities = [
            ['action' => 'Reported Incident', 'details' => 'Near-miss incident at warehouse', 'date' => now()->subHours(2)->format('d/m/Y H:i'), 'icon' => 'bx-error-circle', 'color' => 'warning'],
            ['action' => 'Completed Training', 'details' => 'Forklift Safety Refresher', 'date' => now()->subDays(1)->format('d/m/Y H:i'), 'icon' => 'bx-graduation', 'color' => 'success'],
            ['action' => 'Equipment Inspection', 'details' => 'Forklift FK-001 - Passed', 'date' => now()->subDays(3)->format('d/m/Y H:i'), 'icon' => 'bx-check-circle', 'color' => 'success'],
            ['action' => 'Document Uploaded', 'details' => 'Updated PPE Certificate', 'date' => now()->subDays(5)->format('d/m/Y H:i'), 'icon' => 'bx-upload', 'color' => 'info'],
          ];
        @endphp

        @if(count($activities) > 0)
        <div class="timeline">
          @foreach($activities as $activity)
          <div class="timeline-item">
            <div class="timeline-event">
              <div class="timeline-event-icon bg-label-{{ $activity['color'] }}">
                <i class='bx {{ $activity['icon'] }}'></i>
              </div>
              <div>
                <p class="mb-1"><strong>{{ $activity['action'] }}</strong></p>
                <p class="mb-1">{{ $activity['details'] }}</p>
                <small class="text-muted">{{ $activity['date'] }}</small>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        @else
        <div class="alert alert-info mb-0">
          <i class='bx bx-info-circle me-2'></i>
          No recent activity recorded for this team member.
        </div>
        @endif
      </div>
    </div>

    <!-- Training Certifications -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Training & Certifications</h5>
      </div>
      <div class="card-body">
        @php
          // In a real implementation, this would come from the database
          $certifications = [
            ['name' => 'Forklift License', 'date_issued' => '15/01/2024', 'expiry' => '15/01/2027', 'status' => 'valid'],
            ['name' => 'First Aid Certificate', 'date_issued' => '10/03/2024', 'expiry' => '10/03/2027', 'status' => 'valid'],
            ['name' => 'Work Health & Safety Induction', 'date_issued' => '05/02/2024', 'expiry' => '05/02/2025', 'status' => 'expiring'],
            ['name' => 'Manual Handling Training', 'date_issued' => '20/12/2023', 'expiry' => '20/12/2024', 'status' => 'expired'],
          ];
        @endphp

        @if(count($certifications) > 0)
        <div class="table-responsive">
          <table id="certificationsTable" class="table table-hover">
            <thead>
              <tr>
                <th>Certification</th>
                <th>Date Issued</th>
                <th>Expiry Date</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($certifications as $cert)
              <tr>
                <td><strong>{{ $cert['name'] }}</strong></td>
                <td>{{ $cert['date_issued'] }}</td>
                <td>{{ $cert['expiry'] }}</td>
                <td>
                  @php
                    $statusClass = match($cert['status']) {
                      'valid' => 'success',
                      'expiring' => 'warning',
                      'expired' => 'danger',
                      default => 'secondary'
                    };
                  @endphp
                  <span class="badge bg-{{ $statusClass }}">{{ ucfirst($cert['status']) }}</span>
                </td>
                <td>
                  <button type="button" class="btn btn-sm btn-icon" title="View Certificate">
                    <i class='bx bx-show'></i>
                  </button>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="alert alert-info mb-0">
          <i class='bx bx-info-circle me-2'></i>
          No training certifications recorded for this team member.
        </div>
        @endif
      </div>
    </div>

    <!-- Incidents Involved -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Incidents Reported/Involved</h5>
      </div>
      <div class="card-body">
        @php
          // In a real implementation, this would come from the database
          $incidents = [
            ['id' => 'INC-2024-001', 'type' => 'Near Miss', 'date' => '18/10/2025', 'severity' => 'low', 'status' => 'resolved'],
            ['id' => 'INC-2024-002', 'type' => 'Property Damage', 'date' => '12/10/2025', 'severity' => 'medium', 'status' => 'investigating'],
            ['id' => 'INC-2024-003', 'type' => 'Safety Observation', 'date' => '05/10/2025', 'severity' => 'low', 'status' => 'resolved'],
          ];
        @endphp

        @if(count($incidents) > 0)
        <div class="table-responsive">
          <table id="incidentsTable" class="table table-hover">
            <thead>
              <tr>
                <th>Incident ID</th>
                <th>Type</th>
                <th>Date</th>
                <th>Severity</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($incidents as $incident)
              <tr>
                <td><strong>{{ $incident['id'] }}</strong></td>
                <td>{{ $incident['type'] }}</td>
                <td>{{ $incident['date'] }}</td>
                <td>
                  @php
                    $severityClass = match($incident['severity']) {
                      'critical' => 'danger',
                      'high' => 'danger',
                      'medium' => 'warning',
                      'low' => 'success',
                      default => 'secondary'
                    };
                  @endphp
                  <span class="badge bg-{{ $severityClass }}">{{ ucfirst($incident['severity']) }}</span>
                </td>
                <td>
                  @php
                    $statusClass = match($incident['status']) {
                      'resolved' => 'success',
                      'investigating' => 'warning',
                      'pending' => 'info',
                      default => 'secondary'
                    };
                  @endphp
                  <span class="badge bg-{{ $statusClass }}">{{ ucfirst($incident['status']) }}</span>
                </td>
                <td>
                  <button type="button" class="btn btn-sm btn-icon" title="View Incident">
                    <i class='bx bx-show'></i>
                  </button>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="alert alert-info mb-0">
          <i class='bx bx-info-circle me-2'></i>
          No incidents reported or involving this team member.
        </div>
        @endif
      </div>
    </div>

    <!-- Notes -->
    @if(isset($member['notes']) && $member['notes'])
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Additional Notes</h5>
      </div>
      <div class="card-body">
        <p class="mb-0" style="white-space: pre-wrap;">{{ $member['notes'] }}</p>
      </div>
    </div>
    @endif

    <!-- Record Information -->
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Record Information</h6>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-3 mb-2">
            <label class="form-label text-muted mb-1">Created</label>
            <p class="mb-0"><small>{{ $member['created_at'] ?? now()->format('d/m/Y H:i') }}</small></p>
          </div>

          <div class="col-md-3 mb-2">
            <label class="form-label text-muted mb-1">Last Updated</label>
            <p class="mb-0"><small>{{ $member['updated_at'] ?? now()->format('d/m/Y H:i') }}</small></p>
          </div>

          <div class="col-md-3 mb-2">
            <label class="form-label text-muted mb-1">Last Login</label>
            <p class="mb-0"><small>{{ $member['last_login'] ?? 'Never' }}</small></p>
          </div>

          <div class="col-md-3 mb-2">
            <label class="form-label text-muted mb-1">Branch</label>
            <p class="mb-0"><small>{{ $member['branch_name'] ?? 'Sydney Office' }}</small></p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Sidebar Column -->
  <div class="col-xl-3 col-lg-4 col-md-4">
    <!-- Quick Actions -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">Quick Actions</h6>
      </div>
      <div class="card-body">
        <div class="d-grid gap-2">
          @if(auth()->user()->hasRole(['Manager', 'Admin']))
          <a href="{{ route('team.edit', $member['id'] ?? '1') }}" class="btn btn-primary btn-sm">
            <i class='bx bx-edit me-1'></i> Edit Profile
          </a>
          @endif

          <button type="button" class="btn btn-outline-info btn-sm">
            <i class='bx bx-envelope me-1'></i> Send Email
          </button>

          <button type="button" class="btn btn-outline-secondary btn-sm">
            <i class='bx bx-phone me-1'></i> Call Member
          </button>

          @if(auth()->user()->hasRole(['Manager', 'Admin']))
          <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
            <i class='bx bx-key me-1'></i> Reset Password
          </button>

          @if($isActive ?? true)
          <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deactivateModal">
            <i class='bx bx-user-x me-1'></i> Deactivate Account
          </button>
          @else
          <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#activateModal">
            <i class='bx bx-user-check me-1'></i> Activate Account
          </button>
          @endif
          @endif

          <a href="{{ route('team.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class='bx bx-arrow-back me-1'></i> Back to Team
          </a>
        </div>
      </div>
    </div>

    <!-- Statistics -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">Member Statistics</h6>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label text-muted mb-1">Incidents Reported</label>
          <h4 class="mb-0">{{ $member['incidents_count'] ?? 3 }}</h4>
        </div>

        <div class="mb-3">
          <label class="form-label text-muted mb-1">Training Completed</label>
          <h4 class="mb-0">{{ $member['training_count'] ?? 12 }}</h4>
        </div>

        <div class="mb-3">
          <label class="form-label text-muted mb-1">Certifications</label>
          <h4 class="mb-0">{{ $member['certifications_count'] ?? 4 }}</h4>
        </div>

        <div class="mb-0">
          <label class="form-label text-muted mb-1">Days Since Last Login</label>
          <h4 class="mb-0">{{ $member['days_since_login'] ?? 2 }}</h4>
        </div>
      </div>
    </div>

    <!-- Contact Information -->
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Contact Information</h6>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label text-muted mb-1">Email</label>
          <p class="mb-0">
            <a href="mailto:{{ $member['email'] ?? 'john.smith@company.com' }}">
              {{ $member['email'] ?? 'john.smith@company.com' }}
            </a>
          </p>
        </div>

        <div class="mb-3">
          <label class="form-label text-muted mb-1">Phone</label>
          <p class="mb-0">
            <a href="tel:{{ $member['phone'] ?? '0412345678' }}">
              {{ $member['phone'] ?? '0412 345 678' }}
            </a>
          </p>
        </div>

        <div class="mb-0">
          <label class="form-label text-muted mb-1">Location</label>
          <p class="mb-0">{{ $member['branch_name'] ?? 'Sydney Office' }}</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reset Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('team.reset-password', $member['id'] ?? '1') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="alert alert-warning mb-3">
            <i class='bx bx-info-circle me-2'></i>
            <strong>Security Action:</strong> A password reset link will be sent to the user's email address.
          </div>

          <p class="mb-0">Send password reset instructions to <strong>{{ $member['email'] ?? 'john.smith@company.com' }}</strong>?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">
            <i class='bx bx-key me-1'></i> Send Reset Link
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Deactivate Account Modal -->
<div class="modal fade" id="deactivateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Deactivate Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('team.deactivate', $member['id'] ?? '1') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="alert alert-danger mb-3">
            <i class='bx bx-error-circle me-2'></i>
            <strong>Warning:</strong> This will immediately prevent the user from logging in!
          </div>

          <p class="mb-3">Are you sure you want to deactivate this account?</p>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="notify_deactivate" name="notify_user" value="1" checked>
            <label class="form-check-label" for="notify_deactivate">
              Send notification email to user
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class='bx bx-user-x me-1'></i> Deactivate Account
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Activate Account Modal -->
<div class="modal fade" id="activateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Activate Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('team.activate', $member['id'] ?? '1') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="alert alert-success mb-3">
            <i class='bx bx-check-circle me-2'></i>
            <strong>Account Activation:</strong> This will allow the user to log in again.
          </div>

          <p class="mb-3">Are you sure you want to activate this account?</p>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="notify_activate" name="notify_user" value="1" checked>
            <label class="form-check-label" for="notify_activate">
              Send notification email to user
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class='bx bx-user-check me-1'></i> Activate Account
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
<script>
$(document).ready(function() {
  'use strict';

  // Initialize DataTables for certifications
  if ($('#certificationsTable').length) {
    $('#certificationsTable').DataTable({
      order: [[2, 'asc']],
      pageLength: 5,
      responsive: true,
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
    });
  }

  // Initialize DataTables for incidents
  if ($('#incidentsTable').length) {
    $('#incidentsTable').DataTable({
      order: [[2, 'desc']],
      pageLength: 5,
      responsive: true,
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
    });
  }
});
</script>
@endsection
