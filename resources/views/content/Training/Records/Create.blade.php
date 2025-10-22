@extends('layouts/layoutMaster')

@section('title', 'Create Training Record')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-1">Create Training Record</h4>
    <p class="mb-0">Assign training or record certification for an employee</p>
  </div>
  <div>
    <a href="{{ route('training.records.index') }}" class="btn btn-outline-secondary">
      <i class='bx bx-arrow-back me-1'></i> Back to Records
    </a>
  </div>
</div>

<form action="{{ route('training.records.store') }}" method="POST" id="trainingRecordForm" enctype="multipart/form-data">
  @csrf

  <div class="row">
    <!-- Main Form Card -->
    <div class="col-md-8">
      <!-- Employee Selection -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Employee Information</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="user_id" class="form-label">Select Employee *</label>
            <select id="user_id" name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
              <option value="">Choose employee</option>
              @foreach(\App\Models\User::where('branch_id', auth()->user()->branch_id)->orderBy('name')->get() as $user)
              <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                {{ $user->name }} - {{ $user->email }}
              </option>
              @endforeach
            </select>
            @error('user_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      <!-- Training Details -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Training Details</h5>
        </div>
        <div class="card-body">
          <!-- Training Type -->
          <div class="mb-3">
            <label for="training_type" class="form-label">Training Type *</label>
            <select id="training_type" name="training_type" class="form-select @error('training_type') is-invalid @enderror" required>
              <option value="">Select training type</option>
              <option value="driver_license" {{ old('training_type') == 'driver_license' ? 'selected' : '' }}>Driver License</option>
              <option value="forklift_license" {{ old('training_type') == 'forklift_license' ? 'selected' : '' }}>Forklift License</option>
              <option value="first_aid" {{ old('training_type') == 'first_aid' ? 'selected' : '' }}>First Aid</option>
              <option value="working_at_heights" {{ old('training_type') == 'working_at_heights' ? 'selected' : '' }}>Working at Heights</option>
              <option value="confined_spaces" {{ old('training_type') == 'confined_spaces' ? 'selected' : '' }}>Confined Spaces</option>
              <option value="manual_handling" {{ old('training_type') == 'manual_handling' ? 'selected' : '' }}>Manual Handling</option>
              <option value="fire_safety" {{ old('training_type') == 'fire_safety' ? 'selected' : '' }}>Fire Safety</option>
              <option value="electrical_safety" {{ old('training_type') == 'electrical_safety' ? 'selected' : '' }}>Electrical Safety</option>
              <option value="custom" {{ old('training_type') == 'custom' ? 'selected' : '' }}>Custom Training</option>
            </select>
            @error('training_type')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="certification_number" class="form-label">Certification Number</label>
              <input type="text"
                     id="certification_number"
                     name="certification_number"
                     class="form-control @error('certification_number') is-invalid @enderror"
                     placeholder="e.g., DL123456"
                     value="{{ old('certification_number') }}">
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
                     placeholder="e.g., VicRoads, WorkSafe Victoria"
                     value="{{ old('issuing_authority') }}"
                     required>
              @error('issuing_authority')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="issue_date" class="form-label">Issue Date *</label>
              <input type="date"
                     id="issue_date"
                     name="issue_date"
                     class="form-control @error('issue_date') is-invalid @enderror"
                     value="{{ old('issue_date', now()->format('Y-m-d')) }}"
                     required>
              @error('issue_date')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="expiry_date" class="form-label">Expiry Date</label>
              <input type="date"
                     id="expiry_date"
                     name="expiry_date"
                     class="form-control @error('expiry_date') is-invalid @enderror"
                     value="{{ old('expiry_date') }}">
              @error('expiry_date')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="form-text text-muted">Leave blank if certification doesn't expire</small>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="training_provider" class="form-label">Training Provider</label>
              <input type="text"
                     id="training_provider"
                     name="training_provider"
                     class="form-control @error('training_provider') is-invalid @enderror"
                     placeholder="e.g., TAFE, SafetyFirst Training"
                     value="{{ old('training_provider') }}">
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
                     placeholder="e.g., 8"
                     value="{{ old('training_duration_hours') }}"
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
                     placeholder="e.g., 350.00"
                     value="{{ old('cost') }}">
              @error('cost')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="proficiency_level" class="form-label">Proficiency Level *</label>
              <select id="proficiency_level" name="proficiency_level" class="form-select @error('proficiency_level') is-invalid @enderror" required>
                <option value="basic" {{ old('proficiency_level', 'basic') == 'basic' ? 'selected' : '' }}>Basic</option>
                <option value="intermediate" {{ old('proficiency_level') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                <option value="advanced" {{ old('proficiency_level') == 'advanced' ? 'selected' : '' }}>Advanced</option>
                <option value="expert" {{ old('proficiency_level') == 'expert' ? 'selected' : '' }}>Expert</option>
              </select>
              @error('proficiency_level')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Renewal Settings -->
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="requires_renewal" name="requires_renewal" value="1" {{ old('requires_renewal') ? 'checked' : '' }}>
              <label class="form-check-label" for="requires_renewal">
                Requires Renewal
              </label>
            </div>
          </div>

          <div id="renewalFields" style="display: {{ old('requires_renewal') ? 'block' : 'none' }};">
            <div class="mb-3">
              <label for="renewal_interval_months" class="form-label">Renewal Interval (Months)</label>
              <input type="number"
                     id="renewal_interval_months"
                     name="renewal_interval_months"
                     class="form-control @error('renewal_interval_months') is-invalid @enderror"
                     min="1"
                     placeholder="e.g., 12, 24, 36"
                     value="{{ old('renewal_interval_months') }}">
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
          <h5 class="mb-0">Assessment Details (Optional)</h5>
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
                     placeholder="e.g., 85"
                     value="{{ old('assessment_score') }}">
              @error('assessment_score')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="assessment_passed" class="form-label">Assessment Result</label>
              <select id="assessment_passed" name="assessment_passed" class="form-select @error('assessment_passed') is-invalid @enderror">
                <option value="">Not assessed</option>
                <option value="1" {{ old('assessment_passed') == '1' ? 'selected' : '' }}>Passed</option>
                <option value="0" {{ old('assessment_passed') == '0' ? 'selected' : '' }}>Failed</option>
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
                   placeholder="Name of trainer/assessor"
                   value="{{ old('trainer_name') }}">
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
                      class="form-control @error('notes') is-invalid @enderror"
                      placeholder="Additional notes about the training...">{{ old('notes') }}</textarea>
            @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label for="certificate_upload" class="form-label">Upload Certificate (Optional)</label>
            <input type="file"
                   id="certificate_upload"
                   name="certificate_upload"
                   class="form-control @error('certificate_upload') is-invalid @enderror"
                   accept=".pdf,.jpg,.jpeg,.png">
            @error('certificate_upload')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Max file size: 10MB. Accepted formats: PDF, JPG, PNG</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
      <!-- Quick Info -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Record Information</h5>
        </div>
        <div class="card-body">
          <div class="mb-2">
            <small class="text-muted">Branch</small>
            <p class="mb-0">{{ auth()->user()->branch->name }}</p>
          </div>
          <div class="mb-2">
            <small class="text-muted">Status</small>
            <p class="mb-0"><span class="badge bg-success">Active</span></p>
          </div>
          <div class="mb-2">
            <small class="text-muted">Recorded By</small>
            <p class="mb-0">{{ auth()->user()->name }}</p>
          </div>
        </div>
      </div>

      <!-- Expiry Alert Settings -->
      <div class="card mb-4 border-warning">
        <div class="card-body">
          <div class="d-flex align-items-start">
            <i class='bx bx-bell text-warning me-2 fs-4'></i>
            <div>
              <h6 class="mb-1">Automated Reminders</h6>
              <small class="text-muted">If expiry date is set, automatic reminder notifications will be sent at 90, 60, 30, and 7 days before expiration.</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Critical Certifications -->
      <div class="card mb-4 border-danger">
        <div class="card-body">
          <div class="d-flex align-items-start">
            <i class='bx bx-error-circle text-danger me-2 fs-4'></i>
            <div>
              <h6 class="mb-1">Critical Certifications</h6>
              <small class="text-muted">Driver licenses and forklift licenses are critical. Expired critical certifications will automatically suspend employee access to related equipment.</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="card">
        <div class="card-body">
          <button type="submit" class="btn btn-primary w-100 mb-2">
            <i class='bx bx-save me-1'></i> Create Training Record
          </button>
          <a href="{{ route('training.records.index') }}" class="btn btn-outline-secondary w-100">
            Cancel
          </a>
        </div>
      </div>
    </div>
  </div>
</form>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
  // Toggle renewal fields
  $('#requires_renewal').on('change', function() {
    if ($(this).is(':checked')) {
      $('#renewalFields').slideDown();
      $('#renewal_interval_months').prop('required', true);
    } else {
      $('#renewalFields').slideUp();
      $('#renewal_interval_months').prop('required', false);
    }
  });

  // Auto-calculate expiry date based on renewal interval
  $('#issue_date, #renewal_interval_months').on('change', function() {
    const issueDate = $('#issue_date').val();
    const renewalMonths = parseInt($('#renewal_interval_months').val());

    if (issueDate && renewalMonths && $('#requires_renewal').is(':checked')) {
      const date = new Date(issueDate);
      date.setMonth(date.getMonth() + renewalMonths);
      $('#expiry_date').val(date.toISOString().split('T')[0]);
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
    const userId = $('#user_id').val();
    const trainingType = $('#training_type').val();

    if (!userId || !trainingType) {
      e.preventDefault();
      alert('Please select an employee and training type.');
      return false;
    }

    // Validate assessment score logic
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
