@extends('layouts/layoutMaster')

@section('title', 'Edit Contractor - ' . $contractor->first_name . ' ' . $contractor->last_name)

@section('content')
<h4 class="mb-1">Edit Contractor</h4>
<p class="mb-4">Update contractor information</p>

<form action="{{ route('contractors.update', $contractor) }}" method="POST" id="contractorForm">
  @csrf
  @method('PUT')

  <div class="row">
    <!-- Main Content -->
    <div class="col-12 col-lg-8">
      <!-- Personal Information Card -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Personal Information</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <!-- Company -->
            <div class="col-md-12">
              <label for="contractor_company_id" class="form-label">Contractor Company *</label>
              <select id="contractor_company_id" name="contractor_company_id"
                      class="form-select @error('contractor_company_id') is-invalid @enderror" required>
                <option value="">Select contractor company</option>
                @foreach($companies as $company)
                <option value="{{ $company->id }}"
                        {{ old('contractor_company_id', $contractor->contractor_company_id) == $company->id ? 'selected' : '' }}>
                  {{ $company->name }}
                </option>
                @endforeach
              </select>
              @error('contractor_company_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="form-text">Select the company this contractor works for</div>
            </div>

            <!-- First Name -->
            <div class="col-md-6">
              <label for="first_name" class="form-label">First Name *</label>
              <input type="text" id="first_name" name="first_name"
                     class="form-control @error('first_name') is-invalid @enderror"
                     value="{{ old('first_name', $contractor->first_name) }}" required
                     placeholder="Enter first name">
              @error('first_name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Last Name -->
            <div class="col-md-6">
              <label for="last_name" class="form-label">Last Name *</label>
              <input type="text" id="last_name" name="last_name"
                     class="form-control @error('last_name') is-invalid @enderror"
                     value="{{ old('last_name', $contractor->last_name) }}" required
                     placeholder="Enter last name">
              @error('last_name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Email -->
            <div class="col-md-6">
              <label for="email" class="form-label">Email Address *</label>
              <input type="email" id="email" name="email"
                     class="form-control @error('email') is-invalid @enderror"
                     value="{{ old('email', $contractor->email) }}" required
                     placeholder="contractor@example.com">
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Phone -->
            <div class="col-md-6">
              <label for="phone" class="form-label">Phone Number *</label>
              <input type="tel" id="phone" name="phone"
                     class="form-control @error('phone') is-invalid @enderror"
                     value="{{ old('phone', $contractor->phone) }}" required
                     placeholder="+61 4XX XXX XXX">
              @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Date of Birth -->
            <div class="col-md-6">
              <label for="date_of_birth" class="form-label">Date of Birth</label>
              <input type="date" id="date_of_birth" name="date_of_birth"
                     class="form-control @error('date_of_birth') is-invalid @enderror"
                     value="{{ old('date_of_birth', $contractor->date_of_birth?->format('Y-m-d')) }}"
                     max="{{ date('Y-m-d', strtotime('-18 years')) }}">
              @error('date_of_birth')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="form-text">Must be at least 18 years old</div>
            </div>

            <!-- Status -->
            <div class="col-md-6">
              <label for="status" class="form-label">Status</label>
              <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                <option value="active" {{ old('status', $contractor->status) === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status', $contractor->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="suspended" {{ old('status', $contractor->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
              </select>
              @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- Driver License Information Card -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Driver License Information</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <!-- License Number -->
            <div class="col-md-6">
              <label for="driver_license_number" class="form-label">License Number</label>
              <input type="text" id="driver_license_number" name="driver_license_number"
                     class="form-control @error('driver_license_number') is-invalid @enderror"
                     value="{{ old('driver_license_number', $contractor->driver_license_number) }}"
                     placeholder="e.g., 123456789">
              @error('driver_license_number')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- License Expiry -->
            <div class="col-md-6">
              <label for="driver_license_expiry" class="form-label">License Expiry Date</label>
              <input type="date" id="driver_license_expiry" name="driver_license_expiry"
                     class="form-control @error('driver_license_expiry') is-invalid @enderror"
                     value="{{ old('driver_license_expiry', $contractor->driver_license_expiry?->format('Y-m-d')) }}"
                     min="{{ date('Y-m-d') }}">
              @error('driver_license_expiry')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="form-text">License must not be expired</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Emergency Contact Card -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Emergency Contact</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <!-- Emergency Contact Name -->
            <div class="col-md-6">
              <label for="emergency_contact_name" class="form-label">Contact Name</label>
              <input type="text" id="emergency_contact_name" name="emergency_contact_name"
                     class="form-control @error('emergency_contact_name') is-invalid @enderror"
                     value="{{ old('emergency_contact_name', $contractor->emergency_contact_name) }}"
                     placeholder="Enter emergency contact name">
              @error('emergency_contact_name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Emergency Contact Phone -->
            <div class="col-md-6">
              <label for="emergency_contact_phone" class="form-label">Contact Phone</label>
              <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone"
                     class="form-control @error('emergency_contact_phone') is-invalid @enderror"
                     value="{{ old('emergency_contact_phone', $contractor->emergency_contact_phone) }}"
                     placeholder="+61 4XX XXX XXX">
              @error('emergency_contact_phone')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- Additional Notes Card -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Additional Notes</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-12">
              <label for="notes" class="form-label">Notes</label>
              <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror"
                        rows="4" placeholder="Enter any additional notes or comments...">{{ old('notes', $contractor->notes) }}</textarea>
              @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="col-12 col-lg-4">
      <!-- Current Status Card -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Current Status</h5>
        </div>
        <div class="card-body">
          <ul class="list-unstyled mb-0">
            <li class="d-flex justify-content-between align-items-center mb-3">
              <span>Induction</span>
              @if($contractor->hasValidInduction())
                <span class="badge bg-success">Valid</span>
              @elseif($contractor->induction_completed)
                <span class="badge bg-danger">Expired</span>
              @else
                <span class="badge bg-warning">Pending</span>
              @endif
            </li>
            <li class="d-flex justify-content-between align-items-center mb-3">
              <span>Site Access</span>
              @if($contractor->site_access_granted)
                <span class="badge bg-success">Granted</span>
              @else
                <span class="badge bg-secondary">No Access</span>
              @endif
            </li>
            <li class="d-flex justify-content-between align-items-center">
              <span>On-Site</span>
              @if($contractor->isSignedIn())
                <span class="badge bg-warning">Yes</span>
              @else
                <span class="badge bg-secondary">No</span>
              @endif
            </li>
          </ul>
        </div>
      </div>

      <!-- Induction Information Card (Read-Only) -->
      @if($contractor->induction_completed)
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Induction Information</h5>
        </div>
        <div class="card-body">
          <div class="alert alert-info">
            <i class="bx bx-info-circle me-2"></i>
            <small>Induction status cannot be changed through edit. Use the "Complete Induction" action instead.</small>
          </div>

          <ul class="list-unstyled mb-0">
            <li class="mb-2">
              <small class="text-muted d-block">Completed On</small>
              <strong>{{ $contractor->induction_completion_date->format('d/m/Y') }}</strong>
            </li>
            <li class="mb-2">
              <small class="text-muted d-block">Expires On</small>
              <strong>{{ $contractor->induction_expiry_date->format('d/m/Y') }}</strong>
            </li>
            @if($contractor->inductor)
            <li>
              <small class="text-muted d-block">Inducted By</small>
              <strong>{{ $contractor->inductor->name }}</strong>
            </li>
            @endif
          </ul>
        </div>
      </div>
      @endif

      <!-- Action Buttons Card -->
      <div class="card">
        <div class="card-body">
          <button type="submit" class="btn btn-primary w-100 mb-2">
            <i class="bx bx-check-circle me-1"></i> Update Contractor
          </button>
          <a href="{{ route('contractors.show', $contractor) }}" class="btn btn-outline-secondary w-100 mb-2">
            <i class="bx bx-arrow-back me-1"></i> Back to Details
          </a>
          <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteModal">
            <i class="bx bx-trash me-1"></i> Delete Contractor
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
        <h5 class="modal-title">Delete Contractor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('contractors.destroy', $contractor) }}" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <p>Are you sure you want to delete <strong>{{ $contractor->first_name }} {{ $contractor->last_name }}</strong>?</p>
          <div class="alert alert-danger mb-0">
            <i class="bx bx-error-circle me-2"></i>
            This action cannot be undone. All contractor records will be permanently removed.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="bx bx-trash me-1"></i> Delete Contractor
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@section('page-script')
<script>
// Validate driver license expiry is not in the past
document.getElementById('driver_license_expiry').addEventListener('change', function() {
  const selectedDate = new Date(this.value);
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  if (selectedDate < today) {
    this.setCustomValidity('Driver license must not be expired');
  } else {
    this.setCustomValidity('');
  }
});

// Validate date of birth (must be 18+ years old)
document.getElementById('date_of_birth').addEventListener('change', function() {
  if (this.value) {
    const dob = new Date(this.value);
    const today = new Date();
    const age = Math.floor((today - dob) / (365.25 * 24 * 60 * 60 * 1000));

    if (age < 18) {
      this.setCustomValidity('Contractor must be at least 18 years old');
    } else {
      this.setCustomValidity('');
    }
  }
});
</script>
@endsection
