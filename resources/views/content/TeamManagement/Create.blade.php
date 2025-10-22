@extends('layouts/layoutMaster')

@section('title', 'Add Team Member - Team Management')

@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}">
@endsection

@section('content')
<div class="row">
  <!-- Main Form Column -->
  <div class="col-xl-9 col-lg-8 col-md-8">
    <form action="{{ route('team.store') }}" method="POST" id="createMemberForm">
      @csrf

      <!-- Personal Information -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Personal Information</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="name" class="form-label">Full Name *</label>
              <input type="text" id="name" name="name"
                     class="form-control @error('name') is-invalid @enderror"
                     value="{{ old('name') }}"
                     placeholder="e.g., John Smith" required>
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="employee_id" class="form-label">Employee ID *</label>
              <input type="text" id="employee_id" name="employee_id"
                     class="form-control @error('employee_id') is-invalid @enderror"
                     value="{{ old('employee_id') }}"
                     placeholder="e.g., EMP-001" required>
              @error('employee_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Unique employee identifier</small>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="email" class="form-label">Email Address *</label>
              <input type="email" id="email" name="email"
                     class="form-control @error('email') is-invalid @enderror"
                     value="{{ old('email') }}"
                     placeholder="john.smith@company.com" required>
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="phone" class="form-label">Phone Number *</label>
              <input type="tel" id="phone" name="phone"
                     class="form-control @error('phone') is-invalid @enderror"
                     value="{{ old('phone') }}"
                     placeholder="0412 345 678" required>
              @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- Employment Details -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Employment Details</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="position" class="form-label">Position/Job Title *</label>
              <input type="text" id="position" name="position"
                     class="form-control @error('position') is-invalid @enderror"
                     value="{{ old('position') }}"
                     placeholder="e.g., Safety Officer" required>
              @error('position')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="branch_id" class="form-label">Branch *</label>
              <select id="branch_id" name="branch_id" class="form-select select2 @error('branch_id') is-invalid @enderror" required>
                <option value="">Select branch</option>
                @foreach(\App\Models\Branch::active()->orderBy('name')->get() as $branch)
                  <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                    {{ $branch->name }}
                  </option>
                @endforeach
              </select>
              @error('branch_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="role" class="form-label">User Role *</label>
              <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                <option value="">Select role</option>
                <option value="employee" {{ old('role') == 'employee' ? 'selected' : '' }}>Employee</option>
                <option value="supervisor" {{ old('role') == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
              </select>
              @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="is_active" class="form-label">Account Status *</label>
              <select id="is_active" name="is_active" class="form-select @error('is_active') is-invalid @enderror" required>
                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
              </select>
              @error('is_active')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Inactive users cannot log in</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Login Credentials -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Login Credentials</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="password" class="form-label">Password *</label>
              <input type="password" id="password" name="password"
                     class="form-control @error('password') is-invalid @enderror"
                     placeholder="Minimum 8 characters" required>
              @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Minimum 8 characters, include uppercase, lowercase, and numbers</small>
            </div>

            <div class="col-md-6 mb-3">
              <label for="password_confirmation" class="form-label">Confirm Password *</label>
              <input type="password" id="password_confirmation" name="password_confirmation"
                     class="form-control"
                     placeholder="Re-enter password" required>
            </div>
          </div>

          <div class="alert alert-info mb-0">
            <i class='bx bx-info-circle me-2'></i>
            <strong>Password Requirements:</strong>
            <ul class="mb-0 mt-2">
              <li>At least 8 characters long</li>
              <li>Include at least one uppercase letter</li>
              <li>Include at least one lowercase letter</li>
              <li>Include at least one number</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Additional Notes -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Additional Information</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-12 mb-3">
              <label for="notes" class="form-label">Notes</label>
              <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror"
                        rows="3"
                        placeholder="Any additional information about this team member...">{{ old('notes') }}</textarea>
              @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="send_welcome_email" name="send_welcome_email" value="1" checked>
            <label class="form-check-label" for="send_welcome_email">
              Send welcome email with login credentials
            </label>
          </div>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('team.index') }}" class="btn btn-outline-secondary">
              <i class='bx bx-x me-1'></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
              <i class='bx bx-user-plus me-1'></i> Add Team Member
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Sidebar Column -->
  <div class="col-xl-3 col-lg-4 col-md-4">
    <!-- Quick Guide -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">Adding Team Members</h6>
      </div>
      <div class="card-body">
        <p class="mb-2"><i class='bx bx-check-circle text-success me-2'></i> <strong>Required Fields:</strong></p>
        <ul class="mb-3">
          <li>Full name and employee ID</li>
          <li>Email and phone number</li>
          <li>Position and branch</li>
          <li>User role and account status</li>
          <li>Password (minimum 8 characters)</li>
        </ul>

        <p class="mb-2"><i class='bx bx-info-circle text-info me-2'></i> <strong>User Roles:</strong></p>
        <ul class="mb-0">
          <li><strong>Employee:</strong> Basic access</li>
          <li><strong>Supervisor:</strong> Team oversight</li>
          <li><strong>Manager:</strong> Department management</li>
          <li><strong>Admin:</strong> Full system access</li>
        </ul>
      </div>
    </div>

    <!-- Account Status Guide -->
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Account Status</h6>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <span class="badge bg-success me-1">Active</span>
          <p class="mb-0 mt-2"><small>User can log in and access the system</small></p>
        </div>

        <div class="mb-0">
          <span class="badge bg-secondary me-1">Inactive</span>
          <p class="mb-0 mt-2"><small>User cannot log in (for terminated employees or on leave)</small></p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script>
$(document).ready(function() {
  'use strict';

  // Initialize Select2 for branch selection
  if ($('#branch_id').length) {
    $('#branch_id').select2({
      placeholder: 'Select branch',
      allowClear: false
    });
  }

  // Password strength indicator
  $('#password').on('input', function() {
    const password = $(this).val();
    let strength = 0;

    // Check length
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;

    // Check for uppercase
    if (/[A-Z]/.test(password)) strength++;

    // Check for lowercase
    if (/[a-z]/.test(password)) strength++;

    // Check for numbers
    if (/[0-9]/.test(password)) strength++;

    // Check for special characters
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    // Update visual indicator (you can add a progress bar here)
    // For now, just validate the minimum requirements
  });

  // Form validation
  $('#createMemberForm').on('submit', function(e) {
    const password = $('#password').val();
    const confirmPassword = $('#password_confirmation').val();

    // Check password match
    if (password !== confirmPassword) {
      e.preventDefault();
      alert('Passwords do not match. Please check and try again.');
      $('#password_confirmation').focus();
      return false;
    }

    // Check minimum password requirements
    if (password.length < 8) {
      e.preventDefault();
      alert('Password must be at least 8 characters long.');
      $('#password').focus();
      return false;
    }

    if (!/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/[0-9]/.test(password)) {
      e.preventDefault();
      alert('Password must include uppercase letters, lowercase letters, and numbers.');
      $('#password').focus();
      return false;
    }
  });

  // Auto-format phone number (Australian format)
  $('#phone').on('input', function() {
    let value = $(this).val().replace(/\D/g, '');

    if (value.length > 0) {
      if (value.length <= 4) {
        $(this).val(value);
      } else if (value.length <= 7) {
        $(this).val(value.slice(0, 4) + ' ' + value.slice(4));
      } else {
        $(this).val(value.slice(0, 4) + ' ' + value.slice(4, 7) + ' ' + value.slice(7, 10));
      }
    }
  });

  // Role-based hints
  $('#role').on('change', function() {
    const role = $(this).val();

    if (role === 'admin') {
      if (!confirm('Admin role grants full system access including user management and system settings. Continue?')) {
        $(this).val('');
      }
    }
  });
});
</script>
@endsection
