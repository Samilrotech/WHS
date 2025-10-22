@extends('layouts/layoutMaster')

@section('title', 'Edit Inspection - ' . $inspection->inspection_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-1">Edit Inspection</h4>
    <p class="mb-0">{{ $inspection->inspection_number }}</p>
  </div>
  <div>
    <a href="{{ route('safety-inspections.show', $inspection) }}" class="btn btn-outline-secondary me-2">
      <i class='bx bx-show me-1'></i> View Details
    </a>
    <a href="{{ route('safety-inspections.index') }}" class="btn btn-outline-secondary">
      <i class='bx bx-arrow-back me-1'></i> Back to List
    </a>
  </div>
</div>

<form action="{{ route('safety-inspections.update', $inspection) }}" method="POST" id="safetyInspectionForm">
  @csrf
  @method('PUT')

  <div class="row">
    <!-- Main Form Card -->
    <div class="col-md-8">
      <!-- Inspection Details -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Inspection Details</h5>
        </div>
        <div class="card-body">
          <!-- Inspection Type -->
          <div class="mb-3">
            <label for="inspection_type" class="form-label">Inspection Type</label>
            <select id="inspection_type" name="inspection_type" class="form-select @error('inspection_type') is-invalid @enderror">
              <option value="workplace_safety" {{ old('inspection_type', $inspection->inspection_type) == 'workplace_safety' ? 'selected' : '' }}>Workplace Safety Audit</option>
              <option value="equipment_safety" {{ old('inspection_type', $inspection->inspection_type) == 'equipment_safety' ? 'selected' : '' }}>Equipment Safety Check</option>
              <option value="contractor_induction" {{ old('inspection_type', $inspection->inspection_type) == 'contractor_induction' ? 'selected' : '' }}>Contractor Induction</option>
              <option value="pre_start_checklist" {{ old('inspection_type', $inspection->inspection_type) == 'pre_start_checklist' ? 'selected' : '' }}>Pre-Start Checklist</option>
              <option value="safety_audit" {{ old('inspection_type', $inspection->inspection_type) == 'safety_audit' ? 'selected' : '' }}>Safety System Audit</option>
              <option value="adhoc_inspection" {{ old('inspection_type', $inspection->inspection_type) == 'adhoc_inspection' ? 'selected' : '' }}>Ad-hoc Inspection</option>
              <option value="warehouse_safety" {{ old('inspection_type', $inspection->inspection_type) == 'warehouse_safety' ? 'selected' : '' }}>Warehouse Safety</option>
              <option value="office_safety" {{ old('inspection_type', $inspection->inspection_type) == 'office_safety' ? 'selected' : '' }}>Office Safety</option>
              <option value="vehicle_safety" {{ old('inspection_type', $inspection->inspection_type) == 'vehicle_safety' ? 'selected' : '' }}>Vehicle Safety Inspection</option>
            </select>
            @error('inspection_type')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Scheduled Date -->
          <div class="mb-3">
            <label for="scheduled_date" class="form-label">Scheduled Date</label>
            <input type="date"
                   id="scheduled_date"
                   name="scheduled_date"
                   class="form-control @error('scheduled_date') is-invalid @enderror"
                   value="{{ old('scheduled_date', $inspection->scheduled_date ? $inspection->scheduled_date->format('Y-m-d') : '') }}">
            @error('scheduled_date')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Location -->
          <div class="mb-3">
            <label for="location" class="form-label">Location</label>
            <input type="text"
                   id="location"
                   name="location"
                   class="form-control @error('location') is-invalid @enderror"
                   placeholder="e.g., Workshop, Site 5, Office Building A"
                   value="{{ old('location', $inspection->location) }}">
            @error('location')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Area / Zone -->
          <div class="mb-3">
            <label for="area" class="form-label">Area / Zone</label>
            <input type="text"
                   id="area"
                   name="area"
                   class="form-control @error('area') is-invalid @enderror"
                   placeholder="e.g., Assembly Line 2, Loading Dock, Admin Floor 3"
                   value="{{ old('area', $inspection->area) }}">
            @error('area')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Asset Tag (Optional) -->
          <div class="mb-3">
            <label for="asset_tag" class="form-label">Asset Tag (Optional)</label>
            <input type="text"
                   id="asset_tag"
                   name="asset_tag"
                   class="form-control @error('asset_tag') is-invalid @enderror"
                   placeholder="e.g., ASSET-001, EQ-12345"
                   value="{{ old('asset_tag', $inspection->asset_tag) }}">
            @error('asset_tag')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Inspector Notes -->
          <div class="mb-3">
            <label for="inspector_notes" class="form-label">Inspector Notes</label>
            <textarea id="inspector_notes"
                      name="inspector_notes"
                      rows="4"
                      class="form-control @error('inspector_notes') is-invalid @enderror"
                      placeholder="Additional notes or observations...">{{ old('inspector_notes', $inspection->inspector_notes) }}</textarea>
            @error('inspector_notes')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      <!-- Read-Only Information -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Read-Only Information</h5>
        </div>
        <div class="card-body">
          <div class="alert alert-info mb-3">
            <i class='bx bx-info-circle me-2'></i>
            The following fields cannot be modified after inspection creation.
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Inspection Number</label>
              <input type="text"
                     class="form-control"
                     value="{{ $inspection->inspection_number }}"
                     readonly
                     disabled>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Status</label>
              <input type="text"
                     class="form-control"
                     value="{{ ucfirst(str_replace('_', ' ', $inspection->status)) }}"
                     readonly
                     disabled>
            </div>

            @if($inspection->template)
            <div class="col-md-12 mb-3">
              <label class="form-label">Template</label>
              <input type="text"
                     class="form-control"
                     value="{{ $inspection->template->template_name }}"
                     readonly
                     disabled>
              <small class="form-text text-muted">Template cannot be changed after creation</small>
            </div>
            @endif

            <div class="col-md-6 mb-3">
              <label class="form-label">Created Date</label>
              <input type="text"
                     class="form-control"
                     value="{{ $inspection->created_at->format('d/m/Y H:i') }}"
                     readonly
                     disabled>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Last Updated</label>
              <input type="text"
                     class="form-control"
                     value="{{ $inspection->updated_at->format('d/m/Y H:i') }}"
                     readonly
                     disabled>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
      <!-- Inspector Information -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Inspector</h5>
        </div>
        <div class="card-body">
          <div class="d-flex align-items-center">
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
          <small class="form-text text-muted mt-2 d-block">Inspector cannot be changed after creation</small>
        </div>
      </div>

      <!-- Status Information -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Current Status</h5>
        </div>
        <div class="card-body">
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
          <div class="mb-3">
            <span class="badge bg-{{ $statusClass }} fs-6">{{ ucfirst(str_replace('_', ' ', $inspection->status)) }}</span>
          </div>

          @if($inspection->status === 'scheduled')
          <div class="alert alert-primary mb-0">
            <i class='bx bx-calendar-event me-2'></i>
            This inspection is scheduled and can be started at any time.
          </div>
          @elseif($inspection->status === 'in_progress')
          <div class="alert alert-warning mb-0">
            <i class='bx bx-time me-2'></i>
            This inspection is currently in progress.
          </div>
          @elseif($inspection->status === 'completed')
          <div class="alert alert-success mb-0">
            <i class='bx bx-check-circle me-2'></i>
            This inspection has been completed.
          </div>
          @elseif($inspection->status === 'approved')
          <div class="alert alert-success mb-0">
            <i class='bx bx-check-double me-2'></i>
            This inspection has been approved.
          </div>
          @elseif($inspection->status === 'rejected')
          <div class="alert alert-danger mb-0">
            <i class='bx bx-x-circle me-2'></i>
            This inspection has been rejected.
          </div>
          @endif
        </div>
      </div>

      <!-- Checklist Summary (if exists) -->
      @if($inspection->checklistItems && $inspection->checklistItems->count() > 0)
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Checklist Summary</h5>
        </div>
        <div class="card-body">
          <div class="mb-2">
            <small class="text-muted">Total Items</small>
            <h4 class="mb-0">{{ $inspection->checklistItems->count() }}</h4>
          </div>
          @php
          $passCount = $inspection->checklistItems->where('result', 'pass')->count();
          $failCount = $inspection->checklistItems->where('result', 'fail')->count();
          $naCount = $inspection->checklistItems->where('result', 'na')->count();
          $pendingCount = $inspection->checklistItems->whereNull('result')->count();
          @endphp
          <div class="row mt-3">
            <div class="col-6">
              <small class="text-muted">Pass</small>
              <p class="mb-0 text-success fw-bold">{{ $passCount }}</p>
            </div>
            <div class="col-6">
              <small class="text-muted">Fail</small>
              <p class="mb-0 text-danger fw-bold">{{ $failCount }}</p>
            </div>
            <div class="col-6 mt-2">
              <small class="text-muted">N/A</small>
              <p class="mb-0 text-secondary fw-bold">{{ $naCount }}</p>
            </div>
            <div class="col-6 mt-2">
              <small class="text-muted">Pending</small>
              <p class="mb-0 text-muted fw-bold">{{ $pendingCount }}</p>
            </div>
          </div>
        </div>
      </div>
      @endif

      <!-- Form Actions -->
      <div class="card">
        <div class="card-body">
          <button type="submit" class="btn btn-primary w-100 mb-2">
            <i class='bx bx-save me-1'></i> Update Inspection
          </button>
          <a href="{{ route('safety-inspections.show', $inspection) }}" class="btn btn-outline-secondary w-100 mb-2">
            Cancel
          </a>

          <hr>

          <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteModal">
            <i class='bx bx-trash me-1'></i> Delete Inspection
          </button>
        </div>
      </div>
    </div>
  </div>
</form>

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
            <strong>Warning:</strong> This action cannot be undone. All checklist items, responses, and associated data will be permanently deleted.
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
@endsection

@section('page-script')
<script>
$(document).ready(function() {
  // Form validation
  $('#safetyInspectionForm').on('submit', function(e) {
    const inspectionType = $('#inspection_type').val();

    if (!inspectionType) {
      e.preventDefault();
      alert('Please select an inspection type.');
      $('#inspection_type').focus();
      return false;
    }
  });
});
</script>
@endsection
