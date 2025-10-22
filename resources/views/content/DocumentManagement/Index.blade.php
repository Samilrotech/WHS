@php
  setcookie('contentLayout', 'wide', time() + 60 * 60 * 24 * 365, '/');
  $pageConfigs = ['contentLayout' => 'wide'];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Document Management')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
@endsection

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
  <!-- Expiring Documents Alert -->
  @if($documents->filter(fn($doc) => $doc->isExpiringSoon())->count() > 0)
  <div class="alert alert-warning alert-dismissible mb-4" role="alert">
    <h5 class="alert-heading mb-2">
      <i class="icon-base ti ti-calendar-exclamation"></i>
      {{ $documents->filter(fn($doc) => $doc->isExpiringSoon())->count() }} Document(s) Expiring Soon
    </h5>
    <p class="mb-0">You have documents expiring within the next 30 days. Please review and renew as needed.</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <x-whs.hero
    eyebrow="Document Control"
    title="Document Management"
    subtitle="Upload, organize, version control, and manage document lifecycle with review workflows and expiry tracking."
    :metric="true"
    metricLabel="Total documents"
    :metricValue="$documents->total() ?? 0"
    metricCaption="All documents"
    :searchRoute="route('documents.index')"
    searchPlaceholder="Search documents, categories, tags…"
    :createRoute="'#'"
    createLabel="Upload document"
    createModal="uploadDocumentModal"
  />

  <section class="whs-metrics">
    <x-whs.metric-card
      icon="ti-file"
      iconVariant="brand"
      label="Total Documents"
      :value="$documents->total() ?? 0"
      meta="All documents"
    />

    <x-whs.metric-card
      icon="ti-clock-check"
      iconVariant="warning"
      label="Pending Review"
      :value="$documents->where('review_status', 'pending')->count()"
      meta="Awaiting approval"
      metaClass="text-warning"
    />

    <x-whs.metric-card
      icon="ti-calendar-event"
      iconVariant="info"
      label="Expiring Soon"
      :value="$documents->filter(fn($doc) => $doc->isExpiringSoon())->count()"
      meta="Within 30 days"
      metaClass="text-info"
    />

    <x-whs.metric-card
      icon="ti-alert-octagon"
      iconVariant="critical"
      label="Expired"
      :value="$documents->where('is_expired', true)->count()"
      meta="Require renewal"
      metaClass="text-danger"
    />
  </section>

  <div class="whs-layout">
    <div class="whs-main">
      <div class="whs-section-heading">
        <div>
          <h2>Document repository</h2>
          <p>All documents with version control, review status, and expiry tracking.</p>
        </div>
        <span class="whs-updated">Updated {{ now()->format('H:i') }}</span>
      </div>

      <div class="whs-card-list">
        @forelse ($documents as $document)
          @php
            $severity = $document->is_expired ? 'critical' :
                       ($document->isExpiringSoon() ? 'high' :
                       ($document->review_status === 'rejected' ? 'medium' : 'low'));
            $fileIcon = match($document->file_type) {
              'pdf' => 'ti-file-type-pdf',
              'doc', 'docx' => 'ti-file-type-doc',
              'xls', 'xlsx' => 'ti-file-type-xls',
              'jpg', 'jpeg', 'png' => 'ti-photo',
              'zip', 'rar' => 'ti-file-zip',
              default => 'ti-file'
            };
          @endphp

          <x-whs.card :severity="$severity">
            <div class="whs-card__header">
              <span class="whs-chip whs-chip--id">
                <i class="icon-base ti {{ $fileIcon }} me-1"></i>
                {{ $document->document_number }}
              </span>
              <span class="whs-chip whs-chip--status whs-chip--status-{{ strtolower($document->review_status ?? 'draft') }}">
                {{ ucfirst($document->review_status ?? 'draft') }}
              </span>
            </div>

            <div class="whs-card__body">
              <div>
                <h3>{{ $document->title }}</h3>
                <p>{{ $document->category->name ?? 'Uncategorized' }} • v{{ $document->current_version }}</p>
              </div>
              <div>
                <span class="whs-location-label">File Details</span>
                <span>{{ strtoupper($document->file_type) }} • {{ $document->formatted_file_size }}</span>
              </div>
              <div>
                <span class="whs-location-label">Uploaded By</span>
                <span>{{ $document->uploader->name ?? 'Unknown' }}</span>
              </div>
              <div>
                <span class="whs-location-label">Expiry Date</span>
                <span>
                  @if($document->expiry_date)
                    {{ $document->expiry_date->format('d M Y') }}
                    @if($document->is_expired)
                      <span class="whs-chip whs-chip--severity whs-chip--severity-critical ms-1">Expired</span>
                    @elseif($document->isExpiringSoon())
                      <span class="whs-chip whs-chip--severity whs-chip--severity-high ms-1">Expiring Soon</span>
                    @endif
                  @else
                    <span class="text-muted">No expiry</span>
                  @endif
                </span>
              </div>
            </div>

            <div class="whs-card__footer">
              <div class="whs-card__actions">
                <a href="{{ route('documents.show', $document) }}" class="whs-action-btn" aria-label="View document">
                  <i class="icon-base ti ti-external-link"></i>
                  <span>Open</span>
                </a>

                <a href="{{ route('documents.download', $document) }}" class="whs-action-btn" aria-label="Download">
                  <i class="icon-base ti ti-download"></i>
                  <span>Download</span>
                </a>

                <button type="button" class="whs-action-btn" onclick="newVersion('{{ $document->id }}', '{{ $document->title }}')">
                  <i class="icon-base ti ti-versions"></i>
                  <span>New Version</span>
                </button>

                @if($document->isPendingReview())
                  <button type="button" class="whs-action-btn whs-action-btn--success" onclick="approveDocument('{{ $document->id }}', '{{ $document->title }}')">
                    <i class="icon-base ti ti-check"></i>
                    <span>Approve</span>
                  </button>

                  <button type="button" class="whs-action-btn whs-action-btn--danger" onclick="rejectDocument('{{ $document->id }}', '{{ $document->title }}')">
                    <i class="icon-base ti ti-x"></i>
                    <span>Reject</span>
                  </button>
                @endif

                <a href="{{ route('documents.edit', $document) }}" class="whs-action-btn">
                  <i class="icon-base ti ti-edit"></i>
                  <span>Edit</span>
                </a>

                <button type="button" class="whs-action-btn whs-action-btn--danger" onclick="deleteDocument('{{ $document->id }}', '{{ $document->title }}')">
                  <i class="icon-base ti ti-trash"></i>
                  <span>Delete</span>
                </button>
              </div>
              <button class="whs-card__more">
                <i class="icon-base ti ti-dots"></i>
              </button>
            </div>
          </x-whs.card>
        @empty
          <div class="whs-empty">
            <div class="whs-empty__content">
              <i class="icon-base ti ti-files whs-empty__icon"></i>
              <h3>No documents yet</h3>
              <p>No documents have been uploaded to the system. Start building your document repository.</p>
              <button type="button" class="whs-btn-primary whs-btn-primary--ghost" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                <i class="icon-base ti ti-upload me-2"></i>
                Upload first document
              </button>
            </div>
          </div>
        @endforelse
      </div>
    </div>

    <aside class="whs-sidebar">
      <x-whs.sidebar-panel title="Document status">
        <ul class="whs-sidebar__stats">
          <li>
            <span>Pending Review</span>
            <strong class="text-warning">{{ $documents->where('review_status', 'pending')->count() }}</strong>
          </li>
          <li>
            <span>Expiring Soon</span>
            <strong class="text-info">{{ $documents->filter(fn($doc) => $doc->isExpiringSoon())->count() }}</strong>
          </li>
          <li>
            <span>Expired</span>
            <strong class="text-danger">{{ $documents->where('is_expired', true)->count() }}</strong>
          </li>
        </ul>
        <p class="whs-sidebar__caption">
          Centralized document control with version history, approval workflows, and automated expiry notifications.
        </p>
      </x-whs.sidebar-panel>

      <x-whs.sidebar-panel title="Document types">
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
          <div style="padding: 0.75rem; background: linear-gradient(135deg, rgba(244, 246, 255, 0.96), rgba(228, 233, 255, 0.98)); border-radius: 12px; border: 1px solid rgba(0, 71, 255, 0.12);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Policies & Procedures</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">WHS policies and operational procedures</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Forms & Templates</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Reusable forms and document templates</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Certifications</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">License and certification documents</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Reports & Records</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Compliance reports and audit records</span>
          </div>
        </div>
      </x-whs.sidebar-panel>
    </aside>
  </div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Upload New Document</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label for="file" class="form-label">Select File *</label>
              <input type="file" id="file" name="file" class="form-control" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip">
              <small class="text-muted">Max file size: 50MB. Supported formats: PDF, DOC, XLS, PPT, Images, ZIP</small>
            </div>
            <div class="col-md-8">
              <label for="title" class="form-label">Document Title *</label>
              <input type="text" id="title" name="title" class="form-control" placeholder="Enter document title" required>
            </div>
            <div class="col-md-4">
              <label for="category_id" class="form-label">Category *</label>
              <select id="category_id" name="category_id" class="form-select" required>
                <option value="">Select category</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12">
              <label for="description" class="form-label">Description</label>
              <textarea id="description" name="description" class="form-control" rows="3" placeholder="Brief description of the document"></textarea>
            </div>
            <div class="col-md-6">
              <label for="visibility" class="form-label">Visibility *</label>
              <select id="visibility" name="visibility" class="form-select" required>
                <option value="public">Public (All users)</option>
                <option value="private">Private (Only me)</option>
                <option value="restricted">Restricted (Selected users/roles)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="expiry_date" class="form-label">Expiry Date</label>
              <input type="text" id="expiry_date" name="expiry_date" class="form-control flatpickr-date" placeholder="Select expiry date">
            </div>
            <div class="col-md-6">
              <label for="tags" class="form-label">Tags</label>
              <input type="text" id="tags" name="tags" class="form-control" placeholder="Enter tags separated by commas">
              <small class="text-muted">e.g., Safety, Compliance, Training</small>
            </div>
            <div class="col-md-6">
              <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" id="requires_review" name="requires_review" value="1">
                <label class="form-check-label" for="requires_review">
                  Requires approval before publishing
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="icon-base ti ti-upload me-1"></i> Upload Document
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- New Version Modal -->
<div class="modal fade" id="newVersionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Upload New Version</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="newVersionForm" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <p class="mb-3">Uploading new version for: <strong id="versionDocTitle"></strong></p>
          <div class="mb-3">
            <label for="version_file" class="form-label">Select New Version File *</label>
            <input type="file" id="version_file" name="file" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="change_notes" class="form-label">Change Notes *</label>
            <textarea id="change_notes" name="change_notes" class="form-control" rows="3" placeholder="Describe what changed in this version" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="icon-base ti ti-versions me-1"></i> Upload Version
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Approve Document Modal -->
<div class="modal fade" id="approveDocumentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Approve Document</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="approveDocumentForm" method="POST">
        @csrf
        <div class="modal-body">
          <p class="mb-3">Approving document: <strong id="approveDocTitle"></strong></p>
          <div class="mb-3">
            <label for="approve_review_notes" class="form-label">Review Notes</label>
            <textarea id="approve_review_notes" name="review_notes" class="form-control" rows="3" placeholder="Optional notes about the approval"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class="icon-base ti ti-check me-1"></i> Approve Document
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Reject Document Modal -->
<div class="modal fade" id="rejectDocumentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reject Document</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="rejectDocumentForm" method="POST">
        @csrf
        <div class="modal-body">
          <p class="mb-3">Rejecting document: <strong id="rejectDocTitle"></strong></p>
          <div class="mb-3">
            <label for="reject_review_notes" class="form-label">Rejection Reason *</label>
            <textarea id="reject_review_notes" name="review_notes" class="form-control" rows="3" placeholder="Explain why this document is being rejected" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="icon-base ti ti-x me-1"></i> Reject Document
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
          <p class="mb-0">Are you sure you want to delete <strong id="deleteDocTitle"></strong>?</p>
          <p class="text-danger mb-0 mt-2">This action cannot be undone. All versions will also be deleted.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="icon-base ti ti-trash me-1"></i> Delete Document
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
  // Initialize Flatpickr
  if (typeof flatpickr !== 'undefined') {
    $('.flatpickr-date').flatpickr({
      dateFormat: "Y-m-d",
      minDate: "today"
    });
  }
});

// New Version Modal
function newVersion(documentId, documentTitle) {
  document.getElementById('versionDocTitle').textContent = documentTitle;
  document.getElementById('newVersionForm').action = `/documents/${documentId}/version`;
  new bootstrap.Modal(document.getElementById('newVersionModal')).show();
}

// Approve Document Modal
function approveDocument(documentId, documentTitle) {
  document.getElementById('approveDocTitle').textContent = documentTitle;
  document.getElementById('approveDocumentForm').action = `/documents/${documentId}/approve`;
  new bootstrap.Modal(document.getElementById('approveDocumentModal')).show();
}

// Reject Document Modal
function rejectDocument(documentId, documentTitle) {
  document.getElementById('rejectDocTitle').textContent = documentTitle;
  document.getElementById('rejectDocumentForm').action = `/documents/${documentId}/reject`;
  new bootstrap.Modal(document.getElementById('rejectDocumentModal')).show();
}

// Delete Document Modal
function deleteDocument(documentId, documentTitle) {
  document.getElementById('deleteDocTitle').textContent = documentTitle;
  document.getElementById('deleteForm').action = `/documents/${documentId}`;
  new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endsection

