@extends('layouts.layoutMaster')

@section('title', 'Report New Incident')

@section('content')
@include('layouts.sections.flash-message')

<h4 class="mb-4">Report New Incident</h4>

<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body">
        <form action="{{ route('incidents.store') }}" method="POST" enctype="multipart/form-data" id="incidentForm">
          @csrf

          <!-- Incident Type & Severity -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="type" class="form-label">Incident Type *</label>
              <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                <option value="">Select type</option>
                <option value="injury" {{ old('type') === 'injury' ? 'selected' : '' }}>Injury</option>
                <option value="near-miss" {{ old('type') === 'near-miss' ? 'selected' : '' }}>Near Miss</option>
                <option value="property-damage" {{ old('type') === 'property-damage' ? 'selected' : '' }}>Property Damage</option>
                <option value="environmental" {{ old('type') === 'environmental' ? 'selected' : '' }}>Environmental</option>
                <option value="security" {{ old('type') === 'security' ? 'selected' : '' }}>Security</option>
              </select>
              @error('type')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="severity" class="form-label">Severity *</label>
              <select id="severity" name="severity" class="form-select @error('severity') is-invalid @enderror" required>
                <option value="">Select severity</option>
                <option value="low" {{ old('severity') === 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ old('severity') === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ old('severity') === 'high' ? 'selected' : '' }}>High</option>
                <option value="critical" {{ old('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
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
                     value="{{ old('incident_datetime', now()->format('Y-m-d\TH:i')) }}" required>
              @error('incident_datetime')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="location_branch" class="form-label">Branch Location *</label>
              <input type="text" id="location_branch" name="location_branch"
                     class="form-control @error('location_branch') is-invalid @enderror"
                     value="{{ old('location_branch', auth()->user()->branch->name) }}" required>
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
                   value="{{ old('location_specific') }}" required>
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
                     value="{{ old('gps_latitude') }}" readonly>
              @error('gps_latitude')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6">
              <label for="gps_longitude" class="form-label">GPS Longitude</label>
              <input type="text" id="gps_longitude" name="gps_longitude"
                     class="form-control @error('gps_longitude') is-invalid @enderror"
                     value="{{ old('gps_longitude') }}" readonly>
              <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="captureLocation">
                <i class='bx bx-current-location'></i> Capture Current Location
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
                      placeholder="Provide detailed description of what happened..." required>{{ old('description') }}</textarea>
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
                      placeholder="What immediate actions were taken?">{{ old('immediate_actions') }}</textarea>
            @error('immediate_actions')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Photo Upload -->
          <div class="mb-3">
            <label for="photos" class="form-label">Photos (Max 10 files, 10MB each)</label>
            <input type="file" id="photos" name="photos[]" class="form-control" multiple accept="image/*" capture="environment">
            <div class="form-text">Recommended: Take photos of incident scene, injuries, equipment involved</div>
            <div id="photoPreview" class="row mt-2"></div>
          </div>

          <!-- Emergency Flags -->
          <div class="row mb-3">
            <div class="col-md-6">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="requires_emergency" name="requires_emergency" value="1"
                       {{ old('requires_emergency') ? 'checked' : '' }}>
                <label class="form-check-label" for="requires_emergency">
                  Requires Emergency Response
                </label>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="notify_authorities" name="notify_authorities" value="1"
                       {{ old('notify_authorities') ? 'checked' : '' }}>
                <label class="form-check-label" for="notify_authorities">
                  Notify Authorities
                </label>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="d-flex justify-content-between">
            <a href="{{ route('incidents.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <div>
              <button type="button" class="btn btn-outline-primary me-2" id="saveOffline">
                <i class='bx bx-cloud-download'></i> Save Offline
              </button>
              <button type="submit" class="btn btn-primary">
                <i class='bx bx-check'></i> Submit Report
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
<script src="https://unpkg.com/dexie@latest/dist/dexie.js"></script>
<script>
// GPS Location Capture
document.getElementById('captureLocation').addEventListener('click', function() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
      document.getElementById('gps_latitude').value = position.coords.latitude;
      document.getElementById('gps_longitude').value = position.coords.longitude;
      toastr.success('Location captured successfully');
    }, function(error) {
      toastr.error('Could not capture location: ' + error.message);
    });
  } else {
    toastr.error('Geolocation not supported by browser');
  }
});

// Photo Preview
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

// Offline Storage with IndexedDB
const db = new Dexie('WHSIncidents');
db.version(1).stores({
  incidents: '++id, timestamp, synced'
});

document.getElementById('saveOffline').addEventListener('click', async function() {
  const form = document.getElementById('incidentForm');
  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());

  await db.incidents.add({
    ...data,
    timestamp: Date.now(),
    synced: false
  });

  toastr.success('Incident saved offline. Will sync when online.');
  form.reset();
});

// Background sync when online
window.addEventListener('online', async function() {
  const unsynced = await db.incidents.where('synced').equals(false).toArray();

  for (const incident of unsynced) {
    try {
      await fetch('{{ route("incidents.store") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(incident)
      });

      await db.incidents.update(incident.id, { synced: true });
      toastr.success('Offline incident synced successfully');
    } catch (error) {
      console.error('Sync failed:', error);
    }
  }
});
</script>
@endsection
