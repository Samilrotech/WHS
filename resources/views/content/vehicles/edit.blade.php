@extends('layouts.layoutMaster')

@section('title', 'Edit Vehicle')

@section('content')
@include('layouts.sections.flash-message')

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Vehicle: {{ $vehicle->registration_number }}</h5>
        <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-sm btn-outline-secondary">
          <i class='icon-base ti ti-arrow-left me-1'></i> Back to Details
        </a>
      </div>
      <div class="card-body">
        <form action="{{ route('vehicles.update', $vehicle) }}" method="POST" class="needs-validation" novalidate>
          @csrf
          @method('PUT')

          <h6 class="mb-3">Vehicle Information</h6>

          <div class="row">
            <!-- Registration Number -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="registration_number" class="form-label">Registration Number *</label>
                <input
                  type="text"
                  id="registration_number"
                  name="registration_number"
                  class="form-control @error('registration_number') is-invalid @enderror"
                  value="{{ old('registration_number', $vehicle->registration_number) }}"
                  required>
                @error('registration_number')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- VIN Number -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="vin_number" class="form-label">VIN Number</label>
                <input
                  type="text"
                  id="vin_number"
                  name="vin_number"
                  class="form-control @error('vin_number') is-invalid @enderror"
                  value="{{ old('vin_number', $vehicle->vin_number) }}">
                @error('vin_number')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Make -->
            <div class="col-md-4">
              <div class="mb-3">
                <label for="make" class="form-label">Make *</label>
                <input
                  type="text"
                  id="make"
                  name="make"
                  class="form-control @error('make') is-invalid @enderror"
                  value="{{ old('make', $vehicle->make) }}"
                  required>
                @error('make')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- Model -->
            <div class="col-md-4">
              <div class="mb-3">
                <label for="model" class="form-label">Model *</label>
                <input
                  type="text"
                  id="model"
                  name="model"
                  class="form-control @error('model') is-invalid @enderror"
                  value="{{ old('model', $vehicle->model) }}"
                  required>
                @error('model')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- Year -->
            <div class="col-md-4">
              <div class="mb-3">
                <label for="year" class="form-label">Year *</label>
                <input
                  type="number"
                  id="year"
                  name="year"
                  class="form-control @error('year') is-invalid @enderror"
                  value="{{ old('year', $vehicle->year) }}"
                  min="1900"
                  max="{{ date('Y') + 1 }}"
                  required>
                @error('year')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Color -->
            <div class="col-md-4">
              <div class="mb-3">
                <label for="color" class="form-label">Color</label>
                <input
                  type="text"
                  id="color"
                  name="color"
                  class="form-control @error('color') is-invalid @enderror"
                  value="{{ old('color', $vehicle->color) }}">
                @error('color')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- Fuel Type -->
            <div class="col-md-4">
              <div class="mb-3">
                <label for="fuel_type" class="form-label">Fuel Type</label>
                <select id="fuel_type" name="fuel_type" class="form-select @error('fuel_type') is-invalid @enderror">
                  <option value="">Select fuel type</option>
                  <option value="Petrol" {{ old('fuel_type', $vehicle->fuel_type) === 'Petrol' ? 'selected' : '' }}>Petrol</option>
                  <option value="Diesel" {{ old('fuel_type', $vehicle->fuel_type) === 'Diesel' ? 'selected' : '' }}>Diesel</option>
                  <option value="Electric" {{ old('fuel_type', $vehicle->fuel_type) === 'Electric' ? 'selected' : '' }}>Electric</option>
                  <option value="Hybrid" {{ old('fuel_type', $vehicle->fuel_type) === 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                  <option value="LPG" {{ old('fuel_type', $vehicle->fuel_type) === 'LPG' ? 'selected' : '' }}>LPG</option>
                </select>
                @error('fuel_type')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- Odometer -->
            <div class="col-md-4">
              <div class="mb-3">
                <label for="odometer_reading" class="form-label">Odometer (km)</label>
                <input
                  type="number"
                  id="odometer_reading"
                  name="odometer_reading"
                  class="form-control @error('odometer_reading') is-invalid @enderror"
                  value="{{ old('odometer_reading', $vehicle->odometer_reading) }}"
                  min="0">
                @error('odometer_reading')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <!-- Status -->
          <div class="row">
            <div class="col-md-12">
              <div class="mb-3">
                <label for="status" class="form-label">Status *</label>
                <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                  @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" {{ old('status', $vehicle->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
                </select>
                @error('status')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <!-- Inspection cadence -->
          <div class="row">
            <div class="col-md-12">
              <div class="mb-3">
                <label for="inspection_frequency" class="form-label">Inspection cadence *</label>
                <select id="inspection_frequency" name="inspection_frequency" class="form-select @error('inspection_frequency') is-invalid @enderror" required>
                  @foreach($inspectionFrequencies as $value => $label)
                    <option value="{{ $value }}" {{ old('inspection_frequency', $vehicle->inspection_frequency) === $value ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
                </select>
                @error('inspection_frequency')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <hr class="my-4">

          <h6 class="mb-3">Financial Information</h6>

          <div class="row">
            <!-- Purchase Date -->
            <div class="col-md-4">
              <div class="mb-3">
                <label for="purchase_date" class="form-label">Purchase Date</label>
                <input
                  type="date"
                  id="purchase_date"
                  name="purchase_date"
                  class="form-control @error('purchase_date') is-invalid @enderror"
                  value="{{ old('purchase_date', $vehicle->purchase_date?->format('Y-m-d')) }}">
                @error('purchase_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- Purchase Price -->
            <div class="col-md-4">
              <div class="mb-3">
                <label for="purchase_price" class="form-label">Purchase Price ($)</label>
                <input
                  type="number"
                  id="purchase_price"
                  name="purchase_price"
                  class="form-control @error('purchase_price') is-invalid @enderror"
                  value="{{ old('purchase_price', $vehicle->purchase_price) }}"
                  step="0.01"
                  min="0">
                @error('purchase_price')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- Depreciation Method -->
            <div class="col-md-4">
              <div class="mb-3">
                <label for="depreciation_method" class="form-label">Depreciation Method</label>
                <select id="depreciation_method" name="depreciation_method" class="form-select @error('depreciation_method') is-invalid @enderror">
                  @foreach($depreciationMethods as $value => $label)
                    <option value="{{ $value }}" {{ old('depreciation_method', $vehicle->depreciation_method) === $value ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
                </select>
                @error('depreciation_method')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Depreciation Rate -->
            <div class="col-md-12">
              <div class="mb-3">
                <label for="depreciation_rate" class="form-label">Depreciation Rate (%)</label>
                <input
                  type="number"
                  id="depreciation_rate"
                  name="depreciation_rate"
                  class="form-control @error('depreciation_rate') is-invalid @enderror"
                  value="{{ old('depreciation_rate', $vehicle->depreciation_rate) }}"
                  step="0.01"
                  min="0"
                  max="100">
                @error('depreciation_rate')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <hr class="my-4">

          <h6 class="mb-3">Insurance Information</h6>

          <div class="row">
            <!-- Insurance Company -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="insurance_company" class="form-label">Insurance Company</label>
                <input
                  type="text"
                  id="insurance_company"
                  name="insurance_company"
                  class="form-control @error('insurance_company') is-invalid @enderror"
                  value="{{ old('insurance_company', $vehicle->insurance_company) }}">
                @error('insurance_company')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- Policy Number -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="insurance_policy_number" class="form-label">Policy Number</label>
                <input
                  type="text"
                  id="insurance_policy_number"
                  name="insurance_policy_number"
                  class="form-control @error('insurance_policy_number') is-invalid @enderror"
                  value="{{ old('insurance_policy_number', $vehicle->insurance_policy_number) }}">
                @error('insurance_policy_number')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Insurance Expiry -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="insurance_expiry_date" class="form-label">Insurance Expiry Date</label>
                <input
                  type="date"
                  id="insurance_expiry_date"
                  name="insurance_expiry_date"
                  class="form-control @error('insurance_expiry_date') is-invalid @enderror"
                  value="{{ old('insurance_expiry_date', $vehicle->insurance_expiry_date?->format('Y-m-d')) }}">
                @error('insurance_expiry_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- Insurance Premium -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="insurance_premium" class="form-label">Insurance Premium ($)</label>
                <input
                  type="number"
                  id="insurance_premium"
                  name="insurance_premium"
                  class="form-control @error('insurance_premium') is-invalid @enderror"
                  value="{{ old('insurance_premium', $vehicle->insurance_premium) }}"
                  step="0.01"
                  min="0">
                @error('insurance_premium')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <hr class="my-4">

          <h6 class="mb-3">Compliance</h6>

          <div class="row">
            <!-- Rego Expiry -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="rego_expiry_date" class="form-label">Registration Expiry Date</label>
                <input
                  type="date"
                  id="rego_expiry_date"
                  name="rego_expiry_date"
                  class="form-control @error('rego_expiry_date') is-invalid @enderror"
                  value="{{ old('rego_expiry_date', $vehicle->rego_expiry_date?->format('Y-m-d')) }}">
                @error('rego_expiry_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- Inspection Due -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="inspection_due_date" class="form-label">Next Inspection Due</label>
                <input
                  type="date"
                  id="inspection_due_date"
                  name="inspection_due_date"
                  class="form-control @error('inspection_due_date') is-invalid @enderror"
                  value="{{ old('inspection_due_date', $vehicle->inspection_due_date?->format('Y-m-d')) }}">
                @error('inspection_due_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <hr class="my-4">

          <h6 class="mb-3">Additional Notes</h6>

          <div class="row">
            <div class="col-md-12">
              <div class="mb-3">
                <label for="notes" class="form-label">Notes</label>
                <textarea
                  id="notes"
                  name="notes"
                  class="form-control @error('notes') is-invalid @enderror"
                  rows="4"
                  placeholder="Any additional information about this vehicle...">{{ old('notes', $vehicle->notes) }}</textarea>
                @error('notes')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <i class='icon-base ti ti-device-floppy me-1'></i> Update Vehicle
            </button>
            <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-outline-secondary">
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

