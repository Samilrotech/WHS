@extends('layouts.layoutMaster')

@section('title', 'Edit Journey - ' . $journey->title)

@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}">
@endsection

@section('content')
@include('layouts.sections.flash-message')

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0">Edit Journey: {{ $journey->title }}</h4>
  <a href="{{ route('journey.show', $journey) }}" class="btn btn-outline-secondary">
    <i class='bx bx-arrow-back'></i> Back to Journey
  </a>
</div>

@if($journey->status !== 'planned')
<div class="alert alert-warning mb-4">
  <i class='bx bx-info-circle me-2'></i>
  <strong>Note:</strong> This journey has already started or been completed. Some fields may be restricted.
</div>
@endif

<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body">
        <form action="{{ route('journey.update', $journey) }}" method="POST" id="journeyForm">
          @csrf
          @method('PUT')

          <!-- Worker & Vehicle -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="user_id" class="form-label">Worker *</label>
              <select id="user_id" name="user_id" class="form-select @error('user_id') is-invalid @enderror"
                      {{ $journey->status !== 'planned' ? 'disabled' : '' }} required>
                <option value="">Select worker</option>
                @foreach(\App\Models\User::where('branch_id', auth()->user()->branch_id)->orderBy('name')->get() as $user)
                <option value="{{ $user->id }}" {{ old('user_id', $journey->user_id) == $user->id ? 'selected' : '' }}>
                  {{ $user->name }} ({{ $user->role }})
                </option>
                @endforeach
              </select>
              @if($journey->status !== 'planned')
              <input type="hidden" name="user_id" value="{{ $journey->user_id }}">
              <div class="form-text">Worker cannot be changed after journey starts</div>
              @endif
              @error('user_id')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="vehicle_id" class="form-label">Vehicle (Optional)</label>
              <select id="vehicle_id" name="vehicle_id" class="form-select @error('vehicle_id') is-invalid @enderror">
                <option value="">No vehicle assigned</option>
                @foreach(\App\Modules\VehicleManagement\Models\Vehicle::where('branch_id', auth()->user()->branch_id)->where('status', 'active')->get() as $vehicle)
                <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $journey->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                  {{ $vehicle->registration_number }} - {{ $vehicle->make }} {{ $vehicle->model }}
                </option>
                @endforeach
              </select>
              @error('vehicle_id')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Journey Title & Purpose -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="title" class="form-label">Journey Title *</label>
              <input type="text" id="title" name="title"
                     class="form-control @error('title') is-invalid @enderror"
                     placeholder="e.g., Delivery to Sydney Client"
                     value="{{ old('title', $journey->title) }}" required>
              @error('title')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="purpose" class="form-label">Purpose</label>
              <input type="text" id="purpose" name="purpose"
                     class="form-control @error('purpose') is-invalid @enderror"
                     placeholder="e.g., Client meeting, Delivery, Site inspection"
                     value="{{ old('purpose', $journey->purpose) }}">
              @error('purpose')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Destination Details -->
          <div class="mb-3">
            <label for="destination" class="form-label">Destination *</label>
            <input type="text" id="destination" name="destination"
                   class="form-control @error('destination') is-invalid @enderror"
                   placeholder="e.g., ABC Corp, 123 George St, Sydney NSW 2000"
                   value="{{ old('destination', $journey->destination) }}" required>
            @error('destination')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label for="destination_address" class="form-label">Full Destination Address</label>
            <textarea id="destination_address" name="destination_address" rows="2"
                      class="form-control @error('destination_address') is-invalid @enderror"
                      placeholder="Complete address with postcode">{{ old('destination_address', $journey->destination_address) }}</textarea>
            @error('destination_address')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- GPS Coordinates -->
          <div class="row mb-3">
            <div class="col-md-5">
              <label for="destination_latitude" class="form-label">Destination GPS Latitude</label>
              <input type="text" id="destination_latitude" name="destination_latitude"
                     class="form-control @error('destination_latitude') is-invalid @enderror"
                     placeholder="-33.8688"
                     value="{{ old('destination_latitude', $journey->destination_latitude) }}" readonly>
              @error('destination_latitude')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-5">
              <label for="destination_longitude" class="form-label">Destination GPS Longitude</label>
              <input type="text" id="destination_longitude" name="destination_longitude"
                     class="form-control @error('destination_longitude') is-invalid @enderror"
                     placeholder="151.2093"
                     value="{{ old('destination_longitude', $journey->destination_longitude) }}" readonly>
              @error('destination_longitude')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-2 d-flex align-items-end">
              <button type="button" class="btn btn-outline-primary w-100" id="captureDestination">
                <i class='bx bx-current-location'></i> Update GPS
              </button>
            </div>
          </div>

          <!-- Journey Planning -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="estimated_distance_km" class="form-label">Estimated Distance (km)</label>
              <input type="number" id="estimated_distance_km" name="estimated_distance_km" step="0.1" min="0"
                     class="form-control @error('estimated_distance_km') is-invalid @enderror"
                     placeholder="e.g., 25.5"
                     value="{{ old('estimated_distance_km', $journey->estimated_distance_km) }}">
              @error('estimated_distance_km')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="estimated_duration_minutes" class="form-label">Estimated Duration (minutes)</label>
              <input type="number" id="estimated_duration_minutes" name="estimated_duration_minutes" min="0"
                     class="form-control @error('estimated_duration_minutes') is-invalid @enderror"
                     placeholder="e.g., 45"
                     value="{{ old('estimated_duration_minutes', $journey->estimated_duration_minutes) }}">
              @error('estimated_duration_minutes')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Start & End Time -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="planned_start_time" class="form-label">Planned Start Time *</label>
              <input type="datetime-local" id="planned_start_time" name="planned_start_time"
                     class="form-control flatpickr-datetime @error('planned_start_time') is-invalid @enderror"
                     value="{{ old('planned_start_time', $journey->planned_start_time->format('Y-m-d\TH:i')) }}"
                     {{ $journey->status !== 'planned' ? 'disabled' : '' }} required>
              @if($journey->status !== 'planned')
              <input type="hidden" name="planned_start_time" value="{{ $journey->planned_start_time->format('Y-m-d\TH:i') }}">
              <div class="form-text">Start time cannot be changed after journey starts</div>
              @endif
              @error('planned_start_time')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="planned_end_time" class="form-label">Planned End Time *</label>
              <input type="datetime-local" id="planned_end_time" name="planned_end_time"
                     class="form-control flatpickr-datetime @error('planned_end_time') is-invalid @enderror"
                     value="{{ old('planned_end_time', $journey->planned_end_time->format('Y-m-d\TH:i')) }}" required>
              <div class="form-text">Must be after start time</div>
              @error('planned_end_time')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Check-in Interval -->
          <div class="mb-3">
            <label for="checkin_interval_minutes" class="form-label">Check-in Interval *</label>
            <select id="checkin_interval_minutes" name="checkin_interval_minutes"
                    class="form-select @error('checkin_interval_minutes') is-invalid @enderror" required>
              <option value="">Select check-in frequency</option>
              <option value="15" {{ old('checkin_interval_minutes', $journey->checkin_interval_minutes) == 15 ? 'selected' : '' }}>Every 15 minutes (High Risk)</option>
              <option value="30" {{ old('checkin_interval_minutes', $journey->checkin_interval_minutes) == 30 ? 'selected' : '' }}>Every 30 minutes (Moderate Risk)</option>
              <option value="60" {{ old('checkin_interval_minutes', $journey->checkin_interval_minutes) == 60 ? 'selected' : '' }}>Every 1 hour (Standard)</option>
              <option value="120" {{ old('checkin_interval_minutes', $journey->checkin_interval_minutes) == 120 ? 'selected' : '' }}>Every 2 hours (Low Risk)</option>
              <option value="240" {{ old('checkin_interval_minutes', $journey->checkin_interval_minutes) == 240 ? 'selected' : '' }}>Every 4 hours (Remote Areas)</option>
              <option value="480" {{ old('checkin_interval_minutes', $journey->checkin_interval_minutes) == 480 ? 'selected' : '' }}>Every 8 hours (Extended Trips)</option>
            </select>
            <div class="form-text">How often should the worker check-in? (15-480 minutes)</div>
            @error('checkin_interval_minutes')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Emergency Contact -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
              <input type="text" id="emergency_contact_name" name="emergency_contact_name"
                     class="form-control @error('emergency_contact_name') is-invalid @enderror"
                     placeholder="e.g., John Smith"
                     value="{{ old('emergency_contact_name', $journey->emergency_contact_name) }}">
              @error('emergency_contact_name')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone</label>
              <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone"
                     class="form-control @error('emergency_contact_phone') is-invalid @enderror"
                     placeholder="e.g., 0412 345 678"
                     value="{{ old('emergency_contact_phone', $journey->emergency_contact_phone) }}">
              @error('emergency_contact_phone')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Risk Assessment -->
          <div class="card bg-label-warning mb-3">
            <div class="card-body">
              <h5 class="card-title mb-3">
                <i class='bx bx-shield-alt-2 me-2'></i>Risk Assessment
              </h5>

              <div class="mb-3">
                <label for="hazards_identified" class="form-label">Hazards Identified</label>
                <textarea id="hazards_identified" name="hazards_identified" rows="3"
                          class="form-control @error('hazards_identified') is-invalid @enderror"
                          placeholder="List any hazards: fatigue, weather, road conditions, wildlife, isolation, etc.">{{ old('hazards_identified', $journey->hazards_identified) }}</textarea>
                @error('hazards_identified')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="mb-0">
                <label for="control_measures" class="form-label">Control Measures</label>
                <textarea id="control_measures" name="control_measures" rows="3"
                          class="form-control @error('control_measures') is-invalid @enderror"
                          placeholder="Actions to mitigate risks: rest breaks, alternative route, PPE, communication plan, etc.">{{ old('control_measures', $journey->control_measures) }}</textarea>
                @error('control_measures')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <!-- Additional Notes -->
          <div class="mb-3">
            <label for="notes" class="form-label">Additional Notes</label>
            <textarea id="notes" name="notes" rows="3"
                      class="form-control @error('notes') is-invalid @enderror"
                      placeholder="Any other relevant information about this journey...">{{ old('notes', $journey->notes) }}</textarea>
            @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Form Actions -->
          <div class="d-flex justify-content-between">
            <a href="{{ route('journey.show', $journey) }}" class="btn btn-outline-secondary">
              <i class='bx bx-x'></i> Cancel
            </a>
            <div>
              @if($journey->status === 'planned')
              <button type="button" class="btn btn-outline-danger me-2" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class='bx bx-trash'></i> Delete Journey
              </button>
              @endif
              <button type="submit" class="btn btn-primary">
                <i class='bx bx-check'></i> Update Journey
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
@if($journey->status === 'planned')
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Delete Journey</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning">
          <i class='bx bx-error-circle me-2'></i>
          <strong>Are you sure you want to delete this journey?</strong>
        </div>
        <p>This action cannot be undone. The journey plan will be permanently deleted.</p>
        <p><strong>Journey:</strong> {{ $journey->title }}</p>
        <p><strong>Worker:</strong> {{ $journey->user->name }}</p>
        <p><strong>Destination:</strong> {{ $journey->destination }}</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <form action="{{ route('journey.destroy', $journey) }}" method="POST" class="d-inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">
            <i class='bx bx-trash'></i> Delete Journey
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endif
@endsection

@section('page-script')
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script>
// Initialize Flatpickr for datetime fields
document.addEventListener('DOMContentLoaded', function() {
  const flatpickrDateTime = document.querySelectorAll('.flatpickr-datetime:not([disabled])');
  flatpickrDateTime.forEach(element => {
    flatpickr(element, {
      enableTime: true,
      dateFormat: 'Y-m-d H:i',
      time_24hr: true,
      altInput: true,
      altFormat: 'd/m/Y h:i K'
    });
  });
});

// GPS Location Capture for Destination
document.getElementById('captureDestination').addEventListener('click', function() {
  const btn = this;
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Capturing...';

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      function(position) {
        document.getElementById('destination_latitude').value = position.coords.latitude.toFixed(6);
        document.getElementById('destination_longitude').value = position.coords.longitude.toFixed(6);

        if (typeof toastr !== 'undefined') {
          toastr.success('GPS coordinates updated successfully');
        }

        btn.disabled = false;
        btn.innerHTML = '<i class="bx bx-check-circle"></i> GPS Updated';
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-success');

        // Reset button after 3 seconds
        setTimeout(function() {
          btn.classList.remove('btn-success');
          btn.classList.add('btn-outline-primary');
          btn.innerHTML = '<i class="bx bx-current-location"></i> Update GPS';
        }, 3000);
      },
      function(error) {
        let errorMsg = 'Could not capture location';
        switch(error.code) {
          case error.PERMISSION_DENIED:
            errorMsg = 'Location permission denied. Please enable location access.';
            break;
          case error.POSITION_UNAVAILABLE:
            errorMsg = 'Location information unavailable.';
            break;
          case error.TIMEOUT:
            errorMsg = 'Location request timed out.';
            break;
        }

        if (typeof toastr !== 'undefined') {
          toastr.error(errorMsg);
        } else {
          alert(errorMsg);
        }

        btn.disabled = false;
        btn.innerHTML = '<i class="bx bx-current-location"></i> Update GPS';
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      }
    );
  } else {
    if (typeof toastr !== 'undefined') {
      toastr.error('Geolocation not supported by this browser');
    } else {
      alert('Geolocation not supported by this browser');
    }
    btn.disabled = false;
    btn.innerHTML = '<i class="bx bx-current-location"></i> Update GPS';
  }
});

// Form validation: End time must be after start time
document.getElementById('planned_end_time')?.addEventListener('change', function() {
  const startTime = new Date(document.getElementById('planned_start_time').value);
  const endTime = new Date(this.value);

  if (endTime <= startTime) {
    this.setCustomValidity('End time must be after start time');
    this.classList.add('is-invalid');
  } else {
    this.setCustomValidity('');
    this.classList.remove('is-invalid');
  }
});

document.getElementById('planned_start_time')?.addEventListener('change', function() {
  // Trigger validation on end time when start time changes
  const endTimeField = document.getElementById('planned_end_time');
  if (endTimeField) {
    endTimeField.dispatchEvent(new Event('change'));
  }
});
</script>
@endsection
