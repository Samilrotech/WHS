@extends('layouts/layoutMaster')

@section('title', 'Edit Compliance Report - Compliance Reporting')

@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}">
@endsection

@section('content')
<div class="row">
  <!-- Main Form Column -->
  <div class="col-xl-9 col-lg-8 col-md-8">
    <!-- Read-Only Information Alert -->
    <div class="alert alert-info mb-4">
      <div class="d-flex align-items-center">
        <i class='bx bx-info-circle bx-md me-3'></i>
        <div>
          <strong>Note:</strong> Report number, type, and period cannot be changed once created. To modify these fields, create a new report.
        </div>
      </div>
    </div>

    <form action="{{ route('compliance-reports.update', $report) }}" method="POST" id="editReportForm" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <!-- Current Report Status -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Current Report Status</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-3 mb-3">
              <label class="form-label text-muted">Report Number</label>
              <h5 class="mb-0">{{ $report->report_number }}</h5>
            </div>

            <div class="col-md-3 mb-3">
              <label class="form-label text-muted">Report Type</label>
              <h5 class="mb-0">{{ $report->getReportTypeLabel() }}</h5>
            </div>

            <div class="col-md-3 mb-3">
              <label class="form-label text-muted">Period</label>
              <h5 class="mb-0">{{ ucfirst($report->period) }}</h5>
            </div>

            <div class="col-md-3 mb-3">
              <label class="form-label text-muted">Status</label>
              <h5 class="mb-0">
                <span class="badge bg-{{ $report->getStatusBadgeColor() }}">{{ ucfirst($report->status) }}</span>
              </h5>
            </div>
          </div>

          <div class="row mt-2">
            <div class="col-md-6 mb-3">
              <label class="form-label text-muted">Reporting Period</label>
              <h6 class="mb-0">
                {{ $report->period_start->format('d/m/Y') }} - {{ $report->period_end->format('d/m/Y') }}
              </h6>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label text-muted">Last Updated</label>
              <h6 class="mb-0">{{ $report->updated_at->format('d/m/Y H:i') }}</h6>
            </div>
          </div>

          @if($report->approved_at)
          <div class="row mt-2">
            <div class="col-12">
              <div class="alert alert-success mb-0">
                <i class='bx bx-check-circle me-2'></i>
                <strong>Approved:</strong> {{ $report->approved_at->format('d/m/Y H:i') }}
                by {{ $report->approver->name ?? 'Unknown' }}
              </div>
            </div>
          </div>
          @endif
        </div>
      </div>

      <!-- Report Information -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Report Information</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-12 mb-3">
              <label for="title" class="form-label">Report Title *</label>
              <input type="text" id="title" name="title"
                     class="form-control @error('title') is-invalid @enderror"
                     value="{{ old('title', $report->title) }}"
                     placeholder="e.g., Q1 2025 Safety Compliance Report" required>
              @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <label for="report_date" class="form-label">Report Date *</label>
              <input type="text" id="report_date" name="report_date"
                     class="form-control flatpickr-input @error('report_date') is-invalid @enderror"
                     value="{{ old('report_date', $report->report_date->format('Y-m-d')) }}"
                     placeholder="Select report date" required>
              @error('report_date')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Date this report was prepared.</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Report Content -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Report Content</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-12 mb-3">
              <label for="executive_summary" class="form-label">Executive Summary</label>
              <textarea id="executive_summary" name="executive_summary"
                        class="form-control @error('executive_summary') is-invalid @enderror"
                        rows="4"
                        placeholder="High-level overview for senior management...">{{ old('executive_summary', $report->executive_summary) }}</textarea>
              @error('executive_summary')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">A brief overview of the report's key points and overall compliance status.</small>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <label for="key_findings" class="form-label">Key Findings</label>
              <textarea id="key_findings" name="key_findings"
                        class="form-control @error('key_findings') is-invalid @enderror"
                        rows="5"
                        placeholder="Important compliance findings and observations...">{{ old('key_findings', $report->key_findings) }}</textarea>
              @error('key_findings')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Critical compliance issues, positive trends, or areas of concern identified during the period.</small>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <label for="recommendations" class="form-label">Recommendations</label>
              <textarea id="recommendations" name="recommendations"
                        class="form-control @error('recommendations') is-invalid @enderror"
                        rows="5"
                        placeholder="Actions recommended to improve compliance...">{{ old('recommendations', $report->recommendations) }}</textarea>
              @error('recommendations')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Suggested actions, improvements, or corrective measures to enhance compliance performance.</small>
            </div>
          </div>
        </div>
      </div>

      <!-- File Attachment -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Supporting Documents</h5>
        </div>
        <div class="card-body">
          @if($report->file_path)
          <div class="alert alert-info mb-3">
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center">
                <i class='bx bx-file bx-lg me-3'></i>
                <div>
                  <strong>{{ $report->file_name }}</strong><br>
                  <small class="text-muted">{{ $report->formatted_file_size }} • Uploaded {{ $report->created_at->format('d/m/Y') }}</small>
                </div>
              </div>
              <div>
                <a href="{{ route('compliance-reports.download', $report) }}" class="btn btn-sm btn-outline-primary">
                  <i class='bx bx-download me-1'></i> Download
                </a>
              </div>
            </div>
          </div>
          @endif

          <div class="mb-3">
            <label for="file" class="form-label">
              {{ $report->file_path ? 'Replace Report File' : 'Attach Report File' }}
            </label>
            <input type="file" id="file" name="file" class="form-control @error('file') is-invalid @enderror"
                   accept=".pdf,.doc,.docx,.xls,.xlsx">
            @error('file')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-muted">
              {{ $report->file_path ? 'Upload a new file to replace the existing one.' : 'Optional: Upload a pre-formatted report document (PDF, Word, Excel). Max size: 10MB' }}
            </small>
          </div>

          <div id="filePreview" style="display: none;" class="alert alert-warning mb-0">
            <div class="d-flex align-items-center">
              <i class='bx bx-file bx-lg me-3'></i>
              <div>
                <strong id="fileName"></strong><br>
                <small id="fileSize" class="text-muted"></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Report Status Update -->
      @if($report->isDraft() || $report->status === 'under-review')
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Update Report Status</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="status" class="form-label">Report Status *</label>
            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
              <option value="draft" {{ old('status', $report->status) === 'draft' ? 'selected' : '' }}>Draft (Continue editing)</option>
              <option value="under-review" {{ old('status', $report->status) === 'under-review' ? 'selected' : '' }}>Under Review (Submit for approval)</option>
            </select>
            @error('status')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="alert alert-info mb-0">
            <i class='bx bx-info-circle me-2'></i>
            <strong>Status Guide:</strong>
            <ul class="mb-0 mt-2">
              <li><strong>Draft:</strong> Keep working on the report</li>
              <li><strong>Under Review:</strong> Submit for manager approval</li>
            </ul>
          </div>
        </div>
      </div>
      @endif

      <!-- Form Actions -->
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('compliance-reports.show', $report) }}" class="btn btn-outline-secondary">
              <i class='bx bx-x me-1'></i> Cancel
            </a>
            <div>
              @if(!$report->isApproved() && !$report->isPublished())
              <button type="button" class="btn btn-outline-danger me-2" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class='bx bx-trash me-1'></i> Delete Report
              </button>
              @endif
              <button type="submit" class="btn btn-primary">
                <i class='bx bx-save me-1'></i> Update Report
              </button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Sidebar Column -->
  <div class="col-xl-3 col-lg-4 col-md-4">
    <!-- Report Summary -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">Report Summary</h6>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label text-muted mb-1">Created By</label>
          <p class="mb-0">{{ $report->creator->name ?? 'Unknown' }}</p>
        </div>

        <div class="mb-3">
          <label class="form-label text-muted mb-1">Created At</label>
          <p class="mb-0">{{ $report->created_at->format('d/m/Y H:i') }}</p>
        </div>

        @if($report->reviewer)
        <div class="mb-3">
          <label class="form-label text-muted mb-1">Reviewed By</label>
          <p class="mb-0">{{ $report->reviewer->name }}</p>
        </div>
        @endif

        @if($report->approver)
        <div class="mb-3">
          <label class="form-label text-muted mb-1">Approved By</label>
          <p class="mb-0">{{ $report->approver->name }}</p>
        </div>
        @endif

        <div class="mb-0">
          <label class="form-label text-muted mb-1">Branch</label>
          <p class="mb-0">{{ $report->branch->name ?? 'Unknown' }}</p>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Quick Actions</h6>
      </div>
      <div class="card-body">
        <div class="d-grid gap-2">
          <a href="{{ route('compliance-reports.show', $report) }}" class="btn btn-outline-primary btn-sm">
            <i class='bx bx-show me-1'></i> View Report
          </a>

          @if($report->file_path)
          <a href="{{ route('compliance-reports.download', $report) }}" class="btn btn-outline-secondary btn-sm">
            <i class='bx bx-download me-1'></i> Download File
          </a>
          @endif

          @if($report->isDraft())
          <button type="button" class="btn btn-outline-info btn-sm" onclick="submitForReview()">
            <i class='bx bx-send me-1'></i> Submit for Review
          </button>
          @endif
        </div>
      </div>
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
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script>
$(document).ready(function() {
  'use strict';

  // Initialize Flatpickr date picker
  if ($('#report_date').length) {
    $('#report_date').flatpickr({
      dateFormat: 'Y-m-d',
      altInput: true,
      altFormat: 'd/m/Y',
      allowInput: false
    });
  }

  // File input change - Show preview
  $('#file').on('change', function(e) {
    const file = e.target.files[0];

    if (file) {
      $('#fileName').text(file.name);
      $('#fileSize').text((file.size / 1024 / 1024).toFixed(2) + ' MB');
      $('#filePreview').slideDown();
    } else {
      $('#filePreview').slideUp();
    }
  });

  // Form validation
  $('#editReportForm').on('submit', function(e) {
    // Check file size if uploaded (10MB = 10 * 1024 * 1024 bytes)
    const file = $('#file')[0].files[0];
    if (file && file.size > 10 * 1024 * 1024) {
      e.preventDefault();
      alert('File size exceeds 10MB limit. Please choose a smaller file.');
      return false;
    }
  });
});

// Submit for review function
function submitForReview() {
  if (confirm('Submit this report for manager review? You will not be able to edit it after submission.')) {
    $('#status').val('under-review');
    $('#editReportForm').submit();
  }
}
</script>
@endsection
