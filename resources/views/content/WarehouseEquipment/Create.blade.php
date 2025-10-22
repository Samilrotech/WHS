@extends('layouts/layoutMaster')

@section('title', 'Add Warehouse Equipment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-1">Add New Equipment</h4>
    <p class="mb-0">Register new warehouse equipment with QR code generation</p>
  </div>
  <div>
    <a href="{{ route('warehouse-equipment.index') }}" class="btn btn-outline-secondary">
      <i class='bx bx-arrow-back me-1'></i> Back to List
    </a>
  </div>
</div>

<form action="{{ route('warehouse-equipment.store') }}" method="POST" id="equipmentForm" enctype="multipart/form-data">
  @csrf

  <div class="row">
    <!-- Main Form Card -->
    <div class="col-md-8">
      <!-- Basic Information -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Basic Information</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="equipment_code" class="form-label">Equipment Code *</label>
              <input type="text"
                     id="equipment_code"
                     name="equipment_code"
                     class="form-control @error('equipment_code') is-invalid @enderror"
                     placeholder="e.g., FK-001, PJ-025"
                     value="{{ old('equipment_code') }}"
                     required>
              @error('equipment_code')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="form-text text-muted">Unique identifier for this equipment</small>
            </div>

            <div class="col-md-6 mb-3">
              <label for="equipment_name" class="form-label">Equipment Name *</label>
              <input type="text"
                     id="equipment_name"
                     name="equipment_name"
                     class="form-control @error('equipment_name') is-invalid @enderror"
                     placeholder="e.g., Forklift #1, Pallet Jack A"
                     value="{{ old('equipment_name') }}"
                     required>
              @error('equipment_name')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="equipment_type" class="form-label">Equipment Type *</label>
              <select id="equipment_type" name="equipment_type" class="form-select @error('equipment_type') is-invalid @enderror" required>
                <option value="">Select type</option>
                <option value="forklift" {{ old('equipment_type') == 'forklift' ? 'selected' : '' }}>Forklift</option>
                <option value="pallet_jack" {{ old('equipment_type') == 'pallet_jack' ? 'selected' : '' }}>Pallet Jack</option>
                <option value="reach_truck" {{ old('equipment_type') == 'reach_truck' ? 'selected' : '' }}>Reach Truck</option>
                <option value="order_picker" {{ old('equipment_type') == 'order_picker' ? 'selected' : '' }}>Order Picker</option>
                <option value="scissor_lift" {{ old('equipment_type') == 'scissor_lift' ? 'selected' : '' }}>Scissor Lift</option>
                <option value="hand_truck" {{ old('equipment_type') == 'hand_truck' ? 'selected' : '' }}>Hand Truck</option>
                <option value="conveyor" {{ old('equipment_type') == 'conveyor' ? 'selected' : '' }}>Conveyor System</option>
                <option value="shelving" {{ old('equipment_type') == 'shelving' ? 'selected' : '' }}>Shelving/Racking</option>
                <option value="other" {{ old('equipment_type') == 'other' ? 'selected' : '' }}>Other</option>
              </select>
              @error('equipment_type')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="status" class="form-label">Initial Status *</label>
              <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="available" {{ old('status', 'available') == 'available' ? 'selected' : '' }}>Available</option>
                <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                <option value="out_of_service" {{ old('status') == 'out_of_service' ? 'selected' : '' }}>Out of Service</option>
              </select>
              @error('status')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="manufacturer" class="form-label">Manufacturer</label>
              <input type="text"
                     id="manufacturer"
                     name="manufacturer"
                     class="form-control @error('manufacturer') is-invalid @enderror"
                     placeholder="e.g., Toyota, Hyster, Crown"
                     value="{{ old('manufacturer') }}">
              @error('manufacturer')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="model" class="form-label">Model</label>
              <input type="text"
                     id="model"
                     name="model"
                     class="form-control @error('model') is-invalid @enderror"
                     placeholder="e.g., 8FBE15U, RC 5500"
                     value="{{ old('model') }}">
              @error('model')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="serial_number" class="form-label">Serial Number</label>
              <input type="text"
                     id="serial_number"
                     name="serial_number"
                     class="form-control @error('serial_number') is-invalid @enderror"
                     placeholder="Manufacturer serial number"
                     value="{{ old('serial_number') }}">
              @error('serial_number')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="purchase_date" class="form-label">Purchase Date</label>
              <input type="date"
                     id="purchase_date"
                     name="purchase_date"
                     class="form-control @error('purchase_date') is-invalid @enderror"
                     value="{{ old('purchase_date') }}">
              @error('purchase_date')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- Location & Capacity -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Location & Specifications</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="location" class="form-label">Current Location</label>
              <input type="text"
                     id="location"
                     name="location"
                     class="form-control @error('location') is-invalid @enderror"
                     placeholder="e.g., Warehouse A, Loading Bay 2"
                     value="{{ old('location') }}">
              @error('location')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="max_load_capacity" class="form-label">Max Load Capacity (kg)</label>
              <input type="number"
                     id="max_load_capacity"
                     name="max_load_capacity"
                     class="form-control @error('max_load_capacity') is-invalid @enderror"
                     step="0.01"
                     placeholder="e.g., 2000, 1500"
                     value="{{ old('max_load_capacity') }}">
              @error('max_load_capacity')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="max_lift_height" class="form-label">Max Lift Height (m)</label>
              <input type="number"
                     id="max_lift_height"
                     name="max_lift_height"
                     class="form-control @error('max_lift_height') is-invalid @enderror"
                     step="0.1"
                     placeholder="e.g., 5.5, 3.2"
                     value="{{ old('max_lift_height') }}">
              @error('max_lift_height')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="fuel_type" class="form-label">Fuel/Power Type</label>
              <select id="fuel_type" name="fuel_type" class="form-select @error('fuel_type') is-invalid @enderror">
                <option value="">Not Applicable</option>
                <option value="electric" {{ old('fuel_type') == 'electric' ? 'selected' : '' }}>Electric/Battery</option>
                <option value="lpg" {{ old('fuel_type') == 'lpg' ? 'selected' : '' }}>LPG</option>
                <option value="diesel" {{ old('fuel_type') == 'diesel' ? 'selected' : '' }}>Diesel</option>
                <option value="petrol" {{ old('fuel_type') == 'petrol' ? 'selected' : '' }}>Petrol</option>
                <option value="manual" {{ old('fuel_type') == 'manual' ? 'selected' : '' }}>Manual/Non-powered</option>
              </select>
              @error('fuel_type')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- License Requirements -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">License & Safety Requirements</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="requires_license" name="requires_license" value="1" {{ old('requires_license') ? 'checked' : '' }}>
              <label class="form-check-label" for="requires_license">
                Requires Operator License/Certification
              </label>
            </div>
          </div>

          <div id="licenseFields" style="display: {{ old('requires_license') ? 'block' : 'none' }};">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="license_type" class="form-label">License Type Required *</label>
                <input type="text"
                       id="license_type"
                       name="license_type"
                       class="form-control @error('license_type') is-invalid @enderror"
                       placeholder="e.g., Forklift LF, Boom Lift WP"
                       value="{{ old('license_type') }}">
                @error('license_type')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6 mb-3">
                <div class="form-check mt-4">
                  <input class="form-check-input" type="checkbox" id="requires_daily_prestart" name="requires_daily_prestart" value="1" {{ old('requires_daily_prestart', true) ? 'checked' : '' }}>
                  <label class="form-check-label" for="requires_daily_prestart">
                    Requires Daily Pre-Start Inspection
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="ppe_requirements" class="form-label">PPE Requirements</label>
            <textarea id="ppe_requirements"
                      name="ppe_requirements"
                      rows="2"
                      class="form-control @error('ppe_requirements') is-invalid @enderror"
                      placeholder="e.g., Steel-toe boots, high-visibility vest, hard hat">{{ old('ppe_requirements') }}</textarea>
            @error('ppe_requirements')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>

      <!-- Maintenance Schedule -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Maintenance Schedule</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="inspection_frequency_days" class="form-label">Inspection Frequency (days)</label>
              <input type="number"
                     id="inspection_frequency_days"
                     name="inspection_frequency_days"
                     class="form-control @error('inspection_frequency_days') is-invalid @enderror"
                     min="1"
                     placeholder="e.g., 30, 90, 180"
                     value="{{ old('inspection_frequency_days', 30) }}">
              @error('inspection_frequency_days')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="form-text text-muted">How often this equipment needs safety inspection</small>
            </div>

            <div class="col-md-6 mb-3">
              <label for="next_inspection_due" class="form-label">Next Inspection Due</label>
              <input type="date"
                     id="next_inspection_due"
                     name="next_inspection_due"
                     class="form-control @error('next_inspection_due') is-invalid @enderror"
                     value="{{ old('next_inspection_due', now()->addDays(30)->format('Y-m-d')) }}">
              @error('next_inspection_due')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="mb-3">
            <label for="notes" class="form-label">Additional Notes</label>
            <textarea id="notes"
                      name="notes"
                      rows="3"
                      class="form-control @error('notes') is-invalid @enderror"
                      placeholder="Any additional information about this equipment...">{{ old('notes') }}</textarea>
            @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
      <!-- QR Code Generation -->
      <div class="card mb-4 border-primary">
        <div class="card-body">
          <div class="d-flex align-items-start">
            <i class='bx bx-qr text-primary me-2 fs-4'></i>
            <div>
              <h6 class="mb-1">QR Code Generation</h6>
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" id="generate_qr" name="generate_qr" value="1" checked>
                <label class="form-check-label" for="generate_qr">
                  Auto-generate QR code for this equipment
                </label>
              </div>
              <small class="text-muted d-block mt-2">QR codes enable instant equipment identification via mobile device scanning.</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Branch Information -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Equipment Information</h5>
        </div>
        <div class="card-body">
          <div class="mb-2">
            <small class="text-muted">Branch</small>
            <p class="mb-0">{{ auth()->user()->branch->name }}</p>
          </div>
          <div class="mb-2">
            <small class="text-muted">Created By</small>
            <p class="mb-0">{{ auth()->user()->name }}</p>
          </div>
          <div class="mb-2">
            <small class="text-muted">Initial Status</small>
            <p class="mb-0"><span class="badge bg-success">Available</span></p>
          </div>
        </div>
      </div>

      <!-- Powered Equipment Notice -->
      <div class="card mb-4 border-warning">
        <div class="card-body">
          <div class="d-flex align-items-start">
            <i class='bx bx-error-circle text-warning me-2 fs-4'></i>
            <div>
              <h6 class="mb-1">Powered Equipment</h6>
              <small class="text-muted">Forklifts, reach trucks, scissor lifts, and other powered equipment require operator certification and daily pre-start inspections.</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="card">
        <div class="card-body">
          <button type="submit" class="btn btn-primary w-100 mb-2">
            <i class='bx bx-save me-1'></i> Add Equipment
          </button>
          <a href="{{ route('warehouse-equipment.index') }}" class="btn btn-outline-secondary w-100">
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
  // Toggle license fields
  $('#requires_license').on('change', function() {
    if ($(this).is(':checked')) {
      $('#licenseFields').slideDown();
      $('#license_type').prop('required', true);
    } else {
      $('#licenseFields').slideUp();
      $('#license_type').prop('required', false);
    }
  });

  // Auto-calculate next inspection date based on frequency
  $('#inspection_frequency_days').on('change', function() {
    const days = parseInt($(this).val());
    if (days > 0) {
      const today = new Date();
      today.setDate(today.getDate() + days);
      $('#next_inspection_due').val(today.toISOString().split('T')[0]);
    }
  });

  // Equipment type change suggestions
  $('#equipment_type').on('change', function() {
    const type = $(this).val();

    // Auto-check license requirement for powered equipment
    if (['forklift', 'reach_truck', 'order_picker', 'scissor_lift'].includes(type)) {
      $('#requires_license').prop('checked', true);
      $('#licenseFields').slideDown();
      $('#license_type').prop('required', true);
      $('#requires_daily_prestart').prop('checked', true);
    }
  });

  // Form validation
  $('#equipmentForm').on('submit', function(e) {
    const equipmentCode = $('#equipment_code').val();
    const equipmentName = $('#equipment_name').val();
    const equipmentType = $('#equipment_type').val();

    if (!equipmentCode || !equipmentName || !equipmentType) {
      e.preventDefault();
      alert('Please fill in all required fields (Code, Name, Type).');
      return false;
    }
  });
});
</script>
@endsection
