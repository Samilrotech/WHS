@extends('layouts/layoutMaster')

@section('title', 'View Compliance Report - Compliance Reporting')

@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
@endsection

@section('content')
<div class="row">
  <!-- Main Content Column -->
  <div class="col-xl-9 col-lg-8 col-md-8">
    <!-- Report Header -->
    <div class="card mb-4">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-1">{{ $report->title }}</h4>
            <div class="text-muted">
              <small>
                <i class='bx bx-calendar me-1'></i>
                {{ $report->period_start->format('d/m/Y') }} - {{ $report->period_end->format('d/m/Y') }}
              </small>
            </div>
          </div>
          <div>
            <span class="badge bg-{{ $report->getStatusBadgeColor() }} fs-6">
              {{ ucfirst($report->status) }}
            </span>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-3 mb-3">
            <label class="form-label text-muted">Report Number</label>
            <h5 class="mb-0">{{ $report->report_number }}</h5>
          </div>

          <div class="col-md-3 mb-3">
            <label class="form-label text-muted">Report Type</label>
            <h5 class="mb-0">
              @php
                $typeIcons = [
                  'periodic' => 'bx-calendar',
                  'audit' => 'bx-shield',
                  'incident-based' => 'bx-error-circle',
                  'regulatory' => 'bx-file',
                  'custom' => 'bx-customize',
                ];
                $icon = $typeIcons[$report->report_type] ?? 'bx-file';
              @endphp
              <i class='bx {{ $icon }} me-1'></i>
              {{ $report->getReportTypeLabel() }}
            </h5>
          </div>

          <div class="col-md-3 mb-3">
            <label class="form-label text-muted">Period</label>
            <h5 class="mb-0">{{ ucfirst($report->period) }}</h5>
          </div>

          <div class="col-md-3 mb-3">
            <label class="form-label text-muted">Report Date</label>
            <h5 class="mb-0">{{ $report->report_date->format('d/m/Y') }}</h5>
          </div>
        </div>

        @if($report->file_path)
        <div class="row mt-2">
          <div class="col-12">
            <div class="alert alert-info mb-0">
              <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                  <i class='bx bx-file bx-lg me-3'></i>
                  <div>
                    <strong>{{ $report->file_name }}</strong><br>
                    <small class="text-muted">{{ $report->formatted_file_size }} • Uploaded {{ $report->created_at->format('d/m/Y') }}</small>
                  </div>
                </div>
                <div>
                  <a href="{{ route('compliance-reports.download', $report) }}" class="btn btn-sm btn-primary">
                    <i class='bx bx-download me-1'></i> Download
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
        @endif
      </div>
    </div>

    <!-- Executive Summary -->
    @if($report->executive_summary)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Executive Summary</h5>
      </div>
      <div class="card-body">
        <p class="mb-0" style="white-space: pre-wrap;">{{ $report->executive_summary }}</p>
      </div>
    </div>
    @endif

    <!-- Key Findings -->
    @if($report->key_findings)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Key Findings</h5>
      </div>
      <div class="card-body">
        <p class="mb-0" style="white-space: pre-wrap;">{{ $report->key_findings }}</p>
      </div>
    </div>
    @endif

    <!-- Recommendations -->
    @if($report->recommendations)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Recommendations</h5>
      </div>
      <div class="card-body">
        <p class="mb-0" style="white-space: pre-wrap;">{{ $report->recommendations }}</p>
      </div>
    </div>
    @endif

    <!-- Compliance Metrics -->
    @if($report->metrics && count($report->metrics) > 0)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Compliance Metrics</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Metric</th>
                <th>Value</th>
                <th>Target</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach($report->metrics as $metric)
              <tr>
                <td><strong>{{ $metric['name'] ?? 'N/A' }}</strong></td>
                <td>{{ $metric['value'] ?? 'N/A' }}</td>
                <td>{{ $metric['target'] ?? 'N/A' }}</td>
                <td>
                  @php
                    $status = $metric['status'] ?? 'unknown';
                    $statusClass = match($status) {
                      'compliant', 'achieved' => 'success',
                      'partial', 'in-progress' => 'warning',
                      'non-compliant', 'not-achieved' => 'danger',
                      default => 'secondary'
                    };
                  @endphp
                  <span class="badge bg-{{ $statusClass }}">{{ ucfirst($status) }}</span>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    @endif

    <!-- Requirements Included -->
    @if($report->requirements_included && count($report->requirements_included) > 0)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Requirements Included in This Report</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="requirementsTable" class="table table-hover">
            <thead>
              <tr>
                <th>Requirement</th>
                <th>Category</th>
                <th>Compliance Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($report->requirements_included as $req)
              <tr>
                <td>
                  <strong>{{ $req['code'] ?? 'N/A' }}</strong><br>
                  <small class="text-muted">{{ $req['title'] ?? 'N/A' }}</small>
                </td>
                <td>{{ $req['category'] ?? 'N/A' }}</td>
                <td>
                  @php
                    $status = $req['compliance_status'] ?? 'unknown';
                    $statusClass = match($status) {
                      'compliant' => 'success',
                      'partial' => 'warning',
                      'non-compliant' => 'danger',
                      default => 'secondary'
                    };
                  @endphp
                  <span class="badge bg-{{ $statusClass }}">{{ ucfirst($status) }}</span>
                </td>
                <td>
                  @if(isset($req['id']))
                  <a href="{{ route('compliance-requirements.show', $req['id']) }}" class="btn btn-sm btn-icon" title="View Requirement">
                    <i class='bx bx-show'></i>
                  </a>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    @endif

    <!-- Approval Information -->
    @if($report->approved_at || $report->reviewer || $report->approver)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Approval & Review Information</h5>
      </div>
      <div class="card-body">
        <div class="row">
          @if($report->reviewer)
          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Reviewed By</label>
            <p class="mb-0">
              <i class='bx bx-user me-1'></i>
              {{ $report->reviewer->name }}
            </p>
          </div>
          @endif

          @if($report->approver)
          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Approved By</label>
            <p class="mb-0">
              <i class='bx bx-user-check me-1'></i>
              {{ $report->approver->name }}
            </p>
          </div>
          @endif

          @if($report->approved_at)
          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Approval Date</label>
            <p class="mb-0">
              <i class='bx bx-calendar-check me-1'></i>
              {{ $report->approved_at->format('d/m/Y H:i') }}
            </p>
          </div>
          @endif
        </div>

        @if($report->isApproved())
        <div class="alert alert-success mb-0">
          <i class='bx bx-check-circle me-2'></i>
          <strong>Approved:</strong> This report has been reviewed and approved by management.
        </div>
        @elseif($report->status === 'under-review')
        <div class="alert alert-info mb-0">
          <i class='bx bx-info-circle me-2'></i>
          <strong>Under Review:</strong> This report is pending management review and approval.
        </div>
        @endif
      </div>
    </div>
    @endif

    <!-- Record Timestamps -->
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Record Information</h6>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-4 mb-2">
            <label class="form-label text-muted mb-1">Created</label>
            <p class="mb-0"><small>{{ $report->created_at->format('d/m/Y H:i') }}</small></p>
          </div>

          <div class="col-md-4 mb-2">
            <label class="form-label text-muted mb-1">Last Updated</label>
            <p class="mb-0"><small>{{ $report->updated_at->format('d/m/Y H:i') }}</small></p>
          </div>

          <div class="col-md-4 mb-2">
            <label class="form-label text-muted mb-1">Branch</label>
            <p class="mb-0"><small>{{ $report->branch->name ?? 'Unknown' }}</small></p>
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
          @if(!$report->isPublished() && !$report->isApproved())
          <a href="{{ route('compliance-reports.edit', $report) }}" class="btn btn-primary btn-sm">
            <i class='bx bx-edit me-1'></i> Edit Report
          </a>
          @endif

          @if($report->file_path)
          <a href="{{ route('compliance-reports.download', $report) }}" class="btn btn-outline-secondary btn-sm">
            <i class='bx bx-download me-1'></i> Download File
          </a>
          @endif

          @if($report->isDraft())
          <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#submitModal">
            <i class='bx bx-send me-1'></i> Submit for Review
          </button>
          @endif

          @if($report->status === 'under-review' && auth()->user()->hasRole(['Manager', 'Admin']))
          <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal">
            <i class='bx bx-check me-1'></i> Approve Report
          </button>
          @endif

          @if($report->isApproved() && auth()->user()->hasRole(['Manager', 'Admin']))
          <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#publishModal">
            <i class='bx bx-globe me-1'></i> Publish Report
          </button>
          @endif

          <a href="{{ route('compliance-reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class='bx bx-arrow-back me-1'></i> Back to Reports
          </a>

          @if(!$report->isApproved() && !$report->isPublished())
          <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal">
            <i class='bx bx-trash me-1'></i> Delete Report
          </button>
          @endif
        </div>
      </div>
    </div>

    <!-- Report Statistics -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">Report Statistics</h6>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label text-muted mb-1">Created By</label>
          <p class="mb-0">{{ $report->creator->name ?? 'Unknown' }}</p>
        </div>

        <div class="mb-3">
          <label class="form-label text-muted mb-1">Report Period</label>
          <p class="mb-0">
            {{ $report->period_start->format('d/m/Y') }}<br>
            to {{ $report->period_end->format('d/m/Y') }}
          </p>
          <small class="text-muted">{{ $report->period_start->diffInDays($report->period_end) }} days</small>
        </div>

        @if($report->metrics && count($report->metrics) > 0)
        <div class="mb-3">
          <label class="form-label text-muted mb-1">Metrics Included</label>
          <p class="mb-0">{{ count($report->metrics) }} metrics</p>
        </div>
        @endif

        @if($report->requirements_included && count($report->requirements_included) > 0)
        <div class="mb-0">
          <label class="form-label text-muted mb-1">Requirements Covered</label>
          <p class="mb-0">{{ count($report->requirements_included) }} requirements</p>
        </div>
        @endif
      </div>
    </div>

    <!-- Status History -->
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Status History</h6>
      </div>
      <div class="card-body">
        <div class="timeline">
          <div class="timeline-item">
            <div class="timeline-event">
              <div class="timeline-event-icon bg-label-secondary">
                <i class='bx bx-file'></i>
              </div>
              <div>
                <p class="mb-1"><strong>Created</strong></p>
                <small class="text-muted">{{ $report->created_at->format('d/m/Y H:i') }}</small>
              </div>
            </div>
          </div>

          @if($report->status !== 'draft')
          <div class="timeline-item">
            <div class="timeline-event">
              <div class="timeline-event-icon bg-label-info">
                <i class='bx bx-send'></i>
              </div>
              <div>
                <p class="mb-1"><strong>Submitted for Review</strong></p>
                <small class="text-muted">{{ $report->updated_at->format('d/m/Y H:i') }}</small>
              </div>
            </div>
          </div>
          @endif

          @if($report->approved_at)
          <div class="timeline-item">
            <div class="timeline-event">
              <div class="timeline-event-icon bg-label-success">
                <i class='bx bx-check'></i>
              </div>
              <div>
                <p class="mb-1"><strong>Approved</strong></p>
                <small class="text-muted">{{ $report->approved_at->format('d/m/Y H:i') }}</small>
              </div>
            </div>
          </div>
          @endif

          @if($report->isPublished())
          <div class="timeline-item">
            <div class="timeline-event">
              <div class="timeline-event-icon bg-label-primary">
                <i class='bx bx-globe'></i>
              </div>
              <div>
                <p class="mb-1"><strong>Published</strong></p>
                <small class="text-muted">{{ $report->updated_at->format('d/m/Y H:i') }}</small>
              </div>
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Submit for Review Modal -->
<div class="modal fade" id="submitModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Submit Report for Review</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('compliance-reports.submit', $report) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="alert alert-info mb-3">
            <i class='bx bx-info-circle me-2'></i>
            <strong>Note:</strong> Once submitted, you will not be able to edit this report until it is returned to you.
          </div>

          <p class="mb-0">Are you ready to submit this report for manager review and approval?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-info">
            <i class='bx bx-send me-1'></i> Submit for Review
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Approve Report</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('compliance-reports.approve', $report) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="alert alert-success mb-3">
            <i class='bx bx-check-circle me-2'></i>
            <strong>Approval:</strong> This report will be marked as approved and ready for publication.
          </div>

          <p class="mb-0">Confirm that this report meets all compliance requirements and is ready for approval?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class='bx bx-check me-1'></i> Approve Report
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Publish Modal -->
<div class="modal fade" id="publishModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Publish Report</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('compliance-reports.publish', $report) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="alert alert-primary mb-3">
            <i class='bx bx-globe me-2'></i>
            <strong>Publication:</strong> This report will be made available to all authorized stakeholders.
          </div>

          <p class="mb-0">Confirm that this report is ready to be published and distributed?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class='bx bx-globe me-1'></i> Publish Report
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
        <h5 class="modal-title">Delete Compliance Report</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('compliance-reports.destroy', $report) }}" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <div class="alert alert-danger mb-3">
            <i class='bx bx-error-circle me-2'></i>
            <strong>Warning:</strong> This action cannot be undone!
          </div>

          <p class="mb-3">Are you sure you want to delete this compliance report?</p>

          <div class="alert alert-info mb-0">
            <strong>Report Details:</strong><br>
            <strong>Number:</strong> {{ $report->report_number }}<br>
            <strong>Title:</strong> {{ $report->title }}<br>
            <strong>Period:</strong> {{ $report->period_start->format('d/m/Y') }} - {{ $report->period_end->format('d/m/Y') }}
          </div>

          <p class="mt-3 mb-0">
            <small class="text-muted">
              <i class='bx bx-info-circle me-1'></i>
              The report will be soft-deleted and can be restored by an administrator if needed.
            </small>
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class='bx bx-trash me-1'></i> Delete Report
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

  // Initialize DataTables for requirements if present
  if ($('#requirementsTable').length) {
    $('#requirementsTable').DataTable({
      order: [[0, 'asc']],
      pageLength: 10,
      responsive: true,
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
    });
  }
});
</script>
@endsection
