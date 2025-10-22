@extends('layouts.layoutMaster')

@section('title', $journey->title . ' - Journey Details')

@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/animate-css/animate.css') }}">
@endsection

@section('content')
@include('layouts.sections.flash-message')

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0">{{ $journey->title }}</h4>
  <div>
    @if($journey->status === 'planned')
    <a href="{{ route('journey.edit', $journey) }}" class="btn btn-outline-primary me-2">
      <i class='bx bx-edit'></i> Edit
    </a>
    @endif
    <a href="{{ route('journey.index') }}" class="btn btn-outline-secondary">
      <i class='bx bx-arrow-back'></i> Back to List
    </a>
  </div>
</div>

<!-- Status Alert Banners -->
@if($journey->status === 'emergency')
<div class="alert alert-danger alert-dismissible mb-4" role="alert">
  <h5 class="alert-heading mb-2">
    <i class="bx bx-error-circle me-2"></i>
    EMERGENCY ALERT - Worker Requires Assistance
  </h5>
  <p class="mb-0">Emergency assistance requested for <strong>{{ $journey->user->name }}</strong>. Immediate action required.</p>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($journey->checkin_overdue && $journey->status === 'active')
<div class="alert alert-warning alert-dismissible mb-4" role="alert">
  <h5 class="alert-heading mb-2">
    <i class="bx bx-time me-2"></i>
    Check-in Overdue
  </h5>
  <p class="mb-0">
    <strong>{{ $journey->user->name }}</strong> has not checked in since
    {{ $journey->last_checkin_time ? $journey->last_checkin_time->format('d/m/Y H:i') : 'journey start' }}.
    Expected check-in: {{ $journey->next_checkin_due->format('d/m/Y H:i') }}
  </p>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row">
  <!-- Main Content -->
  <div class="col-md-8">
    <!-- Journey Overview -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Journey Overview</h5>
      </div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <p class="text-muted mb-1">Worker</p>
            <p class="mb-0"><strong>{{ $journey->user->name }}</strong></p>
          </div>
          <div class="col-md-6">
            <p class="text-muted mb-1">Status</p>
            @php
              $statusColors = [
                'planned' => 'info',
                'active' => 'primary',
                'completed' => 'success',
                'overdue' => 'warning',
                'emergency' => 'danger',
                'cancelled' => 'secondary'
              ];
              $statusColor = $statusColors[$journey->status] ?? 'secondary';
            @endphp
            <span class="badge bg-{{ $statusColor }}">{{ ucfirst($journey->status) }}</span>
          </div>
        </div>

        @if($journey->vehicle)
        <div class="row mb-3">
          <div class="col-md-12">
            <p class="text-muted mb-1">Vehicle</p>
            <p class="mb-0">
              {{ $journey->vehicle->registration_number }} -
              {{ $journey->vehicle->make }} {{ $journey->vehicle->model }}
            </p>
          </div>
        </div>
        @endif

        @if($journey->purpose)
        <div class="row mb-3">
          <div class="col-md-12">
            <p class="text-muted mb-1">Purpose</p>
            <p class="mb-0">{{ $journey->purpose }}</p>
          </div>
        </div>
        @endif

        <div class="row mb-3">
          <div class="col-md-12">
            <p class="text-muted mb-1">Destination</p>
            <p class="mb-0"><strong>{{ $journey->destination }}</strong></p>
            @if($journey->destination_address)
            <p class="text-muted mb-0">{{ $journey->destination_address }}</p>
            @endif
          </div>
        </div>

        @if($journey->destination_latitude && $journey->destination_longitude)
        <div class="row mb-3">
          <div class="col-12">
            <p class="text-muted mb-1">Destination GPS Coordinates</p>
            <p class="mb-0">
              {{ $journey->destination_latitude }}, {{ $journey->destination_longitude }}
              <a href="https://www.google.com/maps?q={{ $journey->destination_latitude }},{{ $journey->destination_longitude }}"
                 target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                <i class='bx bx-map'></i> View on Map
              </a>
            </p>
          </div>
        </div>
        @endif

        <div class="row mb-3">
          <div class="col-md-6">
            <p class="text-muted mb-1">Planned Start</p>
            <p class="mb-0">{{ $journey->planned_start_time->format('d/m/Y H:i') }}</p>
            @if($journey->actual_start_time)
            <p class="text-success mb-0">
              <small><i class='bx bx-check-circle'></i> Started: {{ $journey->actual_start_time->format('d/m/Y H:i') }}</small>
            </p>
            @endif
          </div>
          <div class="col-md-6">
            <p class="text-muted mb-1">Planned End</p>
            <p class="mb-0">{{ $journey->planned_end_time->format('d/m/Y H:i') }}</p>
            @if($journey->actual_end_time)
            <p class="text-success mb-0">
              <small><i class='bx bx-check-circle'></i> Completed: {{ $journey->actual_end_time->format('d/m/Y H:i') }}</small>
            </p>
            @endif
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-4">
            <p class="text-muted mb-1">Estimated Distance</p>
            <p class="mb-0">{{ $journey->estimated_distance_km ? number_format($journey->estimated_distance_km, 1) . ' km' : 'Not specified' }}</p>
          </div>
          <div class="col-md-4">
            <p class="text-muted mb-1">Estimated Duration</p>
            <p class="mb-0">
              {{ $journey->estimated_duration_minutes ? floor($journey->estimated_duration_minutes / 60) . 'h ' . ($journey->estimated_duration_minutes % 60) . 'm' : 'Not specified' }}
            </p>
          </div>
          <div class="col-md-4">
            <p class="text-muted mb-1">Check-in Interval</p>
            <p class="mb-0">Every {{ $journey->checkin_interval_minutes }} minutes</p>
          </div>
        </div>

        @if($journey->actual_start_time && $journey->actual_end_time)
        <div class="row mb-3">
          <div class="col-md-12">
            <p class="text-muted mb-1">Actual Duration</p>
            <p class="mb-0">
              @php
                $actualMinutes = $journey->actual_start_time->diffInMinutes($journey->actual_end_time);
                $actualHours = floor($actualMinutes / 60);
                $remainingMinutes = $actualMinutes % 60;
              @endphp
              {{ $actualHours }}h {{ $remainingMinutes }}m
            </p>
          </div>
        </div>
        @endif

        @if($journey->notes)
        <div class="mb-0">
          <p class="text-muted mb-1">Journey Notes</p>
          <p class="mb-0">{{ $journey->notes }}</p>
        </div>
        @endif
      </div>
    </div>

    <!-- Risk Assessment -->
    @if($journey->hazards_identified || $journey->control_measures)
    <div class="card mb-4 border-warning">
      <div class="card-header bg-label-warning">
        <h5 class="mb-0"><i class='bx bx-shield-alt-2 me-2'></i>Risk Assessment</h5>
      </div>
      <div class="card-body">
        @if($journey->hazards_identified)
        <div class="mb-3">
          <p class="text-muted mb-1">Hazards Identified</p>
          <p class="mb-0">{{ $journey->hazards_identified }}</p>
        </div>
        @endif

        @if($journey->control_measures)
        <div class="mb-0">
          <p class="text-muted mb-1">Control Measures</p>
          <p class="mb-0">{{ $journey->control_measures }}</p>
        </div>
        @endif
      </div>
    </div>
    @endif

    <!-- Check-in History -->
    @if($journey->checkpoints && count($journey->checkpoints) > 0)
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Check-in History ({{ count($journey->checkpoints) }})</h5>
        <span class="badge bg-label-primary">{{ count($journey->checkpoints) }} Check-ins</span>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm">
            <thead>
              <tr>
                <th>Time</th>
                <th>Location</th>
                <th>Status</th>
                <th>Type</th>
                <th>Notes</th>
              </tr>
            </thead>
            <tbody>
              @foreach($journey->checkpoints as $checkpoint)
              <tr>
                <td>{{ $checkpoint->checkin_time->format('d/m/Y H:i') }}</td>
                <td>
                  @if($checkpoint->latitude && $checkpoint->longitude)
                  <a href="https://www.google.com/maps?q={{ $checkpoint->latitude }},{{ $checkpoint->longitude }}"
                     target="_blank" class="text-decoration-none">
                    <i class='bx bx-map-pin'></i>
                    {{ $checkpoint->location_name ?? 'GPS Location' }}
                  </a>
                  @else
                  {{ $checkpoint->location_name ?? 'N/A' }}
                  @endif
                </td>
                <td>
                  @php
                    $checkpointStatusColors = [
                      'ok' => 'success',
                      'assistance_needed' => 'warning',
                      'emergency' => 'danger'
                    ];
                    $checkpointColor = $checkpointStatusColors[$checkpoint->status] ?? 'secondary';
                  @endphp
                  <span class="badge badge-light-{{ $checkpointColor }}">{{ ucfirst(str_replace('_', ' ', $checkpoint->status)) }}</span>
                </td>
                <td>
                  <span class="badge badge-light-secondary">{{ ucfirst($checkpoint->type) }}</span>
                </td>
                <td>{{ $checkpoint->notes ?? '-' }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    @endif

    @if($journey->completion_notes)
    <div class="card mb-4">
      <div class="card-header bg-label-success">
        <h5 class="mb-0"><i class='bx bx-check-circle me-2'></i>Completion Notes</h5>
      </div>
      <div class="card-body">
        <p class="mb-0">{{ $journey->completion_notes }}</p>
      </div>
    </div>
    @endif
  </div>

  <!-- Sidebar -->
  <div class="col-md-4">
    <!-- Actions -->
    @if($journey->status !== 'completed' && $journey->status !== 'cancelled')
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Actions</h5>
      </div>
      <div class="card-body">
        @if($journey->status === 'planned')
        <form action="{{ route('journey.start', $journey) }}" method="POST" class="mb-3">
          @csrf
          <button type="submit" class="btn btn-info w-100">
            <i class='bx bx-play-circle'></i> Start Journey
          </button>
        </form>
        @endif

        @if($journey->status === 'active')
        <button type="button" class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#checkinModal">
          <i class='bx bx-map-pin'></i> Record Check-in
        </button>

        <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#completeModal">
          <i class='bx bx-check-circle'></i> Complete Journey
        </button>

        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#emergencyModal">
          <i class='bx bx-error-circle'></i> Request Emergency Assistance
        </button>
        @endif
      </div>
    </div>
    @endif

    <!-- Journey Details -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Journey Details</h5>
      </div>
      <div class="card-body">
        @if($journey->emergency_contact_name || $journey->emergency_contact_phone)
        <div class="mb-3">
          <p class="text-muted mb-1">Emergency Contact</p>
          <p class="mb-0">{{ $journey->emergency_contact_name ?? 'N/A' }}</p>
          @if($journey->emergency_contact_phone)
          <p class="mb-0">
            <a href="tel:{{ $journey->emergency_contact_phone }}" class="text-decoration-none">
              <i class='bx bx-phone'></i> {{ $journey->emergency_contact_phone }}
            </a>
          </p>
          @endif
        </div>
        @endif

        <div class="mb-3">
          <p class="text-muted mb-1">Created</p>
          <p class="mb-0">{{ $journey->created_at->format('d/m/Y H:i') }}</p>
        </div>

        <div class="mb-3">
          <p class="text-muted mb-1">Last Updated</p>
          <p class="mb-0">{{ $journey->updated_at->format('d/m/Y H:i') }}</p>
        </div>

        @if($journey->status === 'active' && $journey->last_checkin_time)
        <div class="mb-3">
          <p class="text-muted mb-1">Last Check-in</p>
          <p class="mb-0">{{ $journey->last_checkin_time->format('d/m/Y H:i') }}</p>
          <p class="text-muted mb-0"><small>{{ $journey->last_checkin_time->diffForHumans() }}</small></p>
        </div>

        <div class="mb-3">
          <p class="text-muted mb-1">Next Check-in Due</p>
          <p class="mb-0 {{ $journey->checkin_overdue ? 'text-danger' : '' }}">
            {{ $journey->next_checkin_due->format('d/m/Y H:i') }}
          </p>
          @if($journey->checkin_overdue)
          <p class="text-danger mb-0"><small><i class='bx bx-error-circle'></i> Overdue</small></p>
          @else
          <p class="text-muted mb-0"><small>{{ $journey->next_checkin_due->diffForHumans() }}</small></p>
          @endif
        </div>
        @endif
      </div>
    </div>

    <!-- Timeline -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Timeline</h5>
      </div>
      <div class="card-body">
        <ul class="timeline">
          <li class="timeline-item timeline-item-transparent">
            <span class="timeline-point timeline-point-primary"></span>
            <div class="timeline-event">
              <div class="timeline-header mb-1">
                <h6 class="mb-0">Journey Planned</h6>
                <small class="text-muted">{{ $journey->created_at->format('d/m/Y H:i') }}</small>
              </div>
              <p class="mb-0">By {{ $journey->user->name }}</p>
            </div>
          </li>

          @if($journey->actual_start_time)
          <li class="timeline-item timeline-item-transparent">
            <span class="timeline-point timeline-point-info"></span>
            <div class="timeline-event">
              <div class="timeline-header mb-1">
                <h6 class="mb-0">Journey Started</h6>
                <small class="text-muted">{{ $journey->actual_start_time->format('d/m/Y H:i') }}</small>
              </div>
              <p class="mb-0">Worker began journey</p>
            </div>
          </li>
          @endif

          @foreach($journey->checkpoints ?? [] as $checkpoint)
          <li class="timeline-item timeline-item-transparent">
            <span class="timeline-point timeline-point-{{ $checkpoint->status === 'ok' ? 'success' : ($checkpoint->status === 'emergency' ? 'danger' : 'warning') }}"></span>
            <div class="timeline-event">
              <div class="timeline-header mb-1">
                <h6 class="mb-0">Check-in: {{ ucfirst(str_replace('_', ' ', $checkpoint->status)) }}</h6>
                <small class="text-muted">{{ $checkpoint->checkin_time->format('d/m/Y H:i') }}</small>
              </div>
              <p class="mb-0">{{ $checkpoint->location_name ?? 'Location recorded' }}</p>
            </div>
          </li>
          @endforeach

          @if($journey->actual_end_time)
          <li class="timeline-item timeline-item-transparent">
            <span class="timeline-point timeline-point-success"></span>
            <div class="timeline-event">
              <div class="timeline-header mb-1">
                <h6 class="mb-0">Journey Completed</h6>
                <small class="text-muted">{{ $journey->actual_end_time->format('d/m/Y H:i') }}</small>
              </div>
              <p class="mb-0">Worker returned safely</p>
            </div>
          </li>
          @endif
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- Check-in Modal -->
@if($journey->status === 'active')
<div class="modal fade" id="checkinModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Record Check-in</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('journey.checkin', $journey) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">GPS Location</label>
            <button type="button" class="btn btn-sm btn-outline-primary w-100" id="captureGPS">
              <i class='bx bx-current-location'></i> Capture Current Location
            </button>
            <input type="hidden" name="latitude" id="checkin_latitude">
            <input type="hidden" name="longitude" id="checkin_longitude">
            <small class="text-muted" id="gpsStatus"></small>
          </div>

          <div class="mb-3">
            <label for="location_name" class="form-label">Location Name</label>
            <input type="text" class="form-control" id="location_name" name="location_name" placeholder="e.g., Client site, Rest area">
          </div>

          <div class="mb-3">
            <label for="status" class="form-label">Status *</label>
            <select class="form-select" id="status" name="status" required>
              <option value="ok" selected>All OK - Proceeding as planned</option>
              <option value="assistance_needed">Minor Issue - Assistance may be needed</option>
              <option value="emergency">Emergency - Immediate help required</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any updates or concerns..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit Check-in</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Complete Journey Modal -->
<div class="modal fade" id="completeModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Complete Journey</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('journey.complete', $journey) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="alert alert-success">
            <i class='bx bx-check-circle me-2'></i>
            Mark this journey as completed and confirm safe return.
          </div>

          <div class="mb-3">
            <label for="completion_notes" class="form-label">Completion Notes</label>
            <textarea class="form-control" id="completion_notes" name="completion_notes" rows="3" placeholder="Any final notes or observations..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Complete Journey</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Emergency Modal -->
<div class="modal fade" id="emergencyModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
          <i class='bx bx-error-circle me-2'></i>Request Emergency Assistance
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('journey.emergency', $journey) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="alert alert-danger">
            <strong>Emergency assistance will be requested immediately!</strong><br>
            Emergency contacts and supervisors will be notified of your location and situation.
          </div>

          <div class="mb-3">
            <label for="emergency_notes" class="form-label">What's the emergency? *</label>
            <textarea class="form-control" id="emergency_notes" name="notes" rows="4" placeholder="Describe the emergency situation..." required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Request Emergency Help</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif
@endsection

@section('page-script')
<script>
// GPS Capture for Check-in
@if($journey->status === 'active')
document.getElementById('captureGPS')?.addEventListener('click', function() {
  const btn = this;
  const statusEl = document.getElementById('gpsStatus');

  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Capturing...';
  statusEl.textContent = 'Acquiring GPS location...';

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      function(position) {
        document.getElementById('checkin_latitude').value = position.coords.latitude.toFixed(6);
        document.getElementById('checkin_longitude').value = position.coords.longitude.toFixed(6);

        btn.disabled = false;
        btn.innerHTML = '<i class="bx bx-check-circle me-1"></i> Location Captured';
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-success');
        statusEl.textContent = `Location: ${position.coords.latitude.toFixed(6)}, ${position.coords.longitude.toFixed(6)}`;
        statusEl.classList.add('text-success');

        if (typeof toastr !== 'undefined') {
          toastr.success('GPS location captured successfully');
        }
      },
      function(error) {
        let errorMsg = 'Could not capture location';
        switch(error.code) {
          case error.PERMISSION_DENIED:
            errorMsg = 'Location permission denied';
            break;
          case error.POSITION_UNAVAILABLE:
            errorMsg = 'Location unavailable';
            break;
          case error.TIMEOUT:
            errorMsg = 'Location request timed out';
            break;
        }

        btn.disabled = false;
        btn.innerHTML = '<i class="bx bx-current-location"></i> Capture Current Location';
        statusEl.textContent = errorMsg;
        statusEl.classList.add('text-danger');

        if (typeof toastr !== 'undefined') {
          toastr.error(errorMsg);
        }
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      }
    );
  } else {
    btn.disabled = false;
    btn.innerHTML = '<i class="bx bx-current-location"></i> Capture Current Location';
    statusEl.textContent = 'Geolocation not supported';
    statusEl.classList.add('text-danger');

    if (typeof toastr !== 'undefined') {
      toastr.error('Geolocation not supported by this browser');
    }
  }
});

// Auto-capture GPS when check-in modal opens
document.getElementById('checkinModal')?.addEventListener('shown.bs.modal', function() {
  // Auto-trigger GPS capture after a short delay
  setTimeout(function() {
    document.getElementById('captureGPS')?.click();
  }, 500);
});
@endif
</script>
@endsection
