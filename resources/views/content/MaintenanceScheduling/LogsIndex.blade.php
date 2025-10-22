@extends('layouts/layoutMaster')

@section('title', 'Maintenance Logs - Work Orders')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
@endsection

@section('content')
<h4 class="mb-1">Maintenance Logs & Work Orders</h4>
<p class="mb-4">Track all maintenance activities and service records</p>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
  <!-- Total Logs Card -->
  <div class="col-sm-6 col-lg-3">
    <div class="card statistics-card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Total Work Orders</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $statistics['total_logs'] ?? 0 }}</h4>
            </div>
            <small class="mb-0">All service records</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="bx bx-file-blank bx-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Pending Approval Card -->
  <div class="col-sm-6 col-lg-3">
    <div class="card statistics-card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Pending Approval</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $statistics['pending'] ?? 0 }}</h4>
              @if(($statistics['pending'] ?? 0) > 0)
              <span class="text-warning ms-1">
                <i class="bx bx-time"></i>
              </span>
              @endif
            </div>
            <small class="mb-0">Awaiting review</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="bx bx-hourglass bx-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- In Progress Card -->
  <div class="col-sm-6 col-lg-3">
    <div class="card statistics-card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">In Progress</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $statistics['in_progress'] ?? 0 }}</h4>
            </div>
            <small class="mb-0">Currently working</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-info">
              <i class="bx bx-wrench bx-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Completed Card -->
  <div class="col-sm-6 col-lg-3">
    <div class="card statistics-card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Completed</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $statistics['completed'] ?? 0 }}</h4>
            </div>
            <small class="mb-0">Finished work</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-success">
              <i class="bx bx-check-circle bx-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Safety Critical Card -->
  <div class="col-sm-6 col-lg-3">
    <div class="card statistics-card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Safety Critical</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $statistics['safety_critical'] ?? 0 }}</h4>
            </div>
            <small class="mb-0">Critical repairs</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-danger">
              <i class="bx bx-shield-alt bx-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Total Cost Card -->
  <div class="col-sm-6 col-lg-3">
    <div class="card statistics-card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Total Spend</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">${{ number_format($statistics['total_cost'] ?? 0, 2) }}</h4>
            </div>
            <small class="mb-0">All maintenance costs</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-success">
              <i class="bx bx-dollar bx-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Logs Table Card -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Work Orders</h5>
    <div class="card-action d-flex gap-2">
      <a href="{{ route('maintenance-logs.create') }}" class="btn btn-primary">
        <i class="bx bx-plus me-1"></i> New Work Order
      </a>
    </div>
  </div>
  <div class="card-body">
    <!-- Filters -->
    <div class="row mb-3">
      <div class="col-md-3">
        <label for="filterStatus" class="form-label">Status</label>
        <select id="filterStatus" class="form-select form-select-sm">
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
          <option value="verified">Verified</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <div class="col-md-3">
        <label for="filterType" class="form-label">Type</label>
        <select id="filterType" class="form-select form-select-sm">
          <option value="">All Types</option>
          <option value="scheduled">Scheduled</option>
          <option value="unscheduled">Unscheduled</option>
          <option value="inspection_followup">Inspection Followup</option>
          <option value="emergency">Emergency</option>
        </select>
      </div>
      <div class="col-md-3">
        <label for="filterVehicle" class="form-label">Vehicle</label>
        <select id="filterVehicle" class="form-select form-select-sm">
          <option value="">All Vehicles</option>
          @foreach($vehicles as $vehicle)
          <option value="{{ $vehicle->id }}">{{ $vehicle->registration_number }} - {{ $vehicle->make }} {{ $vehicle->model }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label for="filterSafety" class="form-label">Safety Critical</label>
        <select id="filterSafety" class="form-select form-select-sm">
          <option value="">All</option>
          <option value="yes">Yes</option>
          <option value="no">No</option>
        </select>
      </div>
    </div>

    <!-- DataTable -->
    <table id="logsTable" class="table table-hover">
      <thead>
        <tr>
          <th>Work Order #</th>
          <th>Vehicle</th>
          <th>Type</th>
          <th>Service Date</th>
          <th>Performed By</th>
          <th>Total Cost</th>
          <th>Status</th>
          <th>Safety</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($logs as $log)
        <tr>
          <td>
            <a href="{{ route('maintenance-logs.show', $log) }}" class="text-body fw-semibold">
              {{ $log->work_order_number }}
            </a>
          </td>
          <td>
            @if($log->vehicle)
            <a href="{{ route('vehicles.show', $log->vehicle) }}" class="text-body">
              {{ $log->vehicle->registration_number }}
            </a>
            @else
            <span class="text-muted">N/A</span>
            @endif
          </td>
          <td>
            @php
            $typeClass = match($log->maintenance_type) {
              'scheduled' => 'success',
              'unscheduled' => 'warning',
              'inspection_followup' => 'info',
              'emergency' => 'danger',
              default => 'secondary'
            };
            @endphp
            <span class="badge badge-light-{{ $typeClass }}">{{ ucfirst(str_replace('_', ' ', $log->maintenance_type)) }}</span>
          </td>
          <td>{{ $log->service_date->format('d/m/Y') }}</td>
          <td>
            @if($log->performer)
            {{ $log->performer->name }}
            @else
            <span class="text-muted">Not assigned</span>
            @endif
          </td>
          <td>
            @if($log->total_cost > 0)
            <span class="fw-semibold">${{ number_format($log->total_cost, 2) }}</span>
            @else
            <span class="text-muted">N/A</span>
            @endif
          </td>
          <td>
            @php
            $statusClass = match($log->status) {
              'pending' => 'warning',
              'approved' => 'info',
              'in_progress' => 'primary',
              'completed' => 'success',
              'verified' => 'success',
              'cancelled' => 'danger',
              default => 'secondary'
            };
            @endphp
            <span class="badge bg-{{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $log->status)) }}</span>
            @if($log->isOverdue())
            <i class="bx bx-error-circle text-danger ms-1" title="Overdue"></i>
            @endif
          </td>
          <td>
            @if($log->safety_critical)
            <span class="badge bg-danger">
              <i class="bx bx-shield-alt"></i> Critical
            </span>
            @else
            <span class="text-muted">-</span>
            @endif
          </td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                <i class="bx bx-dots-vertical-rounded"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end">
                <a class="dropdown-item" href="{{ route('maintenance-logs.show', $log) }}">
                  <i class="bx bx-show me-1"></i> View Details
                </a>
                <a class="dropdown-item" href="{{ route('maintenance-logs.edit', $log) }}">
                  <i class="bx bx-edit me-1"></i> Edit Work Order
                </a>

                @if($log->status === 'pending')
                <form action="{{ route('maintenance-logs.approve', $log) }}" method="POST" class="d-inline">
                  @csrf
                  <button type="submit" class="dropdown-item">
                    <i class="bx bx-check me-1"></i> Approve
                  </button>
                </form>
                @endif

                @if($log->status === 'approved' || $log->status === 'in_progress')
                <form action="{{ route('maintenance-logs.complete', $log) }}" method="POST" class="d-inline">
                  @csrf
                  <button type="submit" class="dropdown-item">
                    <i class="bx bx-check-circle me-1"></i> Mark Complete
                  </button>
                </form>
                @endif

                @if($log->status === 'completed')
                <a class="dropdown-item" href="javascript:void(0);"
                   onclick="openVerifyModal('{{ $log->id }}', '{{ $log->work_order_number }}')">
                  <i class="bx bx-check-double me-1"></i> Verify Quality
                </a>
                @endif

                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="javascript:void(0);"
                   onclick="confirmDelete('{{ $log->id }}', '{{ $log->work_order_number }}')">
                  <i class="bx bx-trash me-1"></i> Delete
                </a>
              </div>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- Verify Quality Modal -->
<div class="modal fade" id="verifyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Verify Work Quality</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="verifyForm" method="POST">
        @csrf
        <div class="modal-body">
          <p>Verify the quality of work for <strong id="verifyLogNumber"></strong></p>

          <div class="mb-3">
            <label for="quality_rating" class="form-label">Quality Rating *</label>
            <select id="quality_rating" name="quality_rating" class="form-select" required>
              <option value="">Select rating</option>
              <option value="excellent">Excellent</option>
              <option value="good">Good</option>
              <option value="satisfactory">Satisfactory</option>
              <option value="poor">Poor</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="verification_notes" class="form-label">Verification Notes</label>
            <textarea id="verification_notes" name="verification_notes" class="form-control" rows="3"
                      placeholder="Enter quality verification notes..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class="bx bx-check-double me-1"></i> Verify Quality
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Delete Work Order</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="deleteForm" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <p>Are you sure you want to delete work order <strong id="deleteLogNumber"></strong>?</p>
          <div class="alert alert-warning mb-0">
            <i class="bx bx-error-circle me-2"></i>
            This will permanently remove all service records and associated data. This action cannot be undone.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="bx bx-trash me-1"></i> Delete Work Order
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
  // Initialize DataTable
  var table = $('#logsTable').DataTable({
    order: [[3, 'desc']], // Sort by Service Date (descending)
    pageLength: 25,
    responsive: true,
    dom: '<"card-header"<"head-label"><"dt-action-buttons text-end"B>><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
  });

  // Filter by Status
  $('#filterStatus').on('change', function() {
    table.column(6).search(this.value).draw();
  });

  // Filter by Type
  $('#filterType').on('change', function() {
    table.column(2).search(this.value).draw();
  });

  // Filter by Vehicle
  $('#filterVehicle').on('change', function() {
    const vehicleId = this.value;
    table.column(1).search(vehicleId).draw();
  });

  // Filter by Safety Critical
  $('#filterSafety').on('change', function() {
    const value = this.value;
    if (value === 'yes') {
      table.column(7).search('Critical').draw();
    } else if (value === 'no') {
      table.column(7).search('^((?!Critical).)*$', true, false).draw();
    } else {
      table.column(7).search('').draw();
    }
  });
});

// Verify quality modal
function openVerifyModal(logId, logNumber) {
  document.getElementById('verifyLogNumber').textContent = logNumber;
  document.getElementById('verifyForm').action = '/maintenance-logs/' + logId + '/verify';

  var verifyModal = new bootstrap.Modal(document.getElementById('verifyModal'));
  verifyModal.show();
}

// Delete confirmation
function confirmDelete(logId, logNumber) {
  document.getElementById('deleteLogNumber').textContent = logNumber;
  document.getElementById('deleteForm').action = '/maintenance-logs/' + logId;

  var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
  deleteModal.show();
}
</script>
@endsection
