@extends('layouts/layoutMaster')

@section('title', 'Add New Contractor')

@section('content')
<h4 class="mb-1">Add New Contractor</h4>
<p class="mb-4">Register a new contractor for site access and induction tracking</p>

<form action="{{ route('contractors.store') }}" method="POST" id="contractorForm">
  @csrf

  <div class="row">
    <!-- Personal Information Card -->
    <div class="col-12 col-lg-8">
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
                <option value="{{ $company->id }}" {{ old('contractor_company_id') == $company->id ? 'selected' : '' }}>
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
                     value="{{ old('first_name') }}" required
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
                     value="{{ old('last_name') }}" required
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
                     value="{{ old('email') }}" required
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
                     value="{{ old('phone') }}" required
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
                     value="{{ old('date_of_birth') }}"
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
                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
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
                     value="{{ old('driver_license_number') }}"
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
                     value="{{ old('driver_license_expiry') }}"
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
                     value="{{ old('emergency_contact_name') }}"
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
                     value="{{ old('emergency_contact_phone') }}"
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
                        rows="4" placeholder="Enter any additional notes or comments...">{{ old('notes') }}</textarea>
              @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sidebar: Induction & Access -->
    <div class="col-12 col-lg-4">
      <!-- Induction Information Card -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Induction Status</h5>
        </div>
        <div class="card-body">
          <div class="alert alert-info">
            <i class="bx bx-info-circle me-2"></i>
            <small>Induction can be completed after contractor registration using the "Complete Induction" action.</small>
          </div>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="induction_completed"
                   name="induction_completed" value="1"
                   {{ old('induction_completed') ? 'checked' : '' }}
                   onchange="toggleInductionFields()">
            <label class="form-check-label" for="induction_completed">
              Mark as already inducted
            </label>
          </div>

          <div id="inductionFields" style="display: {{ old('induction_completed') ? 'block' : 'none' }};">
            <div class="mb-3">
              <label for="induction_completion_date" class="form-label">Completion Date</label>
              <input type="date" id="induction_completion_date" name="induction_completion_date"
                     class="form-control @error('induction_completion_date') is-invalid @enderror"
                     value="{{ old('induction_completion_date') }}"
                     max="{{ date('Y-m-d') }}">
              @error('induction_completion_date')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="induction_expiry_date" class="form-label">Expiry Date</label>
              <input type="date" id="induction_expiry_date" name="induction_expiry_date"
                     class="form-control @error('induction_expiry_date') is-invalid @enderror"
                     value="{{ old('induction_expiry_date', date('Y-m-d', strtotime('+12 months'))) }}"
                     min="{{ date('Y-m-d') }}">
              @error('induction_expiry_date')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="form-text">Standard validity: 12 months</div>
            </div>

            <div class="mb-3">
              <label for="inducted_by" class="form-label">Inducted By</label>
              <select id="inducted_by" name="inducted_by" class="form-select @error('inducted_by') is-invalid @enderror">
                <option value="">Select user</option>
                @foreach(\App\Models\User::where('branch_id', auth()->user()->branch_id)->get() as $user)
                <option value="{{ $user->id }}" {{ old('inducted_by') == $user->id ? 'selected' : '' }}>
                  {{ $user->name }}
                </option>
                @endforeach
              </select>
              @error('inducted_by')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- Site Access Card -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Site Access</h5>
        </div>
        <div class="card-body">
          <div class="alert alert-warning">
            <i class="bx bx-shield-alt me-2"></i>
            <small>Site access can only be granted after a valid induction is completed.</small>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="site_access_granted"
                   name="site_access_granted" value="1"
                   {{ old('site_access_granted') ? 'checked' : '' }}>
            <label class="form-check-label" for="site_access_granted">
              Grant site access
            </label>
          </div>
        </div>
      </div>

      <!-- Action Buttons Card -->
      <div class="card">
        <div class="card-body">
          <button type="submit" class="btn btn-primary w-100 mb-2">
            <i class="bx bx-check-circle me-1"></i> Create Contractor
          </button>
          <a href="{{ route('contractors.index') }}" class="btn btn-outline-secondary w-100">
            <i class="bx bx-x me-1"></i> Cancel
          </a>
        </div>
      </div>
    </div>
  </div>
</form>

@endsection

@section('page-script')
<script>
// Toggle induction fields based on checkbox
function toggleInductionFields() {
  const checkbox = document.getElementById('induction_completed');
  const fields = document.getElementById('inductionFields');

  if (checkbox.checked) {
    fields.style.display = 'block';
    // Set default expiry date to 12 months from now
    const completionInput = document.getElementById('induction_completion_date');
    const expiryInput = document.getElementById('induction_expiry_date');

    if (!completionInput.value) {
      completionInput.value = '{{ date('Y-m-d') }}';
    }

    if (!expiryInput.value) {
      expiryInput.value = '{{ date('Y-m-d', strtotime('+12 months')) }}';
    }
  } else {
    fields.style.display = 'none';
  }
}

// Validate expiry date is after completion date
document.getElementById('contractorForm').addEventListener('submit', function(e) {
  const inductionCompleted = document.getElementById('induction_completed').checked;

  if (inductionCompleted) {
    const completionDate = new Date(document.getElementById('induction_completion_date').value);
    const expiryDate = new Date(document.getElementById('induction_expiry_date').value);

    if (expiryDate <= completionDate) {
      e.preventDefault();
      alert('Induction expiry date must be after completion date');
      return false;
    }
  }
});

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
