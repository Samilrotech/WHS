@extends('layouts/layoutMaster')

@section('title', 'Create Maintenance Schedule')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-1">Create Maintenance Schedule</h4>
    <p class="mb-0">Set up preventive maintenance schedule for vehicles and equipment</p>
  </div>
  <a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary">
    <i class='bx bx-arrow-back me-1'></i> Back to Schedules
  </a>
</div>

<form action="{{ route('maintenance.store') }}" method="POST" id="maintenanceScheduleForm">
  @csrf

  <div class="row">
    <!-- Main Form Card -->
    <div class="col-md-8">
      <!-- Basic Information -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Schedule Information</h5>
        </div>
        <div class="card-body">
          <!-- Vehicle Selection -->
          <div class="mb-3">
            <label for="vehicle_id" class="form-label">Vehicle / Equipment *</label>
            <select id="vehicle_id" name="vehicle_id" class="form-select @error('vehicle_id') is-invalid @enderror" required>
              <option value="">Select Vehicle...</option>
              @foreach($vehicles as $vehicle)
              <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                {{ $vehicle->registration_number }} - {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }})
              </option>
              @endforeach
            </select>
            @error('vehicle_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Schedule Name -->
          <div class="mb-3">
            <label for="schedule_name" class="form-label">Schedule Name *</label>
            <input type="text"
                   id="schedule_name"
                   name="schedule_name"
                   class="form-control @error('schedule_name') is-invalid @enderror"
                   placeholder="e.g., Oil Change Service"
                   value="{{ old('schedule_name') }}"
                   required>
            @error('schedule_name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Descriptive name for this maintenance schedule</small>
          </div>

          <!-- Description -->
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea id="description"
                      name="description"
                      rows="3"
                      class="form-control @error('description') is-invalid @enderror"
                      placeholder="Additional details about this maintenance schedule...">{{ old('description') }}</textarea>
            @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Schedule Type -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="schedule_type" class="form-label">Schedule Type *</label>
              <select id="schedule_type" name="schedule_type" class="form-select @error('schedule_type') is-invalid @enderror" required>
                <option value="">Select Type...</option>
                <option value="preventive" {{ old('schedule_type') == 'preventive' ? 'selected' : '' }}>Preventive Maintenance</option>
                <option value="predictive" {{ old('schedule_type') == 'predictive' ? 'selected' : '' }}>Predictive Maintenance</option>
                <option value="corrective" {{ old('schedule_type') == 'corrective' ? 'selected' : '' }}>Corrective Maintenance</option>
                <option value="emergency" {{ old('schedule_type') == 'emergency' ? 'selected' : '' }}>Emergency Maintenance</option>
              </select>
              @error('schedule_type')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="priority" class="form-label">Priority *</label>
              <select id="priority" name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                <option value="">Select Priority...</option>
                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
              </select>
              @error('priority')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- Recurrence Settings -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Recurrence Settings</h5>
        </div>
        <div class="card-body">
          <!-- Recurrence Type -->
          <div class="mb-3">
            <label for="recurrence_type" class="form-label">Recurrence Type *</label>
            <select id="recurrence_type" name="recurrence_type" class="form-select @error('recurrence_type') is-invalid @enderror" required>
              <option value="">Select Recurrence...</option>
              <option value="once" {{ old('recurrence_type') == 'once' ? 'selected' : '' }}>Once (One-time)</option>
              <option value="daily" {{ old('recurrence_type') == 'daily' ? 'selected' : '' }}>Daily</option>
              <option value="weekly" {{ old('recurrence_type') == 'weekly' ? 'selected' : '' }}>Weekly</option>
              <option value="monthly" {{ old('recurrence_type') == 'monthly' ? 'selected' : '' }}>Monthly</option>
              <option value="quarterly" {{ old('recurrence_type') == 'quarterly' ? 'selected' : '' }}>Quarterly (3 months)</option>
              <option value="semi_annual" {{ old('recurrence_type') == 'semi_annual' ? 'selected' : '' }}>Semi-Annual (6 months)</option>
              <option value="annual" {{ old('recurrence_type') == 'annual' ? 'selected' : '' }}>Annual (12 months)</option>
              <option value="odometer_based" {{ old('recurrence_type') == 'odometer_based' ? 'selected' : '' }}>Odometer-Based (km)</option>
              <option value="engine_hours" {{ old('recurrence_type') == 'engine_hours' ? 'selected' : '' }}>Engine Hours</option>
            </select>
            @error('recurrence_type')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Conditional Fields Based on Recurrence Type -->
          <div id="intervalFields" style="display: none;">
            <div class="mb-3">
              <label for="recurrence_interval" class="form-label">Recurrence Interval</label>
              <div class="input-group">
                <input type="number"
                       id="recurrence_interval"
                       name="recurrence_interval"
                       class="form-control @error('recurrence_interval') is-invalid @enderror"
                       placeholder="e.g., 2"
                       value="{{ old('recurrence_interval') }}"
                       min="1">
                <span class="input-group-text" id="intervalUnit">units</span>
              </div>
              <small class="form-text text-muted">How often the maintenance should repeat</small>
              @error('recurrence_interval')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div id="odometerFields" style="display: none;">
            <div class="mb-3">
              <label for="odometer_interval" class="form-label">Odometer Interval (km) *</label>
              <div class="input-group">
                <input type="number"
                       id="odometer_interval"
                       name="odometer_interval"
                       class="form-control @error('odometer_interval') is-invalid @enderror"
                       placeholder="e.g., 5000"
                       value="{{ old('odometer_interval') }}"
                       min="1">
                <span class="input-group-text">km</span>
              </div>
              <small class="form-text text-muted">Service every X kilometers</small>
              @error('odometer_interval')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div id="engineHoursFields" style="display: none;">
            <div class="mb-3">
              <label for="engine_hours_interval" class="form-label">Engine Hours Interval *</label>
              <div class="input-group">
                <input type="number"
                       id="engine_hours_interval"
                       name="engine_hours_interval"
                       class="form-control @error('engine_hours_interval') is-invalid @enderror"
                       placeholder="e.g., 250"
                       value="{{ old('engine_hours_interval') }}"
                       min="1">
                <span class="input-group-text">hours</span>
              </div>
              <small class="form-text text-muted">Service every X engine hours</small>
              @error('engine_hours_interval')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Start Date -->
          <div class="mb-3">
            <label for="start_date" class="form-label">Start Date *</label>
            <input type="date"
                   id="start_date"
                   name="start_date"
                   class="form-control @error('start_date') is-invalid @enderror"
                   value="{{ old('start_date', date('Y-m-d')) }}"
                   required>
            @error('start_date')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">When should this schedule begin?</small>
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
                       value="{{ old('estimated_cost_per_service') }}"
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
                     value="{{ old('preferred_vendor') }}">
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
                   value="{{ old('vendor_contact') }}">
            @error('vendor_contact')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
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
                   value="{{ old('reminder_days_before', 7) }}"
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
                   {{ old('email_notifications', true) ? 'checked' : '' }}>
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
                   {{ old('sms_notifications') ? 'checked' : '' }}>
            <label class="form-check-label" for="sms_notifications">
              SMS Notifications
            </label>
          </div>
        </div>
      </div>

      <!-- Parts Management -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Parts Management</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label for="required_parts" class="form-label">Required Parts</label>
            <textarea id="required_parts"
                      name="required_parts"
                      rows="3"
                      class="form-control @error('required_parts') is-invalid @enderror"
                      placeholder="e.g., Oil filter, Engine oil 5W-30">{{ old('required_parts') }}</textarea>
            @error('required_parts')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">List parts needed for this service</small>
          </div>

          <div class="form-check">
            <input class="form-check-input"
                   type="checkbox"
                   id="auto_order_parts"
                   name="auto_order_parts"
                   value="1"
                   {{ old('auto_order_parts') ? 'checked' : '' }}>
            <label class="form-check-label" for="auto_order_parts">
              Auto-order parts when low stock
            </label>
          </div>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="card">
        <div class="card-body">
          <button type="submit" class="btn btn-primary w-100 mb-2">
            <i class='bx bx-save me-1'></i> Create Schedule
          </button>
          <a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary w-100">
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
  // Handle recurrence type changes
  $('#recurrence_type').on('change', function() {
    const recurrenceType = $(this).val();

    // Hide all conditional fields
    $('#intervalFields, #odometerFields, #engineHoursFields').hide();

    // Show relevant fields based on selection
    if (recurrenceType === 'odometer_based') {
      $('#odometerFields').show();
      $('#odometer_interval').prop('required', true);
      $('#engine_hours_interval, #recurrence_interval').prop('required', false);
    } else if (recurrenceType === 'engine_hours') {
      $('#engineHoursFields').show();
      $('#engine_hours_interval').prop('required', true);
      $('#odometer_interval, #recurrence_interval').prop('required', false);
    } else if (['daily', 'weekly', 'monthly'].includes(recurrenceType)) {
      $('#intervalFields').show();
      $('#recurrence_interval').prop('required', false); // Optional for these
      $('#odometer_interval, #engine_hours_interval').prop('required', false);

      // Update interval unit text
      const unitMap = {
        'daily': 'days',
        'weekly': 'weeks',
        'monthly': 'months'
      };
      $('#intervalUnit').text(unitMap[recurrenceType] || 'units');
    } else {
      // For once, quarterly, semi_annual, annual - no additional fields needed
      $('#odometer_interval, #engine_hours_interval, #recurrence_interval').prop('required', false);
    }
  });

  // Trigger change on page load to show correct fields
  $('#recurrence_type').trigger('change');

  // Form validation
  $('#maintenanceScheduleForm').on('submit', function(e) {
    const recurrenceType = $('#recurrence_type').val();

    if (recurrenceType === 'odometer_based' && !$('#odometer_interval').val()) {
      e.preventDefault();
      toastr.error('Odometer interval is required for odometer-based schedules');
      return false;
    }

    if (recurrenceType === 'engine_hours' && !$('#engine_hours_interval').val()) {
      e.preventDefault();
      toastr.error('Engine hours interval is required for engine hours-based schedules');
      return false;
    }
  });
});
</script>
@endsection
