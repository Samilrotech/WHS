@extends('layouts/layoutMaster')

@section('title', 'Edit Training Record - ' . $record->certification_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-1">Edit Training Record</h4>
    <p class="mb-0">{{ $record->trainingTypeLabel() }} - {{ $record->user->name }}</p>
  </div>
  <div>
    <a href="{{ route('training.records.show', $record) }}" class="btn btn-outline-secondary me-2">
      <i class='bx bx-show me-1'></i> View Details
    </a>
    <a href="{{ route('training.records.index') }}" class="btn btn-outline-secondary">
      <i class='bx bx-arrow-back me-1'></i> Back to List
    </a>
  </div>
</div>

<form action="{{ route('training.records.update', $record) }}" method="POST" id="trainingRecordForm" enctype="multipart/form-data">
  @csrf
  @method('PUT')

  <div class="row">
    <!-- Main Form Card -->
    <div class="col-md-8">
      <!-- Read-Only Information -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Read-Only Information</h5>
        </div>
        <div class="card-body">
          <div class="alert alert-info mb-3">
            <i class='bx bx-info-circle me-2'></i>
            The following fields cannot be modified after record creation.
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Employee</label>
              <input type="text"
                     class="form-control"
                     value="{{ $record->user->name }} ({{ $record->user->email }})"
                     readonly
                     disabled>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Training Type</label>
              <input type="text"
                     class="form-control"
                     value="{{ $record->trainingTypeLabel() }}"
                     readonly
                     disabled>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Issue Date</label>
              <input type="text"
                     class="form-control"
                     value="{{ $record->issue_date->format('d/m/Y') }}"
                     readonly
                     disabled>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Created Date</label>
              <input type="text"
                     class="form-control"
                     value="{{ $record->created_at->format('d/m/Y H:i') }}"
                     readonly
                     disabled>
            </div>
          </div>
        </div>
      </div>

      <!-- Editable Training Details -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Training Details</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="certification_number" class="form-label">Certification Number</label>
              <input type="text"
                     id="certification_number"
                     name="certification_number"
                     class="form-control @error('certification_number') is-invalid @enderror"
                     value="{{ old('certification_number', $record->certification_number) }}">
              @error('certification_number')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="issuing_authority" class="form-label">Issuing Authority *</label>
              <input type="text"
                     id="issuing_authority"
                     name="issuing_authority"
                     class="form-control @error('issuing_authority') is-invalid @enderror"
                     value="{{ old('issuing_authority', $record->issuing_authority) }}"
                     required>
              @error('issuing_authority')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="expiry_date" class="form-label">Expiry Date</label>
              <input type="date"
                     id="expiry_date"
                     name="expiry_date"
                     class="form-control @error('expiry_date') is-invalid @enderror"
                     value="{{ old('expiry_date', $record->expiry_date ? $record->expiry_date->format('Y-m-d') : '') }}">
              @error('expiry_date')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="status" class="form-label">Status *</label>
              <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="active" {{ old('status', $record->status) == 'active' ? 'selected' : '' }}>Active</option>
                <option value="expired" {{ old('status', $record->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                <option value="renewed" {{ old('status', $record->status) == 'renewed' ? 'selected' : '' }}>Renewed</option>
                <option value="suspended" {{ old('status', $record->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
              </select>
              @error('status')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="training_provider" class="form-label">Training Provider</label>
              <input type="text"
                     id="training_provider"
                     name="training_provider"
                     class="form-control @error('training_provider') is-invalid @enderror"
                     value="{{ old('training_provider', $record->training_provider) }}">
              @error('training_provider')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="training_duration_hours" class="form-label">Duration (Hours) *</label>
              <input type="number"
                     id="training_duration_hours"
                     name="training_duration_hours"
                     class="form-control @error('training_duration_hours') is-invalid @enderror"
                     step="0.5"
                     min="0.5"
                     value="{{ old('training_duration_hours', $record->training_duration_hours) }}"
                     required>
              @error('training_duration_hours')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="cost" class="form-label">Training Cost ($)</label>
              <input type="number"
                     id="cost"
                     name="cost"
                     class="form-control @error('cost') is-invalid @enderror"
                     step="0.01"
                     min="0"
                     value="{{ old('cost', $record->cost) }}">
              @error('cost')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="proficiency_level" class="form-label">Proficiency Level *</label>
              <select id="proficiency_level" name="proficiency_level" class="form-select @error('proficiency_level') is-invalid @enderror" required>
                <option value="none" {{ old('proficiency_level', $record->proficiency_level) == 'none' ? 'selected' : '' }}>None</option>
                <option value="basic" {{ old('proficiency_level', $record->proficiency_level) == 'basic' ? 'selected' : '' }}>Basic</option>
                <option value="intermediate" {{ old('proficiency_level', $record->proficiency_level) == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                <option value="advanced" {{ old('proficiency_level', $record->proficiency_level) == 'advanced' ? 'selected' : '' }}>Advanced</option>
                <option value="expert" {{ old('proficiency_level', $record->proficiency_level) == 'expert' ? 'selected' : '' }}>Expert</option>
              </select>
              @error('proficiency_level')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Renewal Settings -->
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="requires_renewal" name="requires_renewal" value="1" {{ old('requires_renewal', $record->requires_renewal) ? 'checked' : '' }}>
              <label class="form-check-label" for="requires_renewal">
                Requires Renewal
              </label>
            </div>
          </div>

          <div id="renewalFields" style="display: {{ old('requires_renewal', $record->requires_renewal) ? 'block' : 'none' }};">
            <div class="mb-3">
              <label for="renewal_interval_months" class="form-label">Renewal Interval (Months)</label>
              <input type="number"
                     id="renewal_interval_months"
                     name="renewal_interval_months"
                     class="form-control @error('renewal_interval_months') is-invalid @enderror"
                     min="1"
                     value="{{ old('renewal_interval_months', $record->renewal_interval_months) }}">
              @error('renewal_interval_months')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- Assessment Details -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Assessment Details</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="assessment_score" class="form-label">Assessment Score (0-100)</label>
              <input type="number"
                     id="assessment_score"
                     name="assessment_score"
                     class="form-control @error('assessment_score') is-invalid @enderror"
                     min="0"
                     max="100"
                     value="{{ old('assessment_score', $record->assessment_score) }}">
              @error('assessment_score')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="assessment_passed" class="form-label">Assessment Result</label>
              <select id="assessment_passed" name="assessment_passed" class="form-select @error('assessment_passed') is-invalid @enderror">
                <option value="">Not assessed</option>
                <option value="1" {{ old('assessment_passed', $record->assessment_passed) == '1' ? 'selected' : '' }}>Passed</option>
                <option value="0" {{ old('assessment_passed', $record->assessment_passed) == '0' ? 'selected' : '' }}>Failed</option>
              </select>
              @error('assessment_passed')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="mb-3">
            <label for="trainer_name" class="form-label">Trainer Name</label>
            <input type="text"
                   id="trainer_name"
                   name="trainer_name"
                   class="form-control @error('trainer_name') is-invalid @enderror"
                   value="{{ old('trainer_name', $record->trainer_name) }}">
            @error('trainer_name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      <!-- Additional Notes -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Additional Information</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea id="notes"
                      name="notes"
                      rows="4"
                      class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $record->notes) }}</textarea>
            @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label for="certificate_upload" class="form-label">Upload New Certificate (Optional)</label>
            <input type="file"
                   id="certificate_upload"
                   name="certificate_upload"
                   class="form-control @error('certificate_upload') is-invalid @enderror"
                   accept=".pdf,.jpg,.jpeg,.png">
            @error('certificate_upload')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Max file size: 10MB. Leave blank to keep existing certificate.</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
      <!-- Current Status -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Current Status</h5>
        </div>
        <div class="card-body">
          @php
          $statusClass = match($record->status) {
            'active' => 'success',
            'expired' => 'danger',
            'renewed' => 'info',
            'suspended' => 'warning',
            default => 'secondary'
          };
          @endphp
          <div class="mb-3">
            <span class="badge bg-{{ $statusClass }} fs-6">{{ ucfirst($record->status) }}</span>
          </div>

          @if($record->expiry_date)
          <div class="alert {{ $record->expiryAlertClass() }} mb-0">
            <i class='bx bx-calendar me-2'></i>
            <strong>{{ $record->expiryStatusText() }}</strong>
            <br>
            <small>Expires: {{ $record->expiry_date->format('d/m/Y') }}</small>
          </div>
          @endif

          @if($record->isCritical() && $record->status === 'expired')
          <div class="alert alert-danger mt-2 mb-0">
            <i class='bx bx-error-circle me-2'></i>
            <strong>Critical Certification Expired</strong>
            <br>
            <small>Employee access to related equipment is suspended.</small>
          </div>
          @endif
        </div>
      </div>

      <!-- Record Information -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Record Information</h5>
        </div>
        <div class="card-body">
          <div class="mb-2">
            <small class="text-muted">Branch</small>
            <p class="mb-0">{{ $record->branch->name }}</p>
          </div>
          <div class="mb-2">
            <small class="text-muted">Record ID</small>
            <p class="mb-0"><code>{{ $record->id }}</code></p>
          </div>
          <div class="mb-2">
            <small class="text-muted">Last Updated</small>
            <p class="mb-0">{{ $record->updated_at->format('d/m/Y H:i') }}</p>
          </div>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="card">
        <div class="card-body">
          <button type="submit" class="btn btn-primary w-100 mb-2">
            <i class='bx bx-save me-1'></i> Update Record
          </button>
          <a href="{{ route('training.records.show', $record) }}" class="btn btn-outline-secondary w-100 mb-2">
            Cancel
          </a>

          <hr>

          <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteModal">
            <i class='bx bx-trash me-1'></i> Delete Record
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
        <h5 class="modal-title">Delete Training Record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('training.records.destroy', $record) }}" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <p>Are you sure you want to delete this training record?</p>
          <div class="alert alert-danger mb-0">
            <i class='bx bx-error-circle me-2'></i>
            <strong>Warning:</strong> This action cannot be undone. The record will be soft-deleted and can be restored by administrators if needed.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class='bx bx-trash me-1'></i> Delete Record
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
  // Toggle renewal fields
  $('#requires_renewal').on('change', function() {
    if ($(this).is(':checked')) {
      $('#renewalFields').slideDown();
    } else {
      $('#renewalFields').slideUp();
    }
  });

  // Auto-determine pass/fail based on score
  $('#assessment_score').on('change', function() {
    const score = parseInt($(this).val());
    if (score >= 80) {
      $('#assessment_passed').val('1');
    } else if (score < 80 && score > 0) {
      $('#assessment_passed').val('0');
    }
  });

  // Form validation
  $('#trainingRecordForm').on('submit', function(e) {
    const score = $('#assessment_score').val();
    const passed = $('#assessment_passed').val();

    if (score && !passed) {
      e.preventDefault();
      alert('Please select assessment result (Passed/Failed) when entering a score.');
      return false;
    }
  });
});
</script>
@endsection
