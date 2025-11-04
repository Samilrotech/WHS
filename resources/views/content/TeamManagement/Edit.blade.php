@extends('layouts/layoutMaster')

@section('title', 'Edit Team Member - Team Management')

@section('content')
<div class="row">
  <!-- Main Form Column -->
  <div class="col-xl-9 col-lg-8 col-md-8">
    <!-- Read-Only Information Alert -->
    <div class="alert alert-info mb-4">
      <div class="d-flex align-items-center">
        <i class='bx bx-info-circle bx-md me-3'></i>
        <div>
          <strong>Note:</strong> Employee ID cannot be changed once created. Contact an administrator if this needs to be updated.
        </div>
      </div>
    </div>

    <form action="{{ route('teams.update', $member['id'] ?? '1') }}" method="POST" id="editMemberForm">
      @csrf
      @method('PUT')

      <!-- Current Status -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Current Status</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-3 mb-3">
              <label class="form-label text-muted">Employee ID</label>
              <h5 class="mb-0">{{ $member['employee_id'] ?? 'EMP-001' }}</h5>
            </div>

            <div class="col-md-3 mb-3">
              <label class="form-label text-muted">Account Status</label>
              <h5 class="mb-0">
                @php
                  $isActive = $member['is_active'] ?? true;
                @endphp
                <span class="badge bg-{{ $isActive ? 'success' : 'secondary' }}">
                  {{ $isActive ? 'Active' : 'Inactive' }}
                </span>
              </h5>
            </div>

            <div class="col-md-3 mb-3">
              <label class="form-label text-muted">Role</label>
              <h5 class="mb-0">
                @php
                  $role = $member['role'] ?? 'employee';
                  $roleIcons = [
                    'employee' => 'bx-user',
                    'supervisor' => 'bx-user-check',
                    'manager' => 'bx-crown',
                    'admin' => 'bx-shield',
                  ];
                  $icon = $roleIcons[$role] ?? 'bx-user';
                @endphp
                <i class='bx {{ $icon }} me-1'></i>
                {{ ucfirst($role) }}
              </h5>
            </div>

            <div class="col-md-3 mb-3">
              <label class="form-label text-muted">Last Updated</label>
              <h6 class="mb-0">{{ $member['updated_at'] ?? now()->format('d/m/Y H:i') }}</h6>
            </div>
          </div>
        </div>
      </div>

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
                     value="{{ old('name', $member['name'] ?? 'John Smith') }}"
                     placeholder="e.g., John Smith" required>
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="email" class="form-label">Email Address *</label>
              <input type="email" id="email" name="email"
                     class="form-control @error('email') is-invalid @enderror"
                     value="{{ old('email', $member['email'] ?? 'john.smith@company.com') }}"
                     placeholder="john.smith@company.com" required>
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="phone" class="form-label">Phone Number *</label>
              <input type="tel" id="phone" name="phone"
                     class="form-control @error('phone') is-invalid @enderror"
                     value="{{ old('phone', $member['phone'] ?? '0412 345 678') }}"
                     placeholder="0412 345 678" required>
              @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="position" class="form-label">Position/Job Title *</label>
              <input type="text" id="position" name="position"
                     class="form-control @error('position') is-invalid @enderror"
                     value="{{ old('position', $member['position'] ?? 'Safety Officer') }}"
                     placeholder="e.g., Safety Officer" required>
              @error('position')
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
              <label for="branch_id" class="form-label">Branch *</label>
              <select id="branch_id" name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                <option value="">Select branch</option>
                @php
                  $currentBranchId = old('branch_id', $member['branch_id'] ?? null);
                @endphp
                @foreach($branches as $branch)
                  <option value="{{ $branch->id }}" {{ $currentBranchId == $branch->id ? 'selected' : '' }}>
                    {{ $branch->name }}
                  </option>
                @endforeach
              </select>
              @error('branch_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Changing branch will affect data visibility and permissions</small>
            </div>

            <div class="col-md-6 mb-3">
              <label for="role" class="form-label">User Role *</label>
              <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                <option value="">Select role</option>
                @php
                  $currentRole = old('role', $member['role'] ?? 'employee');
                @endphp
                @foreach($roles as $role)
                  <option value="{{ $role['key'] }}" {{ $currentRole == $role['key'] ? 'selected' : '' }}>
                    {{ $role['label'] }}
                  </option>
                @endforeach
              </select>
              @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Changes to role take effect immediately upon saving</small>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <label for="is_active" class="form-label">Account Status *</label>
              <select id="is_active" name="is_active" class="form-select @error('is_active') is-invalid @enderror" required>
                @php
                  $currentStatus = old('is_active', $member['is_active'] ?? 1);
                @endphp
                <option value="1" {{ $currentStatus == 1 ? 'selected' : '' }}>Active (Can log in)</option>
                <option value="0" {{ $currentStatus == 0 ? 'selected' : '' }}>Inactive (Cannot log in)</option>
              </select>
              @error('is_active')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="text-muted">Inactive users cannot log in to the system. Use for terminated employees or long-term leave.</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Reset Password (Optional) -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Reset Password (Optional)</h5>
        </div>
        <div class="card-body">
          <div class="alert alert-warning mb-3">
            <i class='bx bx-info-circle me-2'></i>
            <strong>Leave blank to keep current password.</strong> Only fill in these fields if you want to reset the password.
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="password" class="form-label">New Password</label>
              <input type="password" id="password" name="password"
                     class="form-control @error('password') is-invalid @enderror"
                     placeholder="Minimum 8 characters">
              @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="password_confirmation" class="form-label">Confirm New Password</label>
              <input type="password" id="password_confirmation" name="password_confirmation"
                     class="form-control"
                     placeholder="Re-enter new password">
            </div>
          </div>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="send_password_email" name="send_password_email" value="1">
            <label class="form-check-label" for="send_password_email">
              Send password reset email to user
            </label>
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
                        placeholder="Any additional information about this team member...">{{ old('notes', $member['notes'] ?? '') }}</textarea>
              @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('teams.show', $member['id'] ?? '1') }}" class="btn btn-outline-secondary">
              <i class='bx bx-x me-1'></i> Cancel
            </a>
            <div>
              <button type="button" class="btn btn-outline-danger me-2" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class='bx bx-trash me-1'></i> Delete Member
              </button>
              <button type="submit" class="btn btn-primary">
                <i class='bx bx-save me-1'></i> Update Team Member
              </button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Sidebar Column -->
  <div class="col-xl-3 col-lg-4 col-md-4">
    <!-- Member Summary -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">Member Summary</h6>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label text-muted mb-1">Created</label>
          <p class="mb-0">{{ $member['created_at'] ?? now()->format('d/m/Y H:i') }}</p>
        </div>

        <div class="mb-3">
          <label class="form-label text-muted mb-1">Last Login</label>
          <p class="mb-0">{{ $member['last_login'] ?? 'Never' }}</p>
        </div>

        <div class="mb-0">
          <label class="form-label text-muted mb-1">Branch</label>
          <p class="mb-0">{{ $member['branch_name'] ?? 'Sydney Office' }}</p>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Quick Actions</h6>
      </div>
      <div class="card-body">
        <div class="d-grid gap-2">
          <a href="{{ route('teams.show', $member['id'] ?? '1') }}" class="btn btn-outline-primary btn-sm">
            <i class='bx bx-show me-1'></i> View Profile
          </a>

          <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
            <i class='bx bx-key me-1'></i> Force Password Reset
          </button>

          @if($isActive ?? true)
          <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deactivateAccount()">
            <i class='bx bx-user-x me-1'></i> Deactivate Account
          </button>
          @else
          <button type="button" class="btn btn-outline-success btn-sm" onclick="activateAccount()">
            <i class='bx bx-user-check me-1'></i> Activate Account
          </button>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Delete Team Member</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('teams.destroy', $member['id'] ?? '1') }}" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <div class="alert alert-danger mb-3">
            <i class='bx bx-error-circle me-2'></i>
            <strong>Warning:</strong> This action will soft-delete the user account!
          </div>

          <p class="mb-3">Are you sure you want to delete this team member?</p>

          <div class="alert alert-info mb-0">
            <strong>Member Details:</strong><br>
            <strong>Name:</strong> {{ $member['name'] ?? 'John Smith' }}<br>
            <strong>Employee ID:</strong> {{ $member['employee_id'] ?? 'EMP-001' }}<br>
            <strong>Position:</strong> {{ $member['position'] ?? 'Safety Officer' }}
          </div>

          <p class="mt-3 mb-0">
            <small class="text-muted">
              <i class='bx bx-info-circle me-1'></i>
              The user will be soft-deleted and can be restored by an administrator if needed. All associated records will be preserved.
            </small>
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class='bx bx-trash me-1'></i> Delete Member
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Force Password Reset Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Force Password Reset</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('teams.reset-password', $member['id'] ?? '1') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="alert alert-warning mb-3">
            <i class='bx bx-info-circle me-2'></i>
            <strong>Security Action:</strong> User will be required to reset their password on next login.
          </div>

          <p class="mb-3">This will immediately invalidate the user's current password and require them to set a new one.</p>

          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="notify_user" name="notify_user" value="1" checked>
            <label class="form-check-label" for="notify_user">
              Send notification email to user
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">
            <i class='bx bx-key me-1'></i> Force Password Reset
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
  'use strict';

  const editForm = document.getElementById('editMemberForm');
  const passwordField = document.getElementById('password');
  const confirmPasswordField = document.getElementById('password_confirmation');
  const phoneField = document.getElementById('phone');
  const roleField = document.getElementById('role');
  const isActiveField = document.getElementById('is_active');

  // Form validation
  if (editForm) {
    editForm.addEventListener('submit', function(e) {
      const password = passwordField.value;
      const confirmPassword = confirmPasswordField.value;

      // Only validate password if it's being changed
      if (password || confirmPassword) {
        // Check password match
        if (password !== confirmPassword) {
          e.preventDefault();
          alert('Passwords do not match. Please check and try again.');
          confirmPasswordField.focus();
          return false;
        }

        // Check minimum password requirements
        if (password.length < 8) {
          e.preventDefault();
          alert('Password must be at least 8 characters long.');
          passwordField.focus();
          return false;
        }

        if (!/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/[0-9]/.test(password)) {
          e.preventDefault();
          alert('Password must include uppercase letters, lowercase letters, and numbers.');
          passwordField.focus();
          return false;
        }
      }
    });
  }

  // Auto-format phone number (Australian format)
  if (phoneField) {
    phoneField.addEventListener('input', function() {
      let value = this.value.replace(/\D/g, '');

      if (value.length > 0) {
        if (value.length <= 4) {
          this.value = value;
        } else if (value.length <= 7) {
          this.value = value.slice(0, 4) + ' ' + value.slice(4);
        } else {
          this.value = value.slice(0, 4) + ' ' + value.slice(4, 7) + ' ' + value.slice(7, 10);
        }
      }
    });
  }

  // Role change warning
  if (roleField) {
    const originalRole = '{{ $member["role"] ?? "employee" }}';

    roleField.addEventListener('change', function() {
      const role = this.value;

      if (role === 'admin' && role !== originalRole) {
        if (!confirm('Admin role grants full system access including user management and system settings. Continue?')) {
          this.value = originalRole;
        }
      }
    });
  }

  // Account status change warning
  if (isActiveField) {
    isActiveField.addEventListener('change', function() {
      const status = this.value;

      if (status == '0') {
        if (!confirm('Deactivating this account will immediately prevent the user from logging in. Continue?')) {
          this.value = '1';
        }
      }
    });
  }
});

// Quick action functions
function deactivateAccount() {
  if (confirm('Deactivate this user account? The user will no longer be able to log in.')) {
    document.getElementById('is_active').value = '0';
    document.getElementById('editMemberForm').submit();
  }
}

function activateAccount() {
  if (confirm('Activate this user account? The user will be able to log in again.')) {
    document.getElementById('is_active').value = '1';
    document.getElementById('editMemberForm').submit();
  }
}
</script>
@endsection




