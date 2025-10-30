@extends('layouts.layoutMaster')

@section('title', 'Create Inspection')

@php
  $selectedVehicleId = $selectedVehicleId ?? null;
  $selectedInspectionType = $selectedInspectionType ?? null;
  $prefillOdometer = $selectedVehicle['odometer_reading'] ?? null;
@endphp

@section('content')
@include('layouts.sections.flash-message')

<div class="row inspection-create-page">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Create New Inspection</h5>
        <a href="{{ route('inspections.index') }}" class="btn btn-sm btn-outline-secondary">
          <i class="bx bx-arrow-back me-1"></i> Back to List
        </a>
      </div>
      <div class="card-body">
        <form action="{{ route('inspections.store') }}" method="POST" class="needs-validation" novalidate>
          @csrf

          <h6 class="mb-3">Inspection Details</h6>

          <div class="row">
            <!-- Vehicle Selection -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="vehicle_id" class="form-label">Select Vehicle *</label>
                <select
                  id="vehicle_id"
                  name="vehicle_id"
                  class="form-select @error('vehicle_id') is-invalid @enderror"
                  required
                  onchange="updateVehicleInfo()">
                  <option value="">Select vehicle</option>
                  @foreach($vehicles as $vehicle)
                    <option
                      value="{{ $vehicle['id'] }}" {{ old('vehicle_id', $selectedVehicleId) == $vehicle['id'] ? 'selected' : '' }}
                      data-make="{{ $vehicle['make'] }}"
                      data-model="{{ $vehicle['model'] }}"
                      data-year="{{ $vehicle['year'] }}"
                      data-odometer="{{ $vehicle['odometer_reading'] }}"
                      data-inspection-due="{{ $vehicle['inspection_due'] ? 'true' : 'false' }}"
                      data-last-inspection="{{ $vehicle['last_inspection_date'] ? $vehicle['last_inspection_date']->format('d/m/Y') : 'Never' }}"
                      {{ old('vehicle_id') === $vehicle['id'] ? 'selected' : '' }}>
                      {{ $vehicle['registration_number'] }} - {{ $vehicle['make'] }} {{ $vehicle['model'] }} ({{ $vehicle['year'] }})
                    </option>
                  @endforeach
                </select>
                @error('vehicle_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- Inspection Type -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="inspection_type" class="form-label">Inspection Type *</label>
                <select id="inspection_type" name="inspection_type" class="form-select @error('inspection_type') is-invalid @enderror" required>
                  <option value="">Select type</option>
                  <option value="monthly_routine" {{ old('inspection_type', $selectedInspectionType) === 'monthly_routine' ? 'selected' : '' }}>Monthly Routine</option>
                  <option value="pre_trip" {{ old('inspection_type', $selectedInspectionType) === 'pre_trip' ? 'selected' : '' }}>Pre-Trip</option>
                  <option value="post_incident" {{ old('inspection_type', $selectedInspectionType) === 'post_incident' ? 'selected' : '' }}>Post-Incident</option>
                  <option value="annual_compliance" {{ old('inspection_type', $selectedInspectionType) === 'annual_compliance' ? 'selected' : '' }}>Annual Compliance</option>
                  <option value="maintenance_followup" {{ old('inspection_type', $selectedInspectionType) === 'maintenance_followup' ? 'selected' : '' }}>Maintenance Follow-up</option>
                  <option value="random_spot_check" {{ old('inspection_type', $selectedInspectionType) === 'random_spot_check' ? 'selected' : '' }}>Random Spot Check</option>
                </select>
                @error('inspection_type')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <!-- Vehicle Information Display (Dynamic) -->
          <div id="vehicleInfoCard" class="alert alert-info d-none mb-3" role="alert">
            <h6 class="alert-heading mb-2">Vehicle Information</h6>
            <div class="row">
              <div class="col-md-3">
                <strong>Vehicle:</strong>
                <p class="mb-0" id="vehicleDisplay">-</p>
              </div>
              <div class="col-md-3">
                <strong>Current Odometer:</strong>
                <p class="mb-0" id="odometerDisplay">-</p>
              </div>
              <div class="col-md-3">
                <strong>Last Inspection:</strong>
                <p class="mb-0" id="lastInspectionDisplay">-</p>
              </div>
              <div class="col-md-3">
                <strong>Status:</strong>
                <p class="mb-0" id="inspectionDueDisplay">-</p>
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
                  value="{{ old('inspection_date', now()->format('Y-m-d')) }}">
                @error('inspection_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Leave blank to set on inspection start</small>
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
                  value="{{ old('odometer_reading', $prefillOdometer) }}"
                  min="0">
                @error('odometer_reading')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Current odometer reading at inspection time</small>
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
                  value="{{ old('location') }}"
                  placeholder="e.g., Main Depot, Customer Site, Roadside">
                @error('location')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <!-- Information Box -->
          <div class="alert alert-primary inspection-create-info" role="alert">
            <h6 class="alert-heading">
              <i class="bx bx-info-circle me-1"></i> What Happens Next
            </h6>
            <ul class="mb-0">
              <li>A standard checklist with {{ '~35' }} items will be automatically created based on inspection type</li>
              <li>After creation, you can start the inspection and mark each item as Pass, Fail, or N/A</li>
              <li>Failed items will require defect notes and repair recommendations</li>
              <li>Once all items are checked, you can complete and submit for approval</li>
            </ul>
          </div>

          <!-- Form Actions -->
          <div class="d-flex gap-2 inspection-create-actions">
            <button type="submit" class="btn btn-primary">
              <i class="bx bx-clipboard me-1"></i> Create Inspection
            </button>
            <a href="{{ route('inspections.index') }}" class="btn btn-outline-secondary">
              Cancel
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@push('page-style')
<style>
  .inspection-create-page .card {
    border-radius: 1rem;
  }

  .inspection-create-page .card-header {
    gap: 0.75rem;
  }

  .inspection-create-page .card-header .btn {
    min-height: 42px;
  }

  .inspection-create-page .form-control,
  .inspection-create-page .form-select {
    min-height: 46px;
    border-radius: 0.85rem;
  }

  .inspection-create-info {
    border-radius: 1rem;
    border: 1px solid rgba(59, 130, 246, 0.3);
  }

  .inspection-create-actions .btn {
    min-height: 48px;
    align-items: center;
    display: inline-flex;
    justify-content: center;
    border-radius: 0.85rem;
  }

  @media (max-width: 767.98px) {
    .inspection-create-page .card-header {
      flex-direction: column;
      align-items: flex-start;
      padding: 1.25rem 1.25rem 0.75rem;
    }

    .inspection-create-page .card-body {
      padding: 1.25rem;
    }

    .inspection-create-actions {
      flex-direction: column;
    }

    .inspection-create-actions .btn {
      width: 100%;
    }

    #vehicleInfoCard .col-md-3 {
      flex: 0 0 100%;
      max-width: 100%;
      margin-bottom: 0.75rem;
    }

    #vehicleInfoCard .col-md-3:last-child {
      margin-bottom: 0;
    }
  }
</style>
@endpush

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

// Update vehicle information display
function updateVehicleInfo() {
  const select = document.getElementById('vehicle_id');
  const selectedOption = select.options[select.selectedIndex];
  const infoCard = document.getElementById('vehicleInfoCard');

  if (selectedOption.value) {
    // Get data attributes
    const make = selectedOption.getAttribute('data-make');
    const model = selectedOption.getAttribute('data-model');
    const year = selectedOption.getAttribute('data-year');
    const odometer = selectedOption.getAttribute('data-odometer');
    const lastInspection = selectedOption.getAttribute('data-last-inspection');
    const inspectionDue = selectedOption.getAttribute('data-inspection-due') === 'true';

    // Update display
    document.getElementById('vehicleDisplay').textContent = `${make} ${model} (${year})`;
    document.getElementById('odometerDisplay').textContent = `${parseInt(odometer).toLocaleString()} km`;
    document.getElementById('lastInspectionDisplay').textContent = lastInspection;

    // Update inspection due status
    const dueDisplay = document.getElementById('inspectionDueDisplay');
    if (inspectionDue) {
      dueDisplay.innerHTML = '<span class="badge bg-danger">Overdue</span>';
    } else if (lastInspection === 'Never') {
      dueDisplay.innerHTML = '<span class="badge bg-warning">No History</span>';
    } else {
      dueDisplay.innerHTML = '<span class="badge bg-success">Up to Date</span>';
    }

    // Auto-fill odometer if not already filled
    const odometerInput = document.getElementById('odometer_reading');
    if (!odometerInput.value) {
      odometerInput.value = odometer;
    }

    // Show info card
    infoCard.classList.remove('d-none');
  } else {
    // Hide info card
    infoCard.classList.add('d-none');
  }
}

// Initialize vehicle info on page load if vehicle is pre-selected
window.addEventListener('load', function() {
  const vehicleSelect = document.getElementById('vehicle_id');
  if (vehicleSelect.value) {
    updateVehicleInfo();
  }
});
</script>
@endsection
@endsection

