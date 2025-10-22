@extends('layouts.layoutMaster')

@section('title', 'Edit Emergency Alert')

@section('content')
@include('layouts.sections.flash-message')

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Emergency Alert #{{ substr($alert->id, 0, 8) }}</h5>
        <a href="{{ route('emergency.show', $alert) }}" class="btn btn-sm btn-outline-secondary">
          <i class='icon-base ti ti-arrow-left me-1'></i> Back
        </a>
      </div>
      <div class="card-body">
        <form action="{{ route('emergency.update', $alert) }}" method="POST" class="needs-validation" novalidate>
          @csrf
          @method('PUT')

          <!-- Emergency Type -->
          <div class="mb-3">
            <label for="type" class="form-label">Emergency Type *</label>
            <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
              <option value="">Select type</option>
              <option value="panic" {{ old('type', $alert->type) === 'panic' ? 'selected' : '' }}>Panic Button</option>
              <option value="medical" {{ old('type', $alert->type) === 'medical' ? 'selected' : '' }}>Medical Emergency</option>
              <option value="fire" {{ old('type', $alert->type) === 'fire' ? 'selected' : '' }}>Fire</option>
              <option value="evacuation" {{ old('type', $alert->type) === 'evacuation' ? 'selected' : '' }}>Evacuation</option>
              <option value="other" {{ old('type', $alert->type) === 'other' ? 'selected' : '' }}>Other Emergency</option>
            </select>
            @error('type')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Status -->
          <div class="mb-3">
            <label for="status" class="form-label">Status *</label>
            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
              <option value="">Select status</option>
              <option value="triggered" {{ old('status', $alert->status) === 'triggered' ? 'selected' : '' }}>Active</option>
              <option value="responded" {{ old('status', $alert->status) === 'responded' ? 'selected' : '' }}>Responded</option>
              <option value="resolved" {{ old('status', $alert->status) === 'resolved' ? 'selected' : '' }}>Resolved</option>
              <option value="cancelled" {{ old('status', $alert->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            @error('status')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Location Information -->
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="latitude" class="form-label">Latitude</label>
                <input
                  type="text"
                  id="latitude"
                  name="latitude"
                  class="form-control @error('latitude') is-invalid @enderror"
                  value="{{ old('latitude', $alert->latitude) }}"
                  placeholder="-90 to 90">
                @error('latitude')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="longitude" class="form-label">Longitude</label>
                <input
                  type="text"
                  id="longitude"
                  name="longitude"
                  class="form-control @error('longitude') is-invalid @enderror"
                  value="{{ old('longitude', $alert->longitude) }}"
                  placeholder="-180 to 180">
                @error('longitude')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <!-- Location Description -->
          <div class="mb-3">
            <label for="location_description" class="form-label">Location Description</label>
            <input
              type="text"
              id="location_description"
              name="location_description"
              class="form-control @error('location_description') is-invalid @enderror"
              value="{{ old('location_description', $alert->location_description) }}"
              maxlength="500"
              placeholder="e.g., Warehouse A, Bay 3">
            @error('location_description')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Additional Details -->
          <div class="mb-3">
            <label for="description" class="form-label">Additional Details</label>
            <textarea
              id="description"
              name="description"
              class="form-control @error('description') is-invalid @enderror"
              rows="4"
              maxlength="2000"
              placeholder="Provide any additional information...">{{ old('description', $alert->description) }}</textarea>
            @error('description')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Response Notes (only if responded/resolved) -->
          @if(in_array($alert->status, ['responded', 'resolved']))
            <div class="mb-3">
              <label for="response_notes" class="form-label">Response Notes</label>
              <textarea
                id="response_notes"
                name="response_notes"
                class="form-control @error('response_notes') is-invalid @enderror"
                rows="4"
                maxlength="2000"
                placeholder="Describe response actions taken...">{{ old('response_notes', $alert->response_notes) }}</textarea>
              @error('response_notes')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          @endif

          <!-- Form Actions -->
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class='icon-base ti ti-device-floppy me-1'></i> Update Alert
            </button>
            <a href="{{ route('emergency.show', $alert) }}" class="btn btn-outline-secondary">
              Cancel
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@section('page-script')
<script>
// Bootstrap form validation
(function() {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();
</script>
@endsection
@endsection

