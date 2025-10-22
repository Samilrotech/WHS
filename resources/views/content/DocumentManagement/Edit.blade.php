@extends('layouts/layoutMaster')

@section('title', 'Edit Document - ' . $document->title)

@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/tagify/tagify.css') }}">
@endsection

@section('content')
<div class="row">
  <!-- Main Form Column -->
  <div class="col-xl-9 col-lg-8 col-md-8">
    <!-- Read-Only Information Alert -->
    <div class="alert alert-info mb-3">
      <i class='bx bx-info-circle me-2'></i>
      <strong>Note:</strong> Document Code and File cannot be modified. To replace the file, create a new version.
    </div>

    <!-- Current Document Status -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Current Document Status</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-3 mb-3">
            <label class="form-label text-muted">Document Code</label>
            <h5 class="mb-0">{{ $document->document_number }}</h5>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label text-muted">Current Version</label>
            <h5 class="mb-0">v{{ $document->current_version }}</h5>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label text-muted">Review Status</label>
            <h5 class="mb-0">
              @php
                $statusClass = match($document->review_status) {
                  'approved' => 'success',
                  'pending' => 'warning',
                  'rejected' => 'danger',
                  default => 'secondary'
                };
              @endphp
              <span class="badge bg-{{ $statusClass }}">{{ ucfirst($document->review_status ?? 'None') }}</span>
            </h5>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label text-muted">Last Updated</label>
            <h5 class="mb-0"><small>{{ $document->updated_at->format('d/m/Y') }}</small></h5>
          </div>
        </div>

        @if($document->expiry_date)
        <div class="row mt-2">
          <div class="col-12">
            @php
              $daysUntilExpiry = now()->diffInDays($document->expiry_date, false);
              $expiryClass = $daysUntilExpiry < 0 ? 'danger' : ($daysUntilExpiry <= 30 ? 'warning' : 'success');
            @endphp
            <div class="alert alert-{{ $expiryClass }} mb-0">
              <i class='bx bx-calendar me-2'></i>
              @if($daysUntilExpiry < 0)
                <strong>EXPIRED:</strong> This document expired {{ abs($daysUntilExpiry) }} days ago on {{ $document->expiry_date->format('d/m/Y') }}
              @elseif($daysUntilExpiry <= 30)
                <strong>EXPIRING SOON:</strong> This document expires in {{ $daysUntilExpiry }} days on {{ $document->expiry_date->format('d/m/Y') }}
              @else
                <strong>Valid Until:</strong> {{ $document->expiry_date->format('d/m/Y') }} ({{ $daysUntilExpiry }} days remaining)
              @endif
            </div>
          </div>
        </div>
        @endif
      </div>
    </div>

    <!-- Edit Document Form -->
    <form action="{{ route('documents.update', $document) }}" method="POST" id="editDocumentForm">
      @csrf
      @method('PUT')

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
                     value="{{ old('title', $document->title) }}"
                     placeholder="e.g., Safety Data Sheet - Cleaning Products" required>
              @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="file_type" class="form-label">Document Type</label>
              <input type="text" id="file_type" class="form-control"
                     value="{{ ucfirst(str_replace('_', ' ', $document->file_type)) }}"
                     readonly disabled>
              <small class="text-muted">Document type cannot be changed after creation.</small>
            </div>

            <div class="col-md-6 mb-3">
              <label for="category_id" class="form-label">Category</label>
              <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                <option value="">Select category (optional)</option>
                @foreach($categories as $category)
                  <option value="{{ $category->id }}" {{ old('category_id', $document->category_id) == $category->id ? 'selected' : '' }}>
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
                        rows="4" placeholder="Detailed description of the document...">{{ old('description', $document->description) }}</textarea>
              @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <label for="tags" class="form-label">Tags</label>
              <input id="tags" name="tags" class="form-control"
                     value="{{ old('tags', is_array($document->tags) ? implode(',', $document->tags) : $document->tags) }}"
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
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="expiry_date" class="form-label">Expiry Date</label>
              <input type="date" id="expiry_date" name="expiry_date"
                     class="form-control @error('expiry_date') is-invalid @enderror"
                     value="{{ old('expiry_date', $document->expiry_date ? $document->expiry_date->format('Y-m-d') : '') }}">
              @error('expiry_date')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Automatic alerts will be sent 90, 60, and 30 days before expiry.</small>
            </div>

            <div class="col-md-6 mb-3">
              <label for="status" class="form-label">Document Status *</label>
              <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="active" {{ old('status', $document->status) == 'active' ? 'selected' : '' }}>Active</option>
                <option value="archived" {{ old('status', $document->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                <option value="deleted" {{ old('status', $document->status) == 'deleted' ? 'selected' : '' }}>Marked for Deletion</option>
              </select>
              @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- Access Control -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Access Control</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="visibility" class="form-label">Visibility Level *</label>
              <select id="visibility" name="visibility" class="form-select @error('visibility') is-invalid @enderror" required>
                <option value="public" {{ old('visibility', $document->visibility) == 'public' ? 'selected' : '' }}>Public (All users can view)</option>
                <option value="internal" {{ old('visibility', $document->visibility) == 'internal' ? 'selected' : '' }}>Internal (Branch members only)</option>
                <option value="confidential" {{ old('visibility', $document->visibility) == 'confidential' ? 'selected' : '' }}>Confidential (Managers and above)</option>
                <option value="restricted" {{ old('visibility', $document->visibility) == 'restricted' ? 'selected' : '' }}>Restricted (Specific users/roles only)</option>
              </select>
              @error('visibility')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" id="requires_review" name="requires_review" value="1"
                       {{ old('requires_review', $document->requires_review) ? 'checked' : '' }}>
                <label class="form-check-label" for="requires_review">
                  Requires Manager Review & Approval
                </label>
              </div>
            </div>
          </div>

          @if($document->visibility === 'restricted')
          <div class="alert alert-warning mb-0">
            <i class='bx bx-info-circle me-2'></i>
            <strong>Restricted Access:</strong> Only specified users or roles can view this document. Configure access restrictions in document settings.
          </div>
          @endif
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
                      rows="3" placeholder="Any additional information or instructions...">{{ old('notes', $document->notes) }}</textarea>
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
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
              <i class='bx bx-trash me-1'></i> Delete Document
            </button>
            <div>
              <a href="{{ route('documents.show', $document) }}" class="btn btn-outline-secondary me-2">
                <i class='bx bx-x me-1'></i> Cancel
              </a>
              <button type="submit" class="btn btn-primary">
                <i class='bx bx-save me-1'></i> Save Changes
              </button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Sidebar Column -->
  <div class="col-xl-3 col-lg-4 col-md-4">
    <!-- Document Summary -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">Document Summary</h6>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <small class="text-muted">Original Filename</small>
          <p class="mb-0">{{ $document->file_name }}</p>
        </div>
        <div class="mb-3">
          <small class="text-muted">File Size</small>
          <p class="mb-0">{{ $document->formatted_file_size ?? 'Unknown' }}</p>
        </div>
        <div class="mb-3">
          <small class="text-muted">File Type</small>
          <p class="mb-0">{{ strtoupper($document->mime_type ?? 'Unknown') }}</p>
        </div>
        <div class="mb-3">
          <small class="text-muted">Uploaded By</small>
          <p class="mb-0">{{ $document->uploader->name ?? 'Unknown' }}</p>
        </div>
        <div class="mb-3">
          <small class="text-muted">Upload Date</small>
          <p class="mb-0">{{ $document->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div class="mb-0">
          <small class="text-muted">Total Versions</small>
          <p class="mb-0">{{ $document->versions()->count() }}</p>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Quick Actions</h6>
      </div>
      <div class="card-body">
        <a href="{{ route('documents.show', $document) }}" class="btn btn-outline-primary w-100 mb-2">
          <i class='bx bx-show me-1'></i> View Document
        </a>
        <a href="{{ route('documents.download', $document) }}" class="btn btn-outline-info w-100 mb-2">
          <i class='bx bx-download me-1'></i> Download File
        </a>
        <button type="button" class="btn btn-outline-secondary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#versionModal">
          <i class='bx bx-history me-1'></i> Version History
        </button>
        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary w-100">
          <i class='bx bx-arrow-back me-1'></i> Back to Library
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Delete Document</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger mb-3">
          <i class='bx bx-error-circle me-2'></i>
          <strong>Warning:</strong> This action will soft-delete the document!
        </div>
        <p>Are you sure you want to delete <strong>{{ $document->title }}</strong>?</p>
        <p class="mb-0">This will:</p>
        <ul>
          <li>Mark the document as deleted (soft delete)</li>
          <li>Remove it from active document library</li>
          <li>Keep version history for 7-year retention policy</li>
          <li>Document can be restored by administrators</li>
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <form action="{{ route('documents.destroy', $document) }}" method="POST" class="d-inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">
            <i class='bx bx-trash me-1'></i> Yes, Delete Document
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Version History Modal -->
<div class="modal fade" id="versionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Version History</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @if($document->versions()->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Version</th>
                <th>Created By</th>
                <th>Date</th>
                <th>Change Notes</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($document->versions()->orderBy('version_number', 'desc')->get() as $version)
              <tr>
                <td>
                  <strong>v{{ $version->version_number }}</strong>
                  @if($version->is_current)
                    <span class="badge bg-success ms-1">Current</span>
                  @endif
                </td>
                <td>{{ $version->createdBy->name ?? 'Unknown' }}</td>
                <td>{{ $version->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $version->change_notes ?? 'No notes' }}</td>
                <td>
                  <button type="button" class="btn btn-sm btn-icon" title="Download this version">
                    <i class='bx bx-download'></i>
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
          No previous versions found. This is the original upload.
        </div>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
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

  // Form validation
  $('#editDocumentForm').on('submit', function(e) {
    // No specific validation needed for edit form
  });
});
</script>
@endsection
