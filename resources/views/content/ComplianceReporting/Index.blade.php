@extends('layouts/layoutMaster')

@section('title', 'Compliance Reporting')

@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}">
@endsection

@section('content')
<!-- Welcome Header -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h4 class="mb-1">Compliance Reporting System</h4>
        <p class="mb-0">Track regulatory compliance, generate automated reports, and maintain audit trails</p>
      </div>
    </div>
  </div>
</div>

<!-- Quick Stats Overview -->
<div class="row g-4 mb-4">
  <!-- Total Reports -->
  <div class="col-sm-6 col-lg-3">
    <div class="card statistics-card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Total Reports</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $reports->total() ?? 0 }}</h4>
            </div>
            <small class="mb-0">All compliance reports</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="bx bx-file-find bx-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Draft Reports -->
  <div class="col-sm-6 col-lg-3">
    <div class="card statistics-card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Draft Reports</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $reports->where('status', 'draft')->count() }}</h4>
            </div>
            <small class="mb-0">In progress</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="bx bx-edit bx-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Under Review -->
  <div class="col-sm-6 col-lg-3">
    <div class="card statistics-card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Under Review</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $reports->where('status', 'under_review')->count() }}</h4>
            </div>
            <small class="mb-0">Awaiting approval</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="bx bx-time-five bx-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Published -->
  <div class="col-sm-6 col-lg-3">
    <div class="card statistics-card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Published</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $reports->where('status', 'published')->count() }}</h4>
            </div>
            <small class="mb-0">Finalized reports</small>
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
</div>

<!-- Compliance Reports Table -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Compliance Reports</h5>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createReportModal">
      <i class="bx bx-plus me-1"></i> Create Report
    </button>
  </div>
  <div class="card-body">
    <!-- Filters -->
    <div class="row mb-3">
      <div class="col-md-3">
        <label class="form-label">Report Type</label>
        <select class="form-select" id="filterReportType">
          <option value="">All Types</option>
          <option value="incident">Incident Reports</option>
          <option value="safety_audit">Safety Audits</option>
          <option value="training_compliance">Training Compliance</option>
          <option value="vehicle_compliance">Vehicle Compliance</option>
          <option value="monthly_summary">Monthly Summary</option>
          <option value="quarterly_review">Quarterly Review</option>
          <option value="annual_report">Annual Report</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Status</label>
        <select class="form-select" id="filterStatus">
          <option value="">All Statuses</option>
          <option value="draft">Draft</option>
          <option value="under_review">Under Review</option>
          <option value="approved">Approved</option>
          <option value="published">Published</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Period</label>
        <select class="form-select" id="filterPeriod">
          <option value="">All Periods</option>
          <option value="current_month">Current Month</option>
          <option value="last_month">Last Month</option>
          <option value="current_quarter">Current Quarter</option>
          <option value="last_quarter">Last Quarter</option>
          <option value="current_year">Current Year</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">&nbsp;</label>
        <button type="button" class="btn btn-outline-secondary w-100" onclick="resetFilters()">
          <i class="bx bx-reset me-1"></i> Reset Filters
        </button>
      </div>
    </div>

    <table id="reportsTable" class="table table-hover">
      <thead>
        <tr>
          <th>Report ID</th>
          <th>Title</th>
          <th>Report Type</th>
          <th>Period</th>
          <th>Status</th>
          <th>Generated By</th>
          <th>Generated Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($reports as $report)
        <tr>
          <td>
            <a href="{{ route('compliance.show', $report) }}" class="text-primary fw-medium">
              {{ $report->report_id }}
            </a>
          </td>
          <td>{{ $report->title }}</td>
          <td>
            <span class="badge bg-label-info">
              {{ match($report->report_type) {
                'incident' => 'Incident Reports',
                'safety_audit' => 'Safety Audit',
                'training_compliance' => 'Training Compliance',
                'vehicle_compliance' => 'Vehicle Compliance',
                'monthly_summary' => 'Monthly Summary',
                'quarterly_review' => 'Quarterly Review',
                'annual_report' => 'Annual Report',
                default => ucfirst(str_replace('_', ' ', $report->report_type))
              } }}
            </span>
          </td>
          <td>{{ $report->period_start->format('M Y') }} - {{ $report->period_end->format('M Y') }}</td>
          <td>
            <span class="badge bg-{{ match($report->status) {
              'draft' => 'secondary',
              'under_review' => 'warning',
              'approved' => 'info',
              'published' => 'success',
              default => 'secondary'
            } }}">
              {{ ucfirst(str_replace('_', ' ', $report->status)) }}
            </span>
          </td>
          <td>{{ $report->generated_by_user->name ?? 'System' }}</td>
          <td>{{ $report->created_at->format('d/m/Y H:i') }}</td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                <i class="bx bx-dots-vertical-rounded"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end">
                <a class="dropdown-item" href="{{ route('compliance.show', $report) }}">
                  <i class="bx bx-show me-1"></i> View
                </a>
                <a class="dropdown-item" href="{{ route('compliance.export', $report) }}">
                  <i class="bx bx-download me-1"></i> Download
                </a>
                @if($report->status === 'draft')
                <li><hr class="dropdown-divider"></li>
                <a class="dropdown-item" href="{{ route('compliance.edit', $report) }}">
                  <i class="bx bx-edit me-1"></i> Edit
                </a>
                <a class="dropdown-item text-primary" href="javascript:void(0);" onclick="submitReport('{{ $report->id }}', '{{ $report->title }}')">
                  <i class="bx bx-send me-1"></i> Submit for Review
                </a>
                @endif
                @if($report->status === 'under_review')
                <li><hr class="dropdown-divider"></li>
                <a class="dropdown-item text-success" href="javascript:void(0);" onclick="approveReport('{{ $report->id }}', '{{ $report->title }}')">
                  <i class="bx bx-check me-1"></i> Approve
                </a>
                @endif
                @if($report->status === 'approved')
                <li><hr class="dropdown-divider"></li>
                <a class="dropdown-item text-success" href="javascript:void(0);" onclick="publishReport('{{ $report->id }}', '{{ $report->title }}')">
                  <i class="bx bx-globe me-1"></i> Publish
                </a>
                @endif
                @if($report->status === 'draft')
                <li><hr class="dropdown-divider"></li>
                <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteReport('{{ $report->id }}', '{{ $report->title }}')">
                  <i class="bx bx-trash me-1"></i> Delete
                </a>
                @endif
              </div>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" class="text-center py-5">
            <div class="my-4">
              <i class="bx bx-file-find bx-lg text-muted mb-3 d-block"></i>
              <h5 class="text-muted">No compliance reports found</h5>
              <p class="text-muted">Create your first compliance report to get started</p>
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createReportModal">
                <i class="bx bx-plus me-1"></i> Create First Report
              </button>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- Create Report Modal -->
<div class="modal fade" id="createReportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create Compliance Report</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('compliance.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label for="title" class="form-label">Report Title *</label>
              <input type="text" id="title" name="title" class="form-control" placeholder="Enter report title" required>
            </div>
            <div class="col-md-6">
              <label for="report_type" class="form-label">Report Type *</label>
              <select id="report_type" name="report_type" class="form-select" required>
                <option value="">Select report type</option>
                <option value="incident">Incident Reports</option>
                <option value="safety_audit">Safety Audit</option>
                <option value="training_compliance">Training Compliance</option>
                <option value="vehicle_compliance">Vehicle Compliance</option>
                <option value="monthly_summary">Monthly Summary</option>
                <option value="quarterly_review">Quarterly Review</option>
                <option value="annual_report">Annual Report</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="reporting_period" class="form-label">Reporting Period *</label>
              <select id="reporting_period" name="reporting_period" class="form-select" required>
                <option value="">Select period</option>
                <option value="current_month">Current Month</option>
                <option value="last_month">Last Month</option>
                <option value="current_quarter">Current Quarter</option>
                <option value="last_quarter">Last Quarter</option>
                <option value="ytd">Year to Date</option>
                <option value="custom">Custom Date Range</option>
              </select>
            </div>
            <div class="col-md-6" id="period_start_container" style="display: none;">
              <label for="period_start" class="form-label">Period Start Date *</label>
              <input type="text" id="period_start" name="period_start" class="form-control flatpickr-date" placeholder="Select start date">
            </div>
            <div class="col-md-6" id="period_end_container" style="display: none;">
              <label for="period_end" class="form-label">Period End Date *</label>
              <input type="text" id="period_end" name="period_end" class="form-control flatpickr-date" placeholder="Select end date">
            </div>
            <div class="col-12">
              <label for="description" class="form-label">Description</label>
              <textarea id="description" name="description" class="form-control" rows="3" placeholder="Brief description of the report"></textarea>
            </div>
            <div class="col-md-6">
              <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" id="auto_generate_metrics" name="auto_generate_metrics" value="1" checked>
                <label class="form-check-label" for="auto_generate_metrics">
                  Auto-generate metrics from system data
                </label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" id="include_charts" name="include_charts" value="1" checked>
                <label class="form-check-label" for="include_charts">
                  Include charts and visualizations
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Create Report
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Submit Report Modal -->
<div class="modal fade" id="submitReportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Submit Report for Review</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="submitReportForm" method="POST">
        @csrf
        <div class="modal-body">
          <p class="mb-3">Submitting report: <strong id="submitReportTitle"></strong></p>
          <p class="text-muted mb-0">This report will be submitted for management review and approval.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-send me-1"></i> Submit Report
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Approve Report Modal -->
<div class="modal fade" id="approveReportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Approve Report</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="approveReportForm" method="POST">
        @csrf
        <div class="modal-body">
          <p class="mb-3">Approving report: <strong id="approveReportTitle"></strong></p>
          <p class="text-muted mb-0">This report will be marked as approved and ready for publishing.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class="bx bx-check me-1"></i> Approve Report
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Publish Report Modal -->
<div class="modal fade" id="publishReportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Publish Report</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="publishReportForm" method="POST">
        @csrf
        <div class="modal-body">
          <p class="mb-3">Publishing report: <strong id="publishReportTitle"></strong></p>
          <p class="text-muted mb-0">This report will be finalized and made available for distribution.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class="bx bx-globe me-1"></i> Publish Report
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="deleteForm" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <p class="mb-0">Are you sure you want to delete <strong id="deleteReportTitle"></strong>?</p>
          <p class="text-danger mb-0 mt-2">This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="bx bx-trash me-1"></i> Delete Report
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script>
$(document).ready(function() {
  // Initialize DataTables
  $('#reportsTable').DataTable({
    order: [[6, 'desc']],
    pageLength: 25,
    responsive: true,
    columnDefs: [
      { orderable: false, targets: 7 }
    ]
  });

  // Initialize Flatpickr
  if (typeof flatpickr !== 'undefined') {
    $('.flatpickr-date').flatpickr({
      dateFormat: "Y-m-d"
    });
  }

  // Show/hide custom date range fields
  $('#reporting_period').on('change', function() {
    if ($(this).val() === 'custom') {
      $('#period_start_container, #period_end_container').show();
    } else {
      $('#period_start_container, #period_end_container').hide();
    }
  });

  // Filter functionality
  $('#filterReportType, #filterStatus, #filterPeriod').on('change', function() {
    const reportType = $('#filterReportType').val();
    const status = $('#filterStatus').val();
    const period = $('#filterPeriod').val();

    const params = new URLSearchParams();
    if (reportType) params.append('report_type', reportType);
    if (status) params.append('status', status);
    if (period) params.append('period', period);

    window.location.href = '{{ route("compliance.index") }}?' + params.toString();
  });
});

// Reset Filters
function resetFilters() {
  window.location.href = '{{ route("compliance.index") }}';
}

// Submit Report Modal
function submitReport(reportId, reportTitle) {
  document.getElementById('submitReportTitle').textContent = reportTitle;
  document.getElementById('submitReportForm').action = `/compliance/${reportId}/submit`;
  new bootstrap.Modal(document.getElementById('submitReportModal')).show();
}

// Approve Report Modal
function approveReport(reportId, reportTitle) {
  document.getElementById('approveReportTitle').textContent = reportTitle;
  document.getElementById('approveReportForm').action = `/compliance/${reportId}/approve`;
  new bootstrap.Modal(document.getElementById('approveReportModal')).show();
}

// Publish Report Modal
function publishReport(reportId, reportTitle) {
  document.getElementById('publishReportTitle').textContent = reportTitle;
  document.getElementById('publishReportForm').action = `/compliance/${reportId}/publish`;
  new bootstrap.Modal(document.getElementById('publishReportModal')).show();
}

// Delete Report Modal
function deleteReport(reportId, reportTitle) {
  document.getElementById('deleteReportTitle').textContent = reportTitle;
  document.getElementById('deleteForm').action = `/compliance/${reportId}`;
  new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endsection
