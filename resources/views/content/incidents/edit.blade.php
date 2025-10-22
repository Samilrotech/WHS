@extends('layouts.layoutMaster')

@section('title', 'Edit Incident #' . $incident->id)

@section('content')
@include('layouts.sections.flash-message')

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0">Edit Incident #{{ $incident->id }}</h4>
  <a href="{{ route('incidents.show', $incident) }}" class="btn btn-outline-secondary">
    <i class='bx bx-arrow-back'></i> Back
  </a>
</div>

<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body">
        <form action="{{ route('incidents.update', $incident) }}" method="POST" enctype="multipart/form-data" id="incidentForm">
          @csrf
          @method('PUT')

          <!-- Incident Type & Severity -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="type" class="form-label">Incident Type *</label>
              <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                <option value="">Select type</option>
                <option value="injury" {{ old('type', $incident->type) === 'injury' ? 'selected' : '' }}>Injury</option>
                <option value="near-miss" {{ old('type', $incident->type) === 'near-miss' ? 'selected' : '' }}>Near Miss</option>
                <option value="property-damage" {{ old('type', $incident->type) === 'property-damage' ? 'selected' : '' }}>Property Damage</option>
                <option value="environmental" {{ old('type', $incident->type) === 'environmental' ? 'selected' : '' }}>Environmental</option>
                <option value="security" {{ old('type', $incident->type) === 'security' ? 'selected' : '' }}>Security</option>
              </select>
              @error('type')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="severity" class="form-label">Severity *</label>
              <select id="severity" name="severity" class="form-select @error('severity') is-invalid @enderror" required>
                <option value="">Select severity</option>
                <option value="low" {{ old('severity', $incident->severity) === 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ old('severity', $incident->severity) === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ old('severity', $incident->severity) === 'high' ? 'selected' : '' }}>High</option>
                <option value="critical" {{ old('severity', $incident->severity) === 'critical' ? 'selected' : '' }}>Critical</option>
              </select>
              @error('severity')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Date/Time & Location -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="incident_datetime" class="form-label">Date & Time *</label>
              <input type="datetime-local" id="incident_datetime" name="incident_datetime"
                     class="form-control @error('incident_datetime') is-invalid @enderror"
                     value="{{ old('incident_datetime', $incident->incident_datetime->format('Y-m-d\TH:i')) }}" required>
              @error('incident_datetime')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="location_branch" class="form-label">Branch Location *</label>
              <input type="text" id="location_branch" name="location_branch"
                     class="form-control @error('location_branch') is-invalid @enderror"
                     value="{{ old('location_branch', $incident->location_branch) }}" required>
              @error('location_branch')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="mb-3">
            <label for="location_specific" class="form-label">Specific Location *</label>
            <input type="text" id="location_specific" name="location_specific"
                   class="form-control @error('location_specific') is-invalid @enderror"
                   placeholder="e.g., Warehouse Bay 3, Production Floor"
                   value="{{ old('location_specific', $incident->location_specific) }}" required>
            @error('location_specific')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- GPS Coordinates -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="gps_latitude" class="form-label">GPS Latitude</label>
              <input type="text" id="gps_latitude" name="gps_latitude"
                     class="form-control @error('gps_latitude') is-invalid @enderror"
                     value="{{ old('gps_latitude', $incident->gps_latitude) }}" readonly>
              @error('gps_latitude')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="gps_longitude" class="form-label">GPS Longitude</label>
              <input type="text" id="gps_longitude" name="gps_longitude"
                     class="form-control @error('gps_longitude') is-invalid @enderror"
                     value="{{ old('gps_longitude', $incident->gps_longitude) }}" readonly>
              <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="captureLocation">
                <i class='bx bx-current-location'></i> Update Location
              </button>
              @error('gps_longitude')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Description -->
          <div class="mb-3">
            <label for="description" class="form-label">Incident Description *</label>
            <textarea id="description" name="description" rows="4"
                      class="form-control @error('description') is-invalid @enderror"
                      placeholder="Provide detailed description of what happened..." required>{{ old('description', $incident->description) }}</textarea>
            <div class="form-text">Minimum 20 characters required</div>
            @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Immediate Actions -->
          <div class="mb-3">
            <label for="immediate_actions" class="form-label">Immediate Actions Taken</label>
            <textarea id="immediate_actions" name="immediate_actions" rows="3"
                      class="form-control @error('immediate_actions') is-invalid @enderror"
                      placeholder="What immediate actions were taken?">{{ old('immediate_actions', $incident->immediate_actions) }}</textarea>
            @error('immediate_actions')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Existing Photos -->
          @if($incident->photos && count($incident->photos) > 0)
          <div class="mb-3">
            <label class="form-label">Existing Photos</label>
            <div class="row">
              @foreach($incident->photos as $photo)
              <div class="col-md-3 mb-2">
                <div class="position-relative">
                  <img src="{{ $photo->url }}" class="img-thumbnail" alt="Incident photo">
                  <form action="{{ route('incidents.deletePhoto', $photo) }}" method="POST" class="position-absolute top-0 end-0 m-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this photo?')">
                      <i class='bx bx-trash bx-xs'></i>
                    </button>
                  </form>
                </div>
              </div>
              @endforeach
            </div>
          </div>
          @endif

          <!-- Add New Photos -->
          <div class="mb-3">
            <label for="photos" class="form-label">Add More Photos (Max 10 files, 10MB each)</label>
            <input type="file" id="photos" name="photos[]" class="form-control" multiple accept="image/*" capture="environment">
            <div class="form-text">Add additional photos to this incident</div>
            <div id="photoPreview" class="row mt-2"></div>
          </div>

          <!-- Emergency Flags -->
          <div class="row mb-3">
            <div class="col-md-6">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="requires_emergency" name="requires_emergency" value="1"
                       {{ old('requires_emergency', $incident->requires_emergency) ? 'checked' : '' }}>
                <label class="form-check-label" for="requires_emergency">
                  Requires Emergency Response
                </label>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="notify_authorities" name="notify_authorities" value="1"
                       {{ old('notify_authorities', $incident->notify_authorities) ? 'checked' : '' }}>
                <label class="form-check-label" for="notify_authorities">
                  Notify Authorities
                </label>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="d-flex justify-content-between">
            <a href="{{ route('incidents.show', $incident) }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
              <i class='bx bx-check'></i> Update Report
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
// GPS Location Capture
document.getElementById('captureLocation').addEventListener('click', function() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
      document.getElementById('gps_latitude').value = position.coords.latitude;
      document.getElementById('gps_longitude').value = position.coords.longitude;
      toastr.success('Location updated successfully');
    }, function(error) {
      toastr.error('Could not capture location: ' + error.message);
    });
  } else {
    toastr.error('Geolocation not supported by browser');
  }
});

// Photo Preview for new uploads
document.getElementById('photos').addEventListener('change', function(e) {
  const preview = document.getElementById('photoPreview');
  preview.innerHTML = '';

  if (e.target.files.length > 10) {
    toastr.warning('Maximum 10 photos allowed');
    e.target.value = '';
    return;
  }

  Array.from(e.target.files).forEach(file => {
    if (file.size > 10 * 1024 * 1024) {
      toastr.warning(file.name + ' exceeds 10MB limit');
      return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
      const col = document.createElement('div');
      col.className = 'col-md-3 mb-2';
      col.innerHTML = `<img src="${e.target.result}" class="img-thumbnail">`;
      preview.appendChild(col);
    };
    reader.readAsDataURL(file);
  });
});
</script>
@endsection
