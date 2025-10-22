@extends('layouts/layoutMaster')

@section('title', 'Edit Maintenance Schedule')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-1">Edit Maintenance Schedule</h4>
    <p class="mb-0">Update preventive maintenance schedule details</p>
  </div>
  <div>
    <a href="{{ route('maintenance.show', $schedule) }}" class="btn btn-outline-secondary me-2">
      <i class='bx bx-show me-1'></i> View Details
    </a>
    <a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary">
      <i class='bx bx-arrow-back me-1'></i> Back to List
    </a>
  </div>
</div>

<form action="{{ route('maintenance.update', $schedule) }}" method="POST" id="maintenanceScheduleForm">
  @csrf
  @method('PUT')

  <div class="row">
    <!-- Main Form Card -->
    <div class="col-md-8">
      <!-- Basic Information -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Schedule Information</h5>
        </div>
        <div class="card-body">
          <!-- Vehicle Selection (Read-only for Edit) -->
          <div class="mb-3">
            <label class="form-label">Vehicle / Equipment</label>
            <input type="text"
                   class="form-control"
                   value="{{ $schedule->vehicle->registration_number }} - {{ $schedule->vehicle->make }} {{ $schedule->vehicle->model }}"
                   readonly
                   disabled>
            <small class="form-text text-muted">Vehicle cannot be changed after schedule creation</small>
          </div>

          <!-- Schedule Name -->
          <div class="mb-3">
            <label for="schedule_name" class="form-label">Schedule Name *</label>
            <input type="text"
                   id="schedule_name"
                   name="schedule_name"
                   class="form-control @error('schedule_name') is-invalid @enderror"
                   placeholder="e.g., Oil Change Service"
                   value="{{ old('schedule_name', $schedule->schedule_name) }}"
                   required>
            @error('schedule_name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Description -->
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea id="description"
                      name="description"
                      rows="3"
                      class="form-control @error('description') is-invalid @enderror"
                      placeholder="Additional details about this maintenance schedule...">{{ old('description', $schedule->description) }}</textarea>
            @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Schedule Type (Read-only for Edit) -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Schedule Type</label>
              <input type="text"
                     class="form-control"
                     value="{{ ucfirst($schedule->schedule_type) }}"
                     readonly
                     disabled>
              <small class="form-text text-muted">Type cannot be changed after creation</small>
            </div>

            <div class="col-md-6 mb-3">
              <label for="priority" class="form-label">Priority *</label>
              <select id="priority" name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                <option value="low" {{ old('priority', $schedule->priority) == 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ old('priority', $schedule->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ old('priority', $schedule->priority) == 'high' ? 'selected' : '' }}>High</option>
                <option value="critical" {{ old('priority', $schedule->priority) == 'critical' ? 'selected' : '' }}>Critical</option>
              </select>
              @error('priority')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- Recurrence Settings (Read-only) -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Recurrence Settings</h5>
        </div>
        <div class="card-body">
          <div class="alert alert-info mb-3">
            <i class='bx bx-info-circle me-2'></i>
            Recurrence settings cannot be modified after schedule creation. Create a new schedule if different recurrence is needed.
          </div>

          <!-- Display Current Recurrence -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Recurrence Type</label>
              <input type="text"
                     class="form-control"
                     value="{{ ucfirst(str_replace('_', ' ', $schedule->recurrence_type)) }}"
                     readonly
                     disabled>
            </div>

            @if($schedule->recurrence_interval)
            <div class="col-md-6 mb-3">
              <label class="form-label">Recurrence Interval</label>
              <input type="text"
                     class="form-control"
                     value="{{ $schedule->recurrence_interval }} {{ $schedule->recurrence_type }}(s)"
                     readonly
                     disabled>
            </div>
            @endif

            @if($schedule->odometer_interval)
            <div class="col-md-6 mb-3">
              <label class="form-label">Odometer Interval</label>
              <input type="text"
                     class="form-control"
                     value="{{ number_format($schedule->odometer_interval) }} km"
                     readonly
                     disabled>
            </div>
            @endif

            @if($schedule->engine_hours_interval)
            <div class="col-md-6 mb-3">
              <label class="form-label">Engine Hours Interval</label>
              <input type="text"
                     class="form-control"
                     value="{{ number_format($schedule->engine_hours_interval) }} hours"
                     readonly
                     disabled>
            </div>
            @endif

            <div class="col-md-6 mb-3">
              <label class="form-label">Start Date</label>
              <input type="text"
                     class="form-control"
                     value="{{ $schedule->start_date?->format('d/m/Y') ?? 'N/A' }}"
                     readonly
                     disabled>
            </div>

            @if($schedule->next_due_date)
            <div class="col-md-6 mb-3">
              <label class="form-label">Next Due Date</label>
              <input type="text"
                     class="form-control {{ $schedule->isOverdue() ? 'text-danger fw-bold' : '' }}"
                     value="{{ $schedule->next_due_date->format('d/m/Y') }}{{ $schedule->isOverdue() ? ' (OVERDUE)' : '' }}"
                     readonly
                     disabled>
            </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Cost & Vendor Information -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Cost & Vendor Information</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="estimated_cost_per_service" class="form-label">Estimated Cost per Service</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number"
                       id="estimated_cost_per_service"
                       name="estimated_cost_per_service"
                       class="form-control @error('estimated_cost_per_service') is-invalid @enderror"
                       placeholder="0.00"
                       value="{{ old('estimated_cost_per_service', $schedule->estimated_cost_per_service) }}"
                       min="0"
                       step="0.01">
              </div>
              @error('estimated_cost_per_service')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="preferred_vendor" class="form-label">Preferred Vendor</label>
              <input type="text"
                     id="preferred_vendor"
                     name="preferred_vendor"
                     class="form-control @error('preferred_vendor') is-invalid @enderror"
                     placeholder="e.g., ABC Auto Services"
                     value="{{ old('preferred_vendor', $schedule->preferred_vendor) }}">
              @error('preferred_vendor')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="mb-3">
            <label for="vendor_contact" class="form-label">Vendor Contact</label>
            <input type="text"
                   id="vendor_contact"
                   name="vendor_contact"
                   class="form-control @error('vendor_contact') is-invalid @enderror"
                   placeholder="Phone or email"
                   value="{{ old('vendor_contact', $schedule->vendor_contact) }}">
            @error('vendor_contact')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
      <!-- Schedule Status -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Schedule Status</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Current Status</label>
            @php
            $statusClass = match($schedule->status) {
              'active' => 'success',
              'paused' => 'warning',
              'completed' => 'secondary',
              default => 'secondary'
            };
            @endphp
            <div>
              <span class="badge bg-{{ $statusClass }} fs-6">{{ ucfirst($schedule->status) }}</span>
            </div>
          </div>

          @if($schedule->status === 'active')
          <div class="alert alert-success mb-0">
            <i class='bx bx-check-circle me-2'></i>
            This schedule is active and generating maintenance reminders.
          </div>
          @elseif($schedule->status === 'paused')
          <div class="alert alert-warning mb-0">
            <i class='bx bx-pause-circle me-2'></i>
            This schedule is paused. No reminders will be sent.
          </div>
          @endif
        </div>
      </div>

      <!-- Notifications -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Notifications</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="reminder_days_before" class="form-label">Reminder (days before)</label>
            <input type="number"
                   id="reminder_days_before"
                   name="reminder_days_before"
                   class="form-control @error('reminder_days_before') is-invalid @enderror"
                   placeholder="7"
                   value="{{ old('reminder_days_before', $schedule->reminder_days_before ?? 7) }}"
                   min="1"
                   max="90">
            @error('reminder_days_before')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Send reminders X days before due date</small>
          </div>

          <div class="form-check mb-2">
            <input class="form-check-input"
                   type="checkbox"
                   id="email_notifications"
                   name="email_notifications"
                   value="1"
                   {{ old('email_notifications', $schedule->email_notifications) ? 'checked' : '' }}>
            <label class="form-check-label" for="email_notifications">
              Email Notifications
            </label>
          </div>

          <div class="form-check mb-2">
            <input class="form-check-input"
                   type="checkbox"
                   id="sms_notifications"
                   name="sms_notifications"
                   value="1"
                   {{ old('sms_notifications', $schedule->sms_notifications) ? 'checked' : '' }}>
            <label class="form-check-label" for="sms_notifications">
              SMS Notifications
            </label>
          </div>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="card">
        <div class="card-body">
          <button type="submit" class="btn btn-primary w-100 mb-2">
            <i class='bx bx-save me-1'></i> Update Schedule
          </button>
          <a href="{{ route('maintenance.show', $schedule) }}" class="btn btn-outline-secondary w-100 mb-2">
            Cancel
          </a>

          @if($schedule->status === 'active')
          <form action="{{ route('maintenance.pause', $schedule) }}" method="POST" class="mb-2">
            @csrf
            <button type="button" class="btn btn-warning w-100" onclick="pauseSchedule(this)">
              <i class='bx bx-pause-circle me-1'></i> Pause Schedule
            </button>
          </form>
          @elseif($schedule->status === 'paused')
          <form action="{{ route('maintenance.resume', $schedule) }}" method="POST" class="mb-2">
            @csrf
            <button type="submit" class="btn btn-success w-100">
              <i class='bx bx-play-circle me-1'></i> Resume Schedule
            </button>
          </form>
          @endif

          <hr>

          <button type="button" class="btn btn-danger w-100" onclick="deleteSchedule()">
            <i class='bx bx-trash me-1'></i> Delete Schedule
          </button>
        </div>
      </div>
    </div>
  </div>
</form>

<!-- Pause Schedule Modal -->
<div class="modal fade" id="pauseModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Pause Maintenance Schedule</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="pauseForm" method="POST" action="{{ route('maintenance.pause', $schedule) }}">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="pause_reason" class="form-label">Reason for Pausing *</label>
            <textarea id="pause_reason"
                      name="reason"
                      rows="3"
                      class="form-control"
                      placeholder="e.g., Vehicle out of service for repairs"
                      required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">
            <i class='bx bx-pause-circle me-1'></i> Pause Schedule
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
        <h5 class="modal-title">Delete Maintenance Schedule</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('maintenance.destroy', $schedule) }}" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <p>Are you sure you want to delete this maintenance schedule?</p>
          <div class="alert alert-danger mb-0">
            <i class='bx bx-error-circle me-2'></i>
            <strong>Warning:</strong> This will delete all associated maintenance logs and history. This action cannot be undone.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class='bx bx-trash me-1'></i> Delete Schedule
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
function pauseSchedule(button) {
  var pauseModal = new bootstrap.Modal(document.getElementById('pauseModal'));
  pauseModal.show();
}

function deleteSchedule() {
  var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
  deleteModal.show();
}
</script>
@endsection
