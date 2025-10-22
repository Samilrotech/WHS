@extends('layouts.layoutMaster')

@section('title', 'Edit Inspection')

@section('content')
@include('layouts.sections.flash-message')

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Inspection: {{ $inspection->inspection_number }}</h5>
        <a href="{{ route('inspections.show', $inspection) }}" class="btn btn-sm btn-outline-secondary">
          <i class="bx bx-arrow-back me-1"></i> Back to Inspection
        </a>
      </div>
      <div class="card-body">
        <form action="{{ route('inspections.update', $inspection) }}" method="POST" class="needs-validation" novalidate>
          @csrf
          @method('PUT')

          <h6 class="mb-3">Inspection Details</h6>

          <div class="row">
            <!-- Vehicle (read-only) -->
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Vehicle</label>
                <input type="text" class="form-control" value="{{ $inspection->vehicle->registration_number }} - {{ $inspection->vehicle->make }} {{ $inspection->vehicle->model }}" readonly disabled>
                <small class="text-muted">Vehicle cannot be changed after inspection creation</small>
              </div>
            </div>

            <!-- Inspection Type -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="inspection_type" class="form-label">Inspection Type *</label>
                <select id="inspection_type" name="inspection_type" class="form-select @error('inspection_type') is-invalid @enderror" required>
                  <option value="monthly_routine" {{ old('inspection_type', $inspection->inspection_type) === 'monthly_routine' ? 'selected' : '' }}>Monthly Routine</option>
                  <option value="pre_trip" {{ old('inspection_type', $inspection->inspection_type) === 'pre_trip' ? 'selected' : '' }}>Pre-Trip</option>
                  <option value="post_incident" {{ old('inspection_type', $inspection->inspection_type) === 'post_incident' ? 'selected' : '' }}>Post-Incident</option>
                  <option value="annual_compliance" {{ old('inspection_type', $inspection->inspection_type) === 'annual_compliance' ? 'selected' : '' }}>Annual Compliance</option>
                  <option value="maintenance_followup" {{ old('inspection_type', $inspection->inspection_type) === 'maintenance_followup' ? 'selected' : '' }}>Maintenance Follow-up</option>
                  <option value="random_spot_check" {{ old('inspection_type', $inspection->inspection_type) === 'random_spot_check' ? 'selected' : '' }}>Random Spot Check</option>
                </select>
                @error('inspection_type')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Inspection Date -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="inspection_date" class="form-label">Inspection Date</label>
                <input
                  type="date"
                  id="inspection_date"
                  name="inspection_date"
                  class="form-control @error('inspection_date') is-invalid @enderror"
                  value="{{ old('inspection_date', $inspection->inspection_date?->format('Y-m-d')) }}">
                @error('inspection_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- Odometer Reading -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="odometer_reading" class="form-label">Odometer Reading (km)</label>
                <input
                  type="number"
                  id="odometer_reading"
                  name="odometer_reading"
                  class="form-control @error('odometer_reading') is-invalid @enderror"
                  value="{{ old('odometer_reading', $inspection->odometer_reading) }}"
                  min="0">
                @error('odometer_reading')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Location -->
            <div class="col-md-12">
              <div class="mb-3">
                <label for="location" class="form-label">Inspection Location</label>
                <input
                  type="text"
                  id="location"
                  name="location"
                  class="form-control @error('location') is-invalid @enderror"
                  value="{{ old('location', $inspection->location) }}"
                  placeholder="e.g., Main Depot, Customer Site, Roadside">
                @error('location')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Inspector Notes -->
            <div class="col-md-12">
              <div class="mb-3">
                <label for="inspector_notes" class="form-label">Inspector Notes</label>
                <textarea
                  id="inspector_notes"
                  name="inspector_notes"
                  class="form-control @error('inspector_notes') is-invalid @enderror"
                  rows="3"
                  placeholder="Any additional notes about this inspection">{{ old('inspector_notes', $inspection->inspector_notes) }}</textarea>
                @error('inspector_notes')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <!-- Warning Alert -->
          <div class="alert alert-warning" role="alert">
            <h6 class="alert-heading">
              <i class="bx bx-info-circle me-1"></i> Important
            </h6>
            <ul class="mb-0">
              <li>Only basic inspection details can be edited (type, date, location, notes)</li>
              <li>Checklist items must be updated from the inspection detail page</li>
              <li>Vehicle cannot be changed after inspection creation</li>
              <li>Approved inspections cannot be edited</li>
            </ul>
          </div>

          <!-- Form Actions -->
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="bx bx-save me-1"></i> Update Inspection
            </button>
            <a href="{{ route('inspections.show', $inspection) }}" class="btn btn-outline-secondary">
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
