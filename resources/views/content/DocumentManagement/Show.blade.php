@extends('layouts/layoutMaster')

@section('title', $document->title . ' - Document Management')

@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
@endsection

@section('content')
<div class="row">
  <!-- Main Content Column -->
  <div class="col-xl-9 col-lg-8 col-md-8">
    <!-- Document Header Card -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $document->title }}</h5>
        <div>
          <a href="{{ route('documents.edit', $document) }}" class="btn btn-sm btn-primary">
            <i class='bx bx-edit me-1'></i> Edit
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- Document Code & Type -->
          <div class="col-md-4 mb-3">
            <label class="form-label text-muted">Document Code</label>
            <h5 class="mb-0">{{ $document->document_number }}</h5>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label text-muted">Document Type</label>
            <h5 class="mb-0">
              @php
                $typeIcons = [
                  'swms' => 'bx-shield',
                  'policy' => 'bx-file',
                  'procedure' => 'bx-list-ul',
                  'safety_data_sheet' => 'bx-test-tube',
                  'training_certificate' => 'bx-certification',
                  'vehicle_insurance' => 'bx-car',
                  'contractor_insurance' => 'bx-briefcase',
                  'incident_evidence' => 'bx-camera',
                  'calibration_certificate' => 'bx-check-circle',
                ];
                $icon = $typeIcons[$document->file_type] ?? 'bx-file';
              @endphp
              <i class='bx {{ $icon }} me-1'></i>
              {{ ucfirst(str_replace('_', ' ', $document->file_type)) }}
            </h5>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label text-muted">Status</label>
            <h5 class="mb-0">
              @php
                $statusClass = match($document->status) {
                  'active' => 'success',
                  'archived' => 'secondary',
                  'deleted' => 'danger',
                  default => 'secondary'
                };
              @endphp
              <span class="badge bg-{{ $statusClass }} fs-6">{{ ucfirst($document->status) }}</span>
            </h5>
          </div>
        </div>

        <hr class="my-3">

        <div class="row">
          <div class="col-md-12 mb-3">
            <label class="form-label text-muted">Description</label>
            <p class="mb-0">{{ $document->description ?? 'No description provided' }}</p>
          </div>
        </div>

        @if($document->tags && count($document->tags) > 0)
        <div class="row">
          <div class="col-md-12 mb-3">
            <label class="form-label text-muted">Tags</label>
            <div>
              @foreach($document->tags as $tag)
                <span class="badge bg-label-primary me-1">{{ $tag }}</span>
              @endforeach
            </div>
          </div>
        </div>
        @endif

        <div class="row">
          <div class="col-md-3 mb-3">
            <label class="form-label text-muted">Uploaded By</label>
            <p class="mb-0">{{ $document->uploader->name ?? 'Unknown' }}</p>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label text-muted">Upload Date</label>
            <p class="mb-0">{{ $document->created_at->format('d/m/Y H:i') }}</p>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label text-muted">Current Version</label>
            <p class="mb-0">v{{ $document->current_version }}</p>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label text-muted">File Size</label>
            <p class="mb-0">{{ $document->formatted_file_size ?? 'Unknown' }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Expiry Information (if applicable) -->
    @if($document->expiry_date)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Expiry Information</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Expiry Date</label>
            <h5 class="mb-0">{{ $document->expiry_date->format('d/m/Y') }}</h5>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Status</label>
            <h5 class="mb-0">
              @php
                $daysUntilExpiry = now()->diffInDays($document->expiry_date, false);
                if ($daysUntilExpiry < 0) {
                  $expiryClass = 'danger';
                  $expiryText = 'EXPIRED ' . abs($daysUntilExpiry) . ' days ago';
                } elseif ($daysUntilExpiry <= 30) {
                  $expiryClass = 'warning';
                  $expiryText = 'Expires in ' . $daysUntilExpiry . ' days';
                } else {
                  $expiryClass = 'success';
                  $expiryText = 'Valid for ' . $daysUntilExpiry . ' days';
                }
              @endphp
              <span class="badge bg-{{ $expiryClass }} fs-6">{{ $expiryText }}</span>
            </h5>
          </div>
        </div>

        @if($daysUntilExpiry < 0 || $daysUntilExpiry <= 30)
        <div class="row">
          <div class="col-12">
            <div class="alert alert-{{ $expiryClass }} mb-0">
              <i class='bx bx-info-circle me-2'></i>
              @if($daysUntilExpiry < 0)
                <strong>Action Required:</strong> This document has expired and may need renewal or replacement.
              @else
                <strong>Expiring Soon:</strong> This document will expire in {{ $daysUntilExpiry }} days. Please prepare for renewal.
              @endif
            </div>
          </div>
        </div>
        @endif
      </div>
    </div>
    @endif

    <!-- Access Control Information -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Access Control</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Visibility Level</label>
            <h5 class="mb-0">
              @php
                $visibilityIcons = [
                  'public' => ['icon' => 'bx-globe', 'class' => 'info'],
                  'internal' => ['icon' => 'bx-building', 'class' => 'primary'],
                  'confidential' => ['icon' => 'bx-lock', 'class' => 'warning'],
                  'restricted' => ['icon' => 'bx-lock-alt', 'class' => 'danger'],
                ];
                $visInfo = $visibilityIcons[$document->visibility] ?? ['icon' => 'bx-file', 'class' => 'secondary'];
              @endphp
              <span class="badge bg-{{ $visInfo['class'] }} fs-6">
                <i class='bx {{ $visInfo['icon'] }} me-1'></i>
                {{ ucfirst($document->visibility) }}
              </span>
            </h5>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Review Status</label>
            <h5 class="mb-0">
              @if($document->requires_review)
                @php
                  $reviewStatusClass = match($document->review_status) {
                    'approved' => 'success',
                    'pending' => 'warning',
                    'rejected' => 'danger',
                    default => 'secondary'
                  };
                @endphp
                <span class="badge bg-{{ $reviewStatusClass }} fs-6">
                  {{ $document->review_status ? ucfirst($document->review_status) : 'Pending Review' }}
                </span>
              @else
                <span class="badge bg-secondary fs-6">No Review Required</span>
              @endif
            </h5>
          </div>
        </div>

        @if($document->reviewed_by && $document->reviewed_at)
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Reviewed By</label>
            <p class="mb-0">{{ $document->reviewer->name ?? 'Unknown' }}</p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Review Date</label>
            <p class="mb-0">{{ $document->reviewed_at->format('d/m/Y H:i') }}</p>
          </div>
        </div>

        @if($document->review_notes)
        <div class="row">
          <div class="col-12">
            <label class="form-label text-muted">Review Notes</label>
            <div class="alert alert-info mb-0">
              <i class='bx bx-note me-2'></i>
              {{ $document->review_notes }}
            </div>
          </div>
        </div>
        @endif
        @endif
      </div>
    </div>

    <!-- Additional Notes (if any) -->
    @if($document->notes)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Internal Notes</h5>
      </div>
      <div class="card-body">
        <p class="mb-0">{{ $document->notes }}</p>
      </div>
    </div>
    @endif

    <!-- Version History -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Version History</h5>
      </div>
      <div class="card-body">
        @if($document->versions()->count() > 0)
        <div class="table-responsive">
          <table id="versionsTable" class="table table-hover">
            <thead>
              <tr>
                <th>Version</th>
                <th>Created By</th>
                <th>Date</th>
                <th>Change Type</th>
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
                <td>
                  @php
                    $changeTypeClass = match($version->change_type) {
                      'major' => 'danger',
                      'minor' => 'warning',
                      'correction' => 'info',
                      default => 'secondary'
                    };
                  @endphp
                  <span class="badge bg-{{ $changeTypeClass }}">{{ ucfirst($version->change_type ?? 'Minor') }}</span>
                </td>
                <td>{{ $version->change_notes ?? 'No notes provided' }}</td>
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
    </div>

    <!-- Access Logs -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Access Audit Trail</h5>
      </div>
      <div class="card-body">
        @if($document->accessLogs()->count() > 0)
        <div class="table-responsive">
          <table id="accessLogsTable" class="table table-hover">
            <thead>
              <tr>
                <th>Action</th>
                <th>User</th>
                <th>Date/Time</th>
                <th>IP Address</th>
              </tr>
            </thead>
            <tbody>
              @foreach($document->accessLogs()->take(20)->get() as $log)
              <tr>
                <td>
                  @php
                    $actionIcons = [
                      'view' => ['icon' => 'bx-show', 'class' => 'info'],
                      'downloaded' => ['icon' => 'bx-download', 'class' => 'primary'],
                      'uploaded' => ['icon' => 'bx-upload', 'class' => 'success'],
                      'updated' => ['icon' => 'bx-edit', 'class' => 'warning'],
                      'deleted' => ['icon' => 'bx-trash', 'class' => 'danger'],
                      'shared' => ['icon' => 'bx-share-alt', 'class' => 'info'],
                    ];
                    $actionInfo = $actionIcons[$log->action] ?? ['icon' => 'bx-file', 'class' => 'secondary'];
                  @endphp
                  <span class="badge bg-{{ $actionInfo['class'] }}">
                    <i class='bx {{ $actionInfo['icon'] }} me-1'></i>
                    {{ ucfirst($log->action) }}
                  </span>
                </td>
                <td>{{ $log->user->name ?? 'Unknown' }}</td>
                <td>{{ $log->accessed_at->format('d/m/Y H:i:s') }}</td>
                <td><small class="text-muted">{{ $log->ip_address }}</small></td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="alert alert-info mb-0">
          <i class='bx bx-info-circle me-2'></i>
          No access logs available yet.
        </div>
        @endif
      </div>
    </div>

    <!-- Record Timestamps -->
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-3">
            <small class="text-muted">Created</small>
            <p class="mb-0">{{ $document->created_at->format('d/m/Y H:i') }}</p>
          </div>
          <div class="col-md-3">
            <small class="text-muted">Last Updated</small>
            <p class="mb-0">{{ $document->updated_at->format('d/m/Y H:i') }}</p>
          </div>
          <div class="col-md-3">
            <small class="text-muted">Branch</small>
            <p class="mb-0">{{ $document->branch->name ?? 'Unknown' }}</p>
          </div>
          <div class="col-md-3">
            <small class="text-muted">Retention Until</small>
            <p class="mb-0">{{ $document->retention_until ? $document->retention_until->format('d/m/Y') : 'Not set' }}</p>
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
        <a href="{{ route('documents.edit', $document) }}" class="btn btn-primary w-100 mb-2">
          <i class='bx bx-edit me-1'></i> Edit Document
        </a>

        <a href="{{ route('documents.download', $document) }}" class="btn btn-info w-100 mb-2">
          <i class='bx bx-download me-1'></i> Download File
        </a>

        <button type="button" class="btn btn-outline-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#shareModal">
          <i class='bx bx-share-alt me-1'></i> Share Document
        </button>

        <button type="button" class="btn btn-outline-secondary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#versionModal">
          <i class='bx bx-history me-1'></i> New Version
        </button>

        @if($document->requires_review && $document->review_status === 'pending')
        <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#approveModal">
          <i class='bx bx-check me-1'></i> Approve Document
        </button>

        <button type="button" class="btn btn-warning w-100 mb-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
          <i class='bx bx-x me-1'></i> Reject Document
        </button>
        @endif

        <hr>

        <button type="button" class="btn btn-danger w-100 mb-2" data-bs-toggle="modal" data-bs-target="#deleteModal">
          <i class='bx bx-trash me-1'></i> Delete Document
        </button>

        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary w-100">
          <i class='bx bx-arrow-back me-1'></i> Back to Library
        </a>
      </div>
    </div>

    <!-- Document Statistics -->
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Document Statistics</h6>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <small class="text-muted">Total Views</small>
          <h4 class="mb-0">{{ $document->accessLogs()->where('action', 'view')->count() }}</h4>
        </div>
        <div class="mb-3">
          <small class="text-muted">Total Downloads</small>
          <h4 class="mb-0 text-primary">{{ $document->accessLogs()->where('action', 'downloaded')->count() }}</h4>
        </div>
        <div class="mb-3">
          <small class="text-muted">Total Versions</small>
          <h4 class="mb-0 text-info">{{ $document->versions()->count() }}</h4>
        </div>
        <div class="mb-0">
          <small class="text-muted">Last Accessed</small>
          <h5 class="mb-0">
            @php
              $lastAccess = $document->accessLogs()->orderBy('accessed_at', 'desc')->first();
            @endphp
            @if($lastAccess)
              {{ $lastAccess->accessed_at->diffForHumans() }}
            @else
              <span class="text-muted">Never</span>
            @endif
          </h5>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Share Document Modal -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Share Document</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('documents.share', $document) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="expires_in_hours" class="form-label">Link Expiration (hours) *</label>
            <input type="number" id="expires_in_hours" name="expires_in_hours" class="form-control"
                   value="24" min="1" max="168" required>
            <small class="text-muted">Maximum: 168 hours (7 days)</small>
          </div>
          <div class="alert alert-info mb-0">
            <i class='bx bx-info-circle me-2'></i>
            A secure, time-limited link will be generated for external sharing. The link will expire after the specified time.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class='bx bx-share-alt me-1'></i> Generate Share Link
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- New Version Modal -->
<div class="modal fade" id="versionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Upload New Version</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('documents.version.create', $document) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="file" class="form-label">Select File *</label>
            <input type="file" id="file" name="file" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="change_notes" class="form-label">Change Notes *</label>
            <textarea id="change_notes" name="change_notes" class="form-control" rows="3"
                      placeholder="Describe what changed in this version..." required></textarea>
          </div>
          <div class="alert alert-warning mb-0">
            <i class='bx bx-info-circle me-2'></i>
            <strong>Note:</strong> Previous version will be archived and the new file will become the current version (v{{ $document->current_version + 1 }}).
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class='bx bx-upload me-1'></i> Upload New Version
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Approve Modal -->
@if($document->requires_review && $document->review_status === 'pending')
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Approve Document</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('documents.approve', $document) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="review_notes" class="form-label">Review Notes</label>
            <textarea id="review_notes" name="review_notes" class="form-control" rows="3"
                      placeholder="Optional review comments..."></textarea>
          </div>
          <div class="alert alert-success mb-0">
            <i class='bx bx-check-circle me-2'></i>
            This document will be marked as <strong>Approved</strong> and become active for use.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class='bx bx-check me-1'></i> Approve Document
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reject Document</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('documents.reject', $document) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="reject_notes" class="form-label">Rejection Reason *</label>
            <textarea id="reject_notes" name="review_notes" class="form-control" rows="3"
                      placeholder="Please provide a reason for rejection..." required></textarea>
          </div>
          <div class="alert alert-warning mb-0">
            <i class='bx bx-info-circle me-2'></i>
            This document will be marked as <strong>Rejected</strong> and the uploader will be notified.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">
            <i class='bx bx-x me-1'></i> Reject Document
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

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
          <li>Keep all {{ $document->versions()->count() }} version(s) for 7-year retention policy</li>
          <li>Preserve {{ $document->accessLogs()->count() }} access log entries</li>
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
@endsection

@section('page-script')
<script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
<script>
$(document).ready(function() {
  'use strict';

  // Initialize DataTables for version history
  if ($('#versionsTable').length) {
    $('#versionsTable').DataTable({
      order: [[0, 'desc']],
      pageLength: 10,
      responsive: true,
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
    });
  }

  // Initialize DataTables for access logs
  if ($('#accessLogsTable').length) {
    $('#accessLogsTable').DataTable({
      order: [[2, 'desc']],
      pageLength: 10,
      responsive: true,
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
    });
  }
});
</script>
@endsection
