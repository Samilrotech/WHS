@extends('layouts/layoutMaster')

@section('title', 'Create Compliance Report - Compliance Reporting')

@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}">
@endsection

@section('content')
<div class="row">
  <!-- Main Form Column -->
  <div class="col-xl-9 col-lg-8 col-md-8">
    <form action="{{ route('compliance-reports.store') }}" method="POST" id="createReportForm" enctype="multipart/form-data">
      @csrf

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
                     value="{{ old('title') }}"
                     placeholder="e.g., Q1 2025 Safety Compliance Report" required>
              @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="report_type" class="form-label">Report Type *</label>
              <select id="report_type" name="report_type" class="form-select @error('report_type') is-invalid @enderror" required>
                <option value="">Select type</option>
                <option value="periodic" {{ old('report_type') == 'periodic' ? 'selected' : '' }}>Periodic Report (Monthly/Quarterly/Annual)</option>
                <option value="audit" {{ old('report_type') == 'audit' ? 'selected' : '' }}>Audit Report (Internal/External)</option>
                <option value="incident-based" {{ old('report_type') == 'incident-based' ? 'selected' : '' }}>Incident-Based Report</option>
                <option value="regulatory" {{ old('report_type') == 'regulatory' ? 'selected' : '' }}>Regulatory Report (Government Submission)</option>
                <option value="custom" {{ old('report_type') == 'custom' ? 'selected' : '' }}>Custom Report</option>
              </select>
              @error('report_type')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="period" class="form-label">Reporting Period *</label>
              <select id="period" name="period" class="form-select @error('period') is-invalid @enderror" required>
                <option value="">Select period</option>
                <option value="weekly" {{ old('period') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                <option value="monthly" {{ old('period') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                <option value="quarterly" {{ old('period') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                <option value="annual" {{ old('period') == 'annual' ? 'selected' : '' }}>Annual</option>
                <option value="custom" {{ old('period') == 'custom' ? 'selected' : '' }}>Custom Period</option>
              </select>
              @error('period')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="period_start" class="form-label">Period Start Date *</label>
              <input type="text" id="period_start" name="period_start"
                     class="form-control flatpickr-input @error('period_start') is-invalid @enderror"
                     value="{{ old('period_start') }}"
                     placeholder="Select start date" required>
              @error('period_start')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-4 mb-3">
              <label for="period_end" class="form-label">Period End Date *</label>
              <input type="text" id="period_end" name="period_end"
                     class="form-control flatpickr-input @error('period_end') is-invalid @enderror"
                     value="{{ old('period_end') }}"
                     placeholder="Select end date" required>
              @error('period_end')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-4 mb-3">
              <label for="report_date" class="form-label">Report Date *</label>
              <input type="text" id="report_date" name="report_date"
                     class="form-control flatpickr-input @error('report_date') is-invalid @enderror"
                     value="{{ old('report_date', now()->format('Y-m-d')) }}"
                     placeholder="Select report date" required>
              @error('report_date')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Date this report was prepared.</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Auto-Generate Metrics Option -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Data Collection Method</h5>
        </div>
        <div class="card-body">
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="auto_generate_metrics" name="auto_generate_metrics" value="1"
                   {{ old('auto_generate_metrics') ? 'checked' : '' }}>
            <label class="form-check-label" for="auto_generate_metrics">
              <strong>Auto-Generate Metrics from System Data</strong>
            </label>
          </div>

          <div class="alert alert-info mb-0">
            <i class='bx bx-info-circle me-2'></i>
            <strong>Recommended:</strong> Enable auto-generation to pull real-time compliance data from:
            <ul class="mb-0 mt-2">
              <li>Incident reports and safety statistics</li>
              <li>Training completion rates and certifications</li>
              <li>Inspection results and equipment compliance</li>
              <li>Document expiry tracking and renewals</li>
              <li>Risk assessment scores and trends</li>
            </ul>
          </div>

          <div id="manualMetricsNote" style="display: {{ old('auto_generate_metrics') ? 'none' : 'block' }};" class="alert alert-warning mb-0 mt-3">
            <i class='bx bx-error me-2'></i>
            <strong>Manual Entry Mode:</strong> You will need to enter metrics and data manually after creating the report.
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
                        placeholder="High-level overview for senior management...">{{ old('executive_summary') }}</textarea>
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
                        placeholder="Important compliance findings and observations...">{{ old('key_findings') }}</textarea>
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
                        placeholder="Actions recommended to improve compliance...">{{ old('recommendations') }}</textarea>
              @error('recommendations')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Suggested actions, improvements, or corrective measures to enhance compliance performance.</small>
            </div>
          </div>
        </div>
      </div>

      <!-- File Attachment (Optional) -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Supporting Documents (Optional)</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="file" class="form-label">Attach Report File</label>
            <input type="file" id="file" name="file" class="form-control @error('file') is-invalid @enderror"
                   accept=".pdf,.doc,.docx,.xls,.xlsx">
            @error('file')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-muted">Optional: Upload a pre-formatted report document (PDF, Word, Excel). Max size: 10MB</small>
          </div>

          <div id="filePreview" style="display: none;" class="alert alert-info mb-0">
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

      <!-- Form Actions -->
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('compliance-reports.index') }}" class="btn btn-outline-secondary">
              <i class='bx bx-x me-1'></i> Cancel
            </a>
            <div>
              <button type="submit" name="status" value="draft" class="btn btn-outline-primary me-2">
                <i class='bx bx-save me-1'></i> Save as Draft
              </button>
              <button type="submit" name="status" value="under-review" class="btn btn-primary">
                <i class='bx bx-check me-1'></i> Create & Submit for Review
              </button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Sidebar Column -->
  <div class="col-xl-3 col-lg-4 col-md-4">
    <!-- Quick Guide -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">Report Guide</h6>
      </div>
      <div class="card-body">
        <p class="mb-2"><i class='bx bx-check-circle text-success me-2'></i> <strong>Report Types:</strong></p>
        <ul class="mb-3">
          <li><strong>Periodic:</strong> Regular scheduled reports</li>
          <li><strong>Audit:</strong> Internal or external audits</li>
          <li><strong>Incident-Based:</strong> Following specific incidents</li>
          <li><strong>Regulatory:</strong> Government submissions</li>
        </ul>

        <p class="mb-2"><i class='bx bx-info-circle text-info me-2'></i> <strong>Best Practices:</strong></p>
        <ul class="mb-0">
          <li>Use auto-generate for accurate metrics</li>
          <li>Include executive summary for leadership</li>
          <li>Document all findings and recommendations</li>
          <li>Submit for review before finalizing</li>
        </ul>
      </div>
    </div>

    <!-- Report Workflow -->
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Report Workflow</h6>
      </div>
      <div class="card-body">
        <div class="d-flex align-items-start mb-3">
          <span class="badge bg-label-secondary rounded-circle p-2 me-2">1</span>
          <div>
            <strong>Draft</strong>
            <p class="mb-0 text-muted"><small>Initial creation and data entry</small></p>
          </div>
        </div>

        <div class="d-flex align-items-start mb-3">
          <span class="badge bg-label-info rounded-circle p-2 me-2">2</span>
          <div>
            <strong>Under Review</strong>
            <p class="mb-0 text-muted"><small>Manager review and validation</small></p>
          </div>
        </div>

        <div class="d-flex align-items-start mb-3">
          <span class="badge bg-label-success rounded-circle p-2 me-2">3</span>
          <div>
            <strong>Approved</strong>
            <p class="mb-0 text-muted"><small>Approved for publication</small></p>
          </div>
        </div>

        <div class="d-flex align-items-start">
          <span class="badge bg-label-primary rounded-circle p-2 me-2">4</span>
          <div>
            <strong>Published</strong>
            <p class="mb-0 text-muted"><small>Available to stakeholders</small></p>
          </div>
        </div>
      </div>
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

  // Initialize Flatpickr date pickers
  if ($('#period_start').length) {
    $('#period_start').flatpickr({
      dateFormat: 'Y-m-d',
      altInput: true,
      altFormat: 'd/m/Y',
      allowInput: false
    });
  }

  if ($('#period_end').length) {
    $('#period_end').flatpickr({
      dateFormat: 'Y-m-d',
      altInput: true,
      altFormat: 'd/m/Y',
      allowInput: false
    });
  }

  if ($('#report_date').length) {
    $('#report_date').flatpickr({
      dateFormat: 'Y-m-d',
      altInput: true,
      altFormat: 'd/m/Y',
      allowInput: false,
      defaultDate: 'today'
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

  // Auto-generate metrics checkbox toggle
  $('#auto_generate_metrics').on('change', function() {
    if ($(this).is(':checked')) {
      $('#manualMetricsNote').slideUp();
    } else {
      $('#manualMetricsNote').slideDown();
    }
  });

  // Report type suggestions for period
  $('#report_type').on('change', function() {
    const type = $(this).val();

    // Suggest appropriate periods based on report type
    if (type === 'periodic') {
      // Suggest monthly or quarterly for periodic reports
      if (!$('#period').val()) {
        $('#period').val('monthly').trigger('change');
      }
    } else if (type === 'audit') {
      // Suggest annual for audit reports
      if (!$('#period').val()) {
        $('#period').val('annual').trigger('change');
      }
    } else if (type === 'regulatory') {
      // Suggest quarterly or annual for regulatory reports
      if (!$('#period').val()) {
        $('#period').val('quarterly').trigger('change');
      }
    }
  });

  // Auto-fill dates based on period selection
  $('#period').on('change', function() {
    const period = $(this).val();
    const today = new Date();
    let startDate, endDate;

    if (period === 'weekly') {
      // Last 7 days
      startDate = new Date(today);
      startDate.setDate(today.getDate() - 7);
      endDate = today;
    } else if (period === 'monthly') {
      // Last month
      startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
      endDate = new Date(today.getFullYear(), today.getMonth(), 0);
    } else if (period === 'quarterly') {
      // Last quarter
      const quarter = Math.floor(today.getMonth() / 3);
      startDate = new Date(today.getFullYear(), (quarter - 1) * 3, 1);
      endDate = new Date(today.getFullYear(), quarter * 3, 0);
    } else if (period === 'annual') {
      // Last year
      startDate = new Date(today.getFullYear() - 1, 0, 1);
      endDate = new Date(today.getFullYear() - 1, 11, 31);
    }

    if (startDate && endDate) {
      $('#period_start').val(formatDate(startDate));
      $('#period_end').val(formatDate(endDate));

      // Trigger flatpickr update
      $('#period_start')[0]._flatpickr.setDate(startDate);
      $('#period_end')[0]._flatpickr.setDate(endDate);
    }
  });

  // Helper function to format date as YYYY-MM-DD
  function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  // Form validation
  $('#createReportForm').on('submit', function(e) {
    const periodStart = new Date($('#period_start').val());
    const periodEnd = new Date($('#period_end').val());

    // Validate period dates
    if (periodStart >= periodEnd) {
      e.preventDefault();
      alert('Period end date must be after the start date.');
      $('#period_end').focus();
      return false;
    }

    // Check file size if uploaded (10MB = 10 * 1024 * 1024 bytes)
    const file = $('#file')[0].files[0];
    if (file && file.size > 10 * 1024 * 1024) {
      e.preventDefault();
      alert('File size exceeds 10MB limit. Please choose a smaller file.');
      return false;
    }
  });
});
</script>
@endsection
