@extends('layouts.layoutMaster')

@section('title', 'Plan New Journey')

@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}">
@endsection

@section('content')
@include('layouts.sections.flash-message')

<h4 class="mb-4">Plan New Journey</h4>

<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body">
        <form action="{{ route('journey.store') }}" method="POST" id="journeyForm">
          @csrf

          <!-- Worker & Vehicle -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="user_id" class="form-label">Worker *</label>
              <select id="user_id" name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                <option value="">Select worker</option>
                @foreach(\App\Models\User::where('branch_id', auth()->user()->branch_id)->orderBy('name')->get() as $user)
                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                  {{ $user->name }} ({{ $user->role }})
                </option>
                @endforeach
              </select>
              @error('user_id')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="vehicle_id" class="form-label">Vehicle (Optional)</label>
              <select id="vehicle_id" name="vehicle_id" class="form-select @error('vehicle_id') is-invalid @enderror">
                <option value="">No vehicle assigned</option>
                @foreach(\App\Modules\VehicleManagement\Models\Vehicle::where('branch_id', auth()->user()->branch_id)->where('status', 'active')->get() as $vehicle)
                <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
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
                     value="{{ old('title') }}" required>
              @error('title')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="purpose" class="form-label">Purpose</label>
              <input type="text" id="purpose" name="purpose"
                     class="form-control @error('purpose') is-invalid @enderror"
                     placeholder="e.g., Client meeting, Delivery, Site inspection"
                     value="{{ old('purpose') }}">
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
                   value="{{ old('destination') }}" required>
            @error('destination')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label for="destination_address" class="form-label">Full Destination Address</label>
            <textarea id="destination_address" name="destination_address" rows="2"
                      class="form-control @error('destination_address') is-invalid @enderror"
                      placeholder="Complete address with postcode">{{ old('destination_address') }}</textarea>
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
                     value="{{ old('destination_latitude') }}" readonly>
              @error('destination_latitude')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-5">
              <label for="destination_longitude" class="form-label">Destination GPS Longitude</label>
              <input type="text" id="destination_longitude" name="destination_longitude"
                     class="form-control @error('destination_longitude') is-invalid @enderror"
                     placeholder="151.2093"
                     value="{{ old('destination_longitude') }}" readonly>
              @error('destination_longitude')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-2 d-flex align-items-end">
              <button type="button" class="btn btn-outline-primary w-100" id="captureDestination">
                <i class='bx bx-current-location'></i> Capture GPS
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
                     value="{{ old('estimated_distance_km') }}">
              @error('estimated_distance_km')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="estimated_duration_minutes" class="form-label">Estimated Duration (minutes)</label>
              <input type="number" id="estimated_duration_minutes" name="estimated_duration_minutes" min="0"
                     class="form-control @error('estimated_duration_minutes') is-invalid @enderror"
                     placeholder="e.g., 45"
                     value="{{ old('estimated_duration_minutes') }}">
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
                     value="{{ old('planned_start_time', now()->format('Y-m-d\TH:i')) }}" required>
              @error('planned_start_time')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="planned_end_time" class="form-label">Planned End Time *</label>
              <input type="datetime-local" id="planned_end_time" name="planned_end_time"
                     class="form-control flatpickr-datetime @error('planned_end_time') is-invalid @enderror"
                     value="{{ old('planned_end_time', now()->addHours(2)->format('Y-m-d\TH:i')) }}" required>
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
              <option value="15" {{ old('checkin_interval_minutes') == 15 ? 'selected' : '' }}>Every 15 minutes (High Risk)</option>
              <option value="30" {{ old('checkin_interval_minutes') == 30 ? 'selected' : '' }}>Every 30 minutes (Moderate Risk)</option>
              <option value="60" {{ old('checkin_interval_minutes', 60) == 60 ? 'selected' : '' }}>Every 1 hour (Standard)</option>
              <option value="120" {{ old('checkin_interval_minutes') == 120 ? 'selected' : '' }}>Every 2 hours (Low Risk)</option>
              <option value="240" {{ old('checkin_interval_minutes') == 240 ? 'selected' : '' }}>Every 4 hours (Remote Areas)</option>
              <option value="480" {{ old('checkin_interval_minutes') == 480 ? 'selected' : '' }}>Every 8 hours (Extended Trips)</option>
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
                     value="{{ old('emergency_contact_name') }}">
              @error('emergency_contact_name')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone</label>
              <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone"
                     class="form-control @error('emergency_contact_phone') is-invalid @enderror"
                     placeholder="e.g., 0412 345 678"
                     value="{{ old('emergency_contact_phone') }}">
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
                          placeholder="List any hazards: fatigue, weather, road conditions, wildlife, isolation, etc.">{{ old('hazards_identified') }}</textarea>
                @error('hazards_identified')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="mb-0">
                <label for="control_measures" class="form-label">Control Measures</label>
                <textarea id="control_measures" name="control_measures" rows="3"
                          class="form-control @error('control_measures') is-invalid @enderror"
                          placeholder="Actions to mitigate risks: rest breaks, alternative route, PPE, communication plan, etc.">{{ old('control_measures') }}</textarea>
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
                      placeholder="Any other relevant information about this journey...">{{ old('notes') }}</textarea>
            @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Form Actions -->
          <div class="d-flex justify-content-between">
            <a href="{{ route('journey.index') }}" class="btn btn-outline-secondary">
              <i class='bx bx-x'></i> Cancel
            </a>
            <div>
              <button type="button" class="btn btn-outline-primary me-2" id="saveOffline">
                <i class='bx bx-cloud-download'></i> Save Offline
              </button>
              <button type="submit" class="btn btn-primary">
                <i class='bx bx-check'></i> Plan Journey
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script src="https://unpkg.com/dexie@latest/dist/dexie.js"></script>
<script>
// Initialize Flatpickr for datetime fields
document.addEventListener('DOMContentLoaded', function() {
  const flatpickrDateTime = document.querySelectorAll('.flatpickr-datetime');
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
          toastr.success('GPS coordinates captured successfully');
        }

        btn.disabled = false;
        btn.innerHTML = '<i class="bx bx-current-location"></i> Capture GPS';
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
        btn.innerHTML = '<i class="bx bx-current-location"></i> Capture GPS';
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
    btn.innerHTML = '<i class="bx bx-current-location"></i> Capture GPS';
  }
});

// Offline Storage with IndexedDB
const db = new Dexie('WHSJourneys');
db.version(1).stores({
  journeys: '++id, timestamp, synced'
});

document.getElementById('saveOffline').addEventListener('click', async function() {
  const form = document.getElementById('journeyForm');
  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());

  // Validate required fields
  if (!data.user_id || !data.title || !data.destination || !data.planned_start_time || !data.planned_end_time || !data.checkin_interval_minutes) {
    if (typeof toastr !== 'undefined') {
      toastr.error('Please fill in all required fields before saving offline');
    } else {
      alert('Please fill in all required fields before saving offline');
    }
    return;
  }

  try {
    await db.journeys.add({
      ...data,
      timestamp: Date.now(),
      synced: false
    });

    if (typeof toastr !== 'undefined') {
      toastr.success('Journey saved offline. Will sync when online.');
    } else {
      alert('Journey saved offline. Will sync when online.');
    }

    // Reset form but keep worker selection
    const userId = data.user_id;
    form.reset();
    document.getElementById('user_id').value = userId;
  } catch (error) {
    console.error('Failed to save offline:', error);
    if (typeof toastr !== 'undefined') {
      toastr.error('Failed to save offline: ' + error.message);
    } else {
      alert('Failed to save offline: ' + error.message);
    }
  }
});

// Background sync when online
window.addEventListener('online', async function() {
  const unsynced = await db.journeys.where('synced').equals(false).toArray();

  if (unsynced.length === 0) return;

  if (typeof toastr !== 'undefined') {
    toastr.info('Syncing ' + unsynced.length + ' offline journey(s)...');
  }

  for (const journey of unsynced) {
    try {
      const response = await fetch('{{ route("journey.store") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json'
        },
        body: JSON.stringify(journey)
      });

      if (response.ok) {
        await db.journeys.update(journey.id, { synced: true });
        if (typeof toastr !== 'undefined') {
          toastr.success('Offline journey synced successfully');
        }
      } else {
        const errorData = await response.json();
        console.error('Sync failed:', errorData);
      }
    } catch (error) {
      console.error('Sync failed:', error);
    }
  }
});

// Form validation: End time must be after start time
document.getElementById('planned_end_time').addEventListener('change', function() {
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

document.getElementById('planned_start_time').addEventListener('change', function() {
  // Trigger validation on end time when start time changes
  document.getElementById('planned_end_time').dispatchEvent(new Event('change'));
});
</script>
@endsection
