@extends('layouts/layoutMaster')

@section('title', 'Inspection Details - ' . $inspection->inspection_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-1">{{ $inspection->inspection_number }}</h4>
    <p class="mb-0">{{ ucfirst(str_replace('_', ' ', $inspection->inspection_type)) }}</p>
  </div>
  <div>
    <a href="{{ route('safety-inspections.edit', $inspection) }}" class="btn btn-outline-primary me-2">
      <i class='bx bx-edit me-1'></i> Edit
    </a>
    <a href="{{ route('safety-inspections.index') }}" class="btn btn-outline-secondary">
      <i class='bx bx-arrow-back me-1'></i> Back to List
    </a>
  </div>
</div>

<div class="row">
  <!-- Main Content -->
  <div class="col-md-8">
    <!-- Inspection Details -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Inspection Details</h5>
        @php
        $statusClass = match($inspection->status) {
          'scheduled' => 'primary',
          'in_progress' => 'warning',
          'completed' => 'success',
          'under_review' => 'info',
          'approved' => 'success',
          'rejected' => 'danger',
          default => 'secondary'
        };
        @endphp
        <span class="badge bg-{{ $statusClass }} fs-6">{{ ucfirst(str_replace('_', ' ', $inspection->status)) }}</span>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <small class="text-muted">Inspection Type</small>
            <p class="mb-0">{{ ucfirst(str_replace('_', ' ', $inspection->inspection_type)) }}</p>
          </div>
          <div class="col-md-6 mb-3">
            <small class="text-muted">Scheduled Date</small>
            <p class="mb-0">{{ $inspection->scheduled_date ? $inspection->scheduled_date->format('d/m/Y') : 'N/A' }}</p>
          </div>
          <div class="col-md-6 mb-3">
            <small class="text-muted">Location</small>
            <p class="mb-0">{{ $inspection->location ?? 'N/A' }}</p>
          </div>
          <div class="col-md-6 mb-3">
            <small class="text-muted">Area / Zone</small>
            <p class="mb-0">{{ $inspection->area ?? 'N/A' }}</p>
          </div>
          @if($inspection->asset_tag)
          <div class="col-md-6 mb-3">
            <small class="text-muted">Asset Tag</small>
            <p class="mb-0">{{ $inspection->asset_tag }}</p>
          </div>
          @endif
          @if($inspection->vehicle)
          <div class="col-md-6 mb-3">
            <small class="text-muted">Vehicle</small>
            <p class="mb-0">{{ $inspection->vehicle->registration_number }} - {{ $inspection->vehicle->make }} {{ $inspection->vehicle->model }}</p>
          </div>
          @endif
        </div>

        @if($inspection->inspector_notes)
        <hr>
        <div>
          <small class="text-muted">Inspector Notes</small>
          <p class="mb-0">{{ $inspection->inspector_notes }}</p>
        </div>
        @endif
      </div>
    </div>

    <!-- Checklist Items -->
    @if($inspection->checklistItems && $inspection->checklistItems->count() > 0)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Inspection Checklist ({{ $inspection->checklistItems->count() }} items)</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th style="width: 5%">#</th>
                <th>Checklist Item</th>
                <th style="width: 15%">Result</th>
                <th style="width: 15%">Response</th>
                <th style="width: 10%">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($inspection->checklistItems as $index => $item)
              <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                  <strong>{{ $item->item_description }}</strong>
                  @if($item->is_mandatory)
                  <span class="badge badge-light-danger">Mandatory</span>
                  @endif
                  @if($item->is_critical)
                  <span class="badge badge-light-warning">Critical</span>
                  @endif
                </td>
                <td>
                  @if($item->result)
                  @php
                  $resultClass = match($item->result) {
                    'pass' => 'success',
                    'fail' => 'danger',
                    'na' => 'secondary',
                    default => 'secondary'
                  };
                  @endphp
                  <span class="badge bg-{{ $resultClass }}">{{ strtoupper($item->result) }}</span>
                  @else
                  <span class="badge bg-secondary">Pending</span>
                  @endif
                </td>
                <td>
                  @if($item->response_value)
                  <small>{{ $item->response_value }}</small>
                  @elseif($item->response_notes)
                  <small>{{ Str::limit($item->response_notes, 30) }}</small>
                  @else
                  <small class="text-muted">No response</small>
                  @endif
                </td>
                <td>
                  <button type="button" class="btn btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#itemModal{{ $item->id }}">
                    <i class='bx bx-show'></i>
                  </button>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    @else
    <div class="card mb-4">
      <div class="card-body text-center py-5">
        <i class='bx bx-clipboard display-4 text-muted mb-3'></i>
        <p class="text-muted mb-0">No checklist items yet. Start the inspection to add items.</p>
      </div>
    </div>
    @endif

    <!-- Review Comments (if applicable) -->
    @if($inspection->reviewer_comments || $inspection->rejection_reason)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Review Comments</h5>
      </div>
      <div class="card-body">
        @if($inspection->reviewer_comments)
        <div class="mb-3">
          <small class="text-muted">Reviewer Comments</small>
          <p class="mb-0">{{ $inspection->reviewer_comments }}</p>
        </div>
        @endif
        @if($inspection->rejection_reason)
        <div class="alert alert-danger mb-0">
          <strong>Rejection Reason:</strong> {{ $inspection->rejection_reason }}
        </div>
        @endif
      </div>
    </div>
    @endif
  </div>

  <!-- Sidebar -->
  <div class="col-md-4">
    <!-- Inspector Information -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Inspector</h5>
      </div>
      <div class="card-body">
        <div class="d-flex align-items-center mb-3">
          <div class="avatar avatar-md me-3">
            <span class="avatar-initial rounded-circle bg-label-primary">
              {{ strtoupper(substr($inspection->inspector->name, 0, 2)) }}
            </span>
          </div>
          <div>
            <h6 class="mb-0">{{ $inspection->inspector->name }}</h6>
            <small class="text-muted">{{ $inspection->inspector->email }}</small>
          </div>
        </div>
        @if($inspection->inspection_date)
        <div class="mb-2">
          <small class="text-muted">Inspection Date</small>
          <p class="mb-0">{{ $inspection->inspection_date->format('d/m/Y H:i') }}</p>
        </div>
        @endif
      </div>
    </div>

    <!-- Reviewer Information (if applicable) -->
    @if($inspection->reviewer)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Reviewer</h5>
      </div>
      <div class="card-body">
        <div class="d-flex align-items-center mb-3">
          <div class="avatar avatar-md me-3">
            <span class="avatar-initial rounded-circle bg-label-success">
              {{ strtoupper(substr($inspection->reviewer->name, 0, 2)) }}
            </span>
          </div>
          <div>
            <h6 class="mb-0">{{ $inspection->reviewer->name }}</h6>
            <small class="text-muted">{{ $inspection->reviewer->email }}</small>
          </div>
        </div>
        @if($inspection->review_date)
        <div class="mb-2">
          <small class="text-muted">Review Date</small>
          <p class="mb-0">{{ $inspection->review_date->format('d/m/Y H:i') }}</p>
        </div>
        @endif
      </div>
    </div>
    @endif

    <!-- Template Information (if used) -->
    @if($inspection->template)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Template Used</h5>
      </div>
      <div class="card-body">
        <h6 class="mb-2">{{ $inspection->template->template_name }}</h6>
        <p class="text-muted mb-2"><small>{{ $inspection->template->description }}</small></p>
        @if($inspection->template->is_scored)
        <div class="mb-2">
          <small class="text-muted">Pass Threshold</small>
          <p class="mb-0">{{ $inspection->template->pass_threshold }}%</p>
        </div>
        @endif
      </div>
    </div>
    @endif

    <!-- Workflow Actions -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Actions</h5>
      </div>
      <div class="card-body">
        @if($inspection->status === 'scheduled')
        <form action="{{ route('safety-inspections.start', $inspection) }}" method="POST" class="mb-2">
          @csrf
          <button type="submit" class="btn btn-primary w-100">
            <i class='bx bx-play-circle me-1'></i> Start Inspection
          </button>
        </form>
        @endif

        @if($inspection->status === 'in_progress')
        <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#completeModal">
          <i class='bx bx-check-circle me-1'></i> Complete Inspection
        </button>
        @endif

        @if($inspection->status === 'completed')
        <form action="{{ route('safety-inspections.submit', $inspection) }}" method="POST" class="mb-2">
          @csrf
          <button type="submit" class="btn btn-info w-100">
            <i class='bx bx-paper-plane me-1'></i> Submit for Review
          </button>
        </form>
        @endif

        @if($inspection->status === 'under_review' && auth()->user()->can('approve-inspections'))
        <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#approveModal">
          <i class='bx bx-check me-1'></i> Approve
        </button>
        <button type="button" class="btn btn-danger w-100 mb-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
          <i class='bx bx-x me-1'></i> Reject
        </button>
        @endif

        @if(in_array($inspection->status, ['completed', 'approved']) && auth()->user()->can('escalate-inspections'))
        <button type="button" class="btn btn-warning w-100 mb-2" data-bs-toggle="modal" data-bs-target="#escalateModal">
          <i class='bx bx-error me-1'></i> Escalate
        </button>
        @endif

        <hr>

        <a href="{{ route('safety-inspections.edit', $inspection) }}" class="btn btn-outline-primary w-100 mb-2">
          <i class='bx bx-edit me-1'></i> Edit Details
        </a>

        <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteModal">
          <i class='bx bx-trash me-1'></i> Delete Inspection
        </button>
      </div>
    </div>

    <!-- Metadata -->
    <div class="card">
      <div class="card-body">
        <small class="text-muted d-block mb-1">Created</small>
        <p class="mb-2">{{ $inspection->created_at->format('d/m/Y H:i') }}</p>
        <small class="text-muted d-block mb-1">Last Updated</small>
        <p class="mb-0">{{ $inspection->updated_at->format('d/m/Y H:i') }}</p>
      </div>
    </div>
  </div>
</div>

<!-- Complete Inspection Modal -->
<div class="modal fade" id="completeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Complete Inspection</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('safety-inspections.complete', $inspection) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="inspector_notes" class="form-label">Inspector Notes</label>
            <textarea id="inspector_notes"
                      name="inspector_notes"
                      rows="4"
                      class="form-control"
                      placeholder="Additional notes or observations...">{{ old('inspector_notes', $inspection->inspector_notes) }}</textarea>
          </div>
          <div class="mb-3">
            <label for="inspector_signature_path" class="form-label">Digital Signature (Optional)</label>
            <input type="text"
                   id="inspector_signature_path"
                   name="inspector_signature_path"
                   class="form-control"
                   placeholder="Signature path (Phase 4 implementation)"
                   value="{{ old('inspector_signature_path') }}">
            <small class="form-text text-muted">Digital signature capture will be implemented in Phase 4</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class='bx bx-check-circle me-1'></i> Complete Inspection
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
        <h5 class="modal-title">Approve Inspection</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('safety-inspections.approve', $inspection) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="reviewer_comments" class="form-label">Reviewer Comments (Optional)</label>
            <textarea id="reviewer_comments"
                      name="reviewer_comments"
                      rows="3"
                      class="form-control"
                      placeholder="Comments or feedback..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class='bx bx-check me-1'></i> Approve
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
        <h5 class="modal-title">Reject Inspection</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('safety-inspections.reject', $inspection) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="rejection_reason" class="form-label">Rejection Reason *</label>
            <textarea id="rejection_reason"
                      name="rejection_reason"
                      rows="3"
                      class="form-control"
                      placeholder="Explain why this inspection is being rejected..."
                      required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class='bx bx-x me-1'></i> Reject
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Escalate Modal -->
<div class="modal fade" id="escalateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Escalate Inspection</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('safety-inspections.escalate', $inspection) }}" method="POST">
        @csrf
        <div class="modal-body">
          <p class="mb-3">Escalate this inspection to senior management or a specialist for further review.</p>
          <div class="mb-3">
            <label for="assigned_to_user_id" class="form-label">Assign To (Optional)</label>
            <select id="assigned_to_user_id" name="assigned_to_user_id" class="form-select">
              <option value="">-- Select User --</option>
              @foreach(\App\Models\User::where('branch_id', auth()->user()->branch_id)->get() as $user)
              <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">
            <i class='bx bx-error me-1'></i> Escalate
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
        <h5 class="modal-title">Delete Inspection</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('safety-inspections.destroy', $inspection) }}" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <p>Are you sure you want to delete this inspection?</p>
          <div class="alert alert-danger mb-0">
            <i class='bx bx-error-circle me-2'></i>
            <strong>Warning:</strong> This action cannot be undone. All checklist items and responses will be permanently deleted.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class='bx bx-trash me-1'></i> Delete Inspection
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Checklist Item Modals (Dynamic) -->
@if($inspection->checklistItems && $inspection->checklistItems->count() > 0)
@foreach($inspection->checklistItems as $item)
<div class="modal fade" id="itemModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Checklist Item Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <h6>{{ $item->item_description }}</h6>
        @if($item->result)
        <p class="mb-2">
          <strong>Result:</strong>
          @php
          $resultClass = match($item->result) {
            'pass' => 'success',
            'fail' => 'danger',
            'na' => 'secondary',
            default => 'secondary'
          };
          @endphp
          <span class="badge bg-{{ $resultClass }}">{{ strtoupper($item->result) }}</span>
        </p>
        @endif
        @if($item->response_value)
        <p class="mb-2"><strong>Response Value:</strong> {{ $item->response_value }}</p>
        @endif
        @if($item->response_notes)
        <p class="mb-2"><strong>Response Notes:</strong> {{ $item->response_notes }}</p>
        @endif
        @if($item->photo_urls && count($item->photo_urls) > 0)
        <p class="mb-2"><strong>Photos:</strong> {{ count($item->photo_urls) }} attached</p>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endforeach
@endif
@endsection
