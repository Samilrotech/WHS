@extends('layouts/layoutMaster')

@section('title', 'Upload Document - Document Management')

@section('page-style')
<parameter name="link" rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/tagify/tagify.css') }}">
@endsection

@section('content')
<div class="row">
  <!-- Main Form Column -->
  <div class="col-xl-9 col-lg-8 col-md-8">
    <form action="{{ route('documents.store') }}" method="POST" id="uploadDocumentForm" enctype="multipart/form-data">
      @csrf

      <!-- File Upload Section -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Document File</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="file" class="form-label">Select Document File *</label>
            <input type="file" id="file" name="file" class="form-control @error('file') is-invalid @enderror"
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                   required>
            @error('file')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-muted">Accepted formats: PDF, Word, Excel, Images (JPG, PNG). Max size: 10MB</small>
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

      <!-- Document Information -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Document Information</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-12 mb-3">
              <label for="title" class="form-label">Document Title *</label>
              <input type="text" id="title" name="title"
                     class="form-control @error('title') is-invalid @enderror"
                     value="{{ old('title') }}"
                     placeholder="e.g., Safety Data Sheet - Cleaning Products" required>
              @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="document_type" class="form-label">Document Type *</label>
              <select id="document_type" name="document_type" class="form-select @error('document_type') is-invalid @enderror" required>
                <option value="">Select type</option>
                <option value="swms" {{ old('document_type') == 'swms' ? 'selected' : '' }}>SWMS (Safe Work Method Statement)</option>
                <option value="policy" {{ old('document_type') == 'policy' ? 'selected' : '' }}>Policy</option>
                <option value="procedure" {{ old('document_type') == 'procedure' ? 'selected' : '' }}>Procedure/SOP</option>
                <option value="safety_data_sheet" {{ old('document_type') == 'safety_data_sheet' ? 'selected' : '' }}>Safety Data Sheet (SDS)</option>
                <option value="training_certificate" {{ old('document_type') == 'training_certificate' ? 'selected' : '' }}>Training Certificate</option>
                <option value="vehicle_registration" {{ old('document_type') == 'vehicle_registration' ? 'selected' : '' }}>Vehicle Registration</option>
                <option value="vehicle_insurance" {{ old('document_type') == 'vehicle_insurance' ? 'selected' : '' }}>Vehicle Insurance</option>
                <option value="asset_warranty" {{ old('document_type') == 'asset_warranty' ? 'selected' : '' }}>Asset Warranty</option>
                <option value="contractor_insurance" {{ old('document_type') == 'contractor_insurance' ? 'selected' : '' }}>Contractor Insurance</option>
                <option value="contractor_license" {{ old('document_type') == 'contractor_license' ? 'selected' : '' }}>Contractor License</option>
                <option value="incident_evidence" {{ old('document_type') == 'incident_evidence' ? 'selected' : '' }}>Incident Evidence</option>
                <option value="calibration_certificate" {{ old('document_type') == 'calibration_certificate' ? 'selected' : '' }}>Calibration Certificate</option>
                <option value="other" {{ old('document_type') == 'other' ? 'selected' : '' }}>Other</option>
              </select>
              @error('document_type')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="category_id" class="form-label">Category</label>
              <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                <option value="">Select category (optional)</option>
                @foreach($categories as $category)
                  <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                  </option>
                @endforeach
              </select>
              @error('category_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <label for="description" class="form-label">Description</label>
              <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror"
                        rows="4" placeholder="Detailed description of the document...">{{ old('description') }}</textarea>
              @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <label for="tags" class="form-label">Tags</label>
              <input id="tags" name="tags" class="form-control"
                     value="{{ old('tags') }}"
                     placeholder="e.g., forklift, safety, annual">
              <small class="text-muted">Press Enter to add tags. Used for quick searching and filtering.</small>
              @error('tags')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- Expiry & Dates -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Expiry Information</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="has_expiry" name="has_expiry" value="1"
                     {{ old('has_expiry') ? 'checked' : '' }}>
              <label class="form-check-label" for="has_expiry">
                This document has an expiry date (e.g., certificates, insurance policies)
              </label>
            </div>
          </div>

          <div id="expiryFields" style="display: {{ old('has_expiry') ? 'block' : 'none' }};">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="issue_date" class="form-label">Issue Date</label>
                <input type="date" id="issue_date" name="issue_date"
                       class="form-control @error('issue_date') is-invalid @enderror"
                       value="{{ old('issue_date') }}">
                @error('issue_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6 mb-3">
                <label for="expiry_date" class="form-label">Expiry Date *</label>
                <input type="date" id="expiry_date" name="expiry_date"
                       class="form-control @error('expiry_date') is-invalid @enderror"
                       value="{{ old('expiry_date') }}">
                @error('expiry_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Automatic alerts will be sent 90, 60, and 30 days before expiry.</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Access Control & Approval -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Access Control & Review</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="visibility" class="form-label">Visibility Level *</label>
              <select id="visibility" name="visibility" class="form-select @error('visibility') is-invalid @enderror" required>
                <option value="public" {{ old('visibility') == 'public' ? 'selected' : '' }}>Public (All users can view)</option>
                <option value="internal" {{ old('visibility', 'internal') == 'internal' ? 'selected' : '' }}>Internal (Branch members only)</option>
                <option value="confidential" {{ old('visibility') == 'confidential' ? 'selected' : '' }}>Confidential (Managers and above)</option>
                <option value="restricted" {{ old('visibility') == 'restricted' ? 'selected' : '' }}>Restricted (Specific users/roles only)</option>
              </select>
              @error('visibility')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" id="requires_review" name="requires_review" value="1"
                       {{ old('requires_review') ? 'checked' : '' }}>
                <label class="form-check-label" for="requires_review">
                  Requires Manager Review & Approval
                </label>
              </div>
            </div>
          </div>

          <div id="restrictedFields" style="display: {{ old('visibility') == 'restricted' ? 'block' : 'none' }};">
            <div class="alert alert-warning mb-0">
              <i class='bx bx-info-circle me-2'></i>
              <strong>Restricted Access:</strong> Only specified users or roles will be able to view this document. Configure access restrictions after upload.
            </div>
          </div>
        </div>
      </div>

      <!-- Additional Notes -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Additional Notes</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="notes" class="form-label">Internal Notes</label>
            <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror"
                      rows="3" placeholder="Any additional information or instructions for document management...">{{ old('notes') }}</textarea>
            @error('notes')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
              <i class='bx bx-x me-1'></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
              <i class='bx bx-upload me-1'></i> Upload Document
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Sidebar Column -->
  <div class="col-xl-3 col-lg-4 col-md-4">
    <!-- Upload Guidelines -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">Upload Guidelines</h6>
      </div>
      <div class="card-body">
        <p class="mb-2"><i class='bx bx-check-circle text-success me-2'></i> <strong>Accepted Formats:</strong></p>
        <ul class="mb-3">
          <li>PDF documents</li>
          <li>Word (DOC, DOCX)</li>
          <li>Excel (XLS, XLSX)</li>
          <li>Images (JPG, PNG)</li>
        </ul>

        <p class="mb-2"><i class='bx bx-error-circle text-warning me-2'></i> <strong>Important Notes:</strong></p>
        <ul class="mb-0">
          <li>Maximum file size: 10MB</li>
          <li>Use descriptive titles</li>
          <li>Add tags for easy searching</li>
          <li>Set expiry dates for certificates</li>
          <li>Documents are stored for 7 years (legal requirement)</li>
        </ul>
      </div>
    </div>

    <!-- Document Types Guide -->
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Document Types</h6>
      </div>
      <div class="card-body">
        <p class="mb-2"><strong>SWMS:</strong> Safe Work Method Statements for high-risk tasks</p>
        <p class="mb-2"><strong>SDS:</strong> Safety Data Sheets for chemicals and hazardous materials</p>
        <p class="mb-2"><strong>Certificates:</strong> Training, licenses, calibration documents</p>
        <p class="mb-2"><strong>Insurance:</strong> Vehicle, contractor, liability policies</p>
        <p class="mb-0"><strong>Evidence:</strong> Photos, witness statements for incident investigations</p>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/tagify/tagify.js') }}"></script>
<script>
$(document).ready(function() {
  'use strict';

  // Initialize Select2
  if ($('#category_id').length) {
    $('#category_id').select2({
      placeholder: 'Select category (optional)',
      allowClear: true
    });
  }

  // Initialize Tagify for tags input
  if ($('#tags').length) {
    new Tagify(document.querySelector('#tags'), {
      delimiters: ',',
      maxTags: 10,
      placeholder: 'Type and press Enter'
    });
  }

  // File input change - Show preview
  $('#file').on('change', function(e) {
    const file = e.target.files[0];

    if (file) {
      $('#fileName').text(file.name);
      $('#fileSize').text((file.size / 1024 / 1024).toFixed(2) + ' MB');
      $('#filePreview').slideDown();

      // Auto-fill title if empty
      if (!$('#title').val()) {
        const titleSuggestion = file.name.replace(/\.[^/.]+$/, "").replace(/[-_]/g, ' ');
        $('#title').val(titleSuggestion);
      }
    } else {
      $('#filePreview').slideUp();
    }
  });

  // Has expiry checkbox toggle
  $('#has_expiry').on('change', function() {
    if ($(this).is(':checked')) {
      $('#expiryFields').slideDown();
      $('#expiry_date').prop('required', true);
    } else {
      $('#expiryFields').slideUp();
      $('#expiry_date').prop('required', false);
      $('#issue_date').val('');
      $('#expiry_date').val('');
    }
  });

  // Visibility change - Show restricted fields notice
  $('#visibility').on('change', function() {
    if ($(this).val() === 'restricted') {
      $('#restrictedFields').slideDown();
    } else {
      $('#restrictedFields').slideUp();
    }
  });

  // Document type suggestions
  $('#document_type').on('change', function() {
    const type = $(this).val();

    // Auto-check has_expiry for certain document types
    if (['vehicle_insurance', 'contractor_insurance', 'training_certificate',
         'vehicle_registration', 'contractor_license', 'calibration_certificate'].includes(type)) {
      if (!$('#has_expiry').is(':checked')) {
        $('#has_expiry').prop('checked', true).trigger('change');
      }
    }

    // Auto-check requires_review for policies and procedures
    if (['policy', 'procedure', 'swms'].includes(type)) {
      $('#requires_review').prop('checked', true);
    }
  });

  // Form validation
  $('#uploadDocumentForm').on('submit', function(e) {
    const file = $('#file')[0].files[0];

    if (!file) {
      e.preventDefault();
      alert('Please select a file to upload.');
      $('#file').focus();
      return false;
    }

    // Check file size (10MB = 10 * 1024 * 1024 bytes)
    if (file.size > 10 * 1024 * 1024) {
      e.preventDefault();
      alert('File size exceeds 10MB limit. Please choose a smaller file.');
      return false;
    }

    // Check expiry fields
    if ($('#has_expiry').is(':checked') && !$('#expiry_date').val()) {
      e.preventDefault();
      alert('Please specify an expiry date or uncheck "Has Expiry Date".');
      $('#expiry_date').focus();
      return false;
    }
  });
});
</script>
@endsection
