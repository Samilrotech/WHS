@extends('layouts/layoutMaster')

@section('title', 'Edit Equipment - Warehouse Equipment')

@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}">
@endsection

@section('content')
<div class="row">
  <!-- Main Form Column -->
  <div class="col-xl-9 col-lg-8 col-md-8">
    <!-- Read-Only Information Alert -->
    <div class="alert alert-info mb-3">
      <i class='bx bx-info-circle me-2'></i>
      <strong>Note:</strong> Equipment Code cannot be modified after creation. To change it, delete and recreate the equipment record.
    </div>

    <!-- Current Status Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Current Equipment Status</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-3 mb-3">
            <label class="form-label text-muted">Equipment Code</label>
            <h5 class="mb-0">{{ $equipment->equipment_code }}</h5>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label text-muted">Current Status</label>
            @php
              $statusClass = match($equipment->status) {
                'available' => 'success',
                'in_use' => 'primary',
                'maintenance' => 'warning',
                'out_of_service' => 'danger',
                'retired' => 'secondary',
                default => 'secondary'
              };
            @endphp
            <h5 class="mb-0"><span class="badge bg-{{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $equipment->status)) }}</span></h5>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label text-muted">QR Code</label>
            <h5 class="mb-0">
              @if($equipment->qr_code_path)
                <i class='bx bx-qr text-success bx-md'></i> <small class="text-muted">Available</small>
              @else
                <i class='bx bx-qr text-muted bx-md'></i> <small class="text-muted">Not Generated</small>
              @endif
            </h5>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label text-muted">Last Updated</label>
            <h5 class="mb-0"><small>{{ $equipment->updated_at->format('d/m/Y H:i') }}</small></h5>
          </div>
        </div>

        @if($equipment->qr_code_path)
        <div class="row mt-2">
          <div class="col-12">
            <a href="{{ asset('storage/' . $equipment->qr_code_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
              <i class='bx bx-qr me-1'></i> View QR Code
            </a>
            <a href="{{ asset('storage/' . $equipment->qr_code_path) }}" download class="btn btn-sm btn-outline-secondary">
              <i class='bx bx-download me-1'></i> Download QR Code
            </a>
          </div>
        </div>
        @else
        <div class="row mt-2">
          <div class="col-12">
            <button type="button" class="btn btn-sm btn-primary" onclick="generateQrCode()">
              <i class='bx bx-qr me-1'></i> Generate QR Code Now
            </button>
          </div>
        </div>
        @endif
      </div>
    </div>

    <!-- Edit Equipment Form -->
    <form action="{{ route('warehouse-equipment.update', $equipment) }}" method="POST" id="editEquipmentForm">
      @csrf
      @method('PUT')

      <!-- Basic Information -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Basic Information</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="equipment_name" class="form-label">Equipment Name *</label>
              <input type="text" id="equipment_name" name="equipment_name"
                     class="form-control @error('equipment_name') is-invalid @enderror"
                     value="{{ old('equipment_name', $equipment->equipment_name) }}"
                     placeholder="e.g., Toyota Forklift #1" required>
              @error('equipment_name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="equipment_type" class="form-label">Equipment Type *</label>
              <select id="equipment_type" name="equipment_type" class="form-select @error('equipment_type') is-invalid @enderror" required>
                <option value="">Select type</option>
                <option value="forklift" {{ old('equipment_type', $equipment->equipment_type) == 'forklift' ? 'selected' : '' }}>Forklift</option>
                <option value="pallet_jack" {{ old('equipment_type', $equipment->equipment_type) == 'pallet_jack' ? 'selected' : '' }}>Pallet Jack</option>
                <option value="reach_truck" {{ old('equipment_type', $equipment->equipment_type) == 'reach_truck' ? 'selected' : '' }}>Reach Truck</option>
                <option value="order_picker" {{ old('equipment_type', $equipment->equipment_type) == 'order_picker' ? 'selected' : '' }}>Order Picker</option>
                <option value="scissor_lift" {{ old('equipment_type', $equipment->equipment_type) == 'scissor_lift' ? 'selected' : '' }}>Scissor Lift</option>
                <option value="hand_truck" {{ old('equipment_type', $equipment->equipment_type) == 'hand_truck' ? 'selected' : '' }}>Hand Truck</option>
                <option value="conveyor" {{ old('equipment_type', $equipment->equipment_type) == 'conveyor' ? 'selected' : '' }}>Conveyor System</option>
                <option value="shelving" {{ old('equipment_type', $equipment->equipment_type) == 'shelving' ? 'selected' : '' }}>Shelving/Racking</option>
                <option value="other" {{ old('equipment_type', $equipment->equipment_type) == 'other' ? 'selected' : '' }}>Other</option>
              </select>
              @error('equipment_type')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="status" class="form-label">Equipment Status *</label>
              <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="">Select status</option>
                <option value="available" {{ old('status', $equipment->status) == 'available' ? 'selected' : '' }}>Available</option>
                <option value="in_use" {{ old('status', $equipment->status) == 'in_use' ? 'selected' : '' }}>In Use</option>
                <option value="maintenance" {{ old('status', $equipment->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="out_of_service" {{ old('status', $equipment->status) == 'out_of_service' ? 'selected' : '' }}>Out of Service</option>
                <option value="retired" {{ old('status', $equipment->status) == 'retired' ? 'selected' : '' }}>Retired</option>
              </select>
              @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="location" class="form-label">Current Location</label>
              <input type="text" id="location" name="location"
                     class="form-control @error('location') is-invalid @enderror"
                     value="{{ old('location', $equipment->location) }}"
                     placeholder="e.g., Warehouse Aisle 3">
              @error('location')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="manufacturer" class="form-label">Manufacturer</label>
              <input type="text" id="manufacturer" name="manufacturer"
                     class="form-control @error('manufacturer') is-invalid @enderror"
                     value="{{ old('manufacturer', $equipment->manufacturer) }}"
                     placeholder="e.g., Toyota, Linde">
              @error('manufacturer')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-4 mb-3">
              <label for="model" class="form-label">Model</label>
              <input type="text" id="model" name="model"
                     class="form-control @error('model') is-invalid @enderror"
                     value="{{ old('model', $equipment->model) }}"
                     placeholder="e.g., 8FG25">
              @error('model')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-4 mb-3">
              <label for="serial_number" class="form-label">Serial Number</label>
              <input type="text" id="serial_number" name="serial_number"
                     class="form-control @error('serial_number') is-invalid @enderror"
                     value="{{ old('serial_number', $equipment->serial_number) }}"
                     placeholder="e.g., SN123456789">
              @error('serial_number')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="purchase_date" class="form-label">Purchase Date</label>
              <input type="date" id="purchase_date" name="purchase_date"
                     class="form-control @error('purchase_date') is-invalid @enderror"
                     value="{{ old('purchase_date', $equipment->purchase_date ? $equipment->purchase_date->format('Y-m-d') : '') }}">
              @error('purchase_date')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="purchase_cost" class="form-label">Purchase Cost (AUD)</label>
              <input type="number" id="purchase_cost" name="purchase_cost"
                     class="form-control @error('purchase_cost') is-invalid @enderror"
                     value="{{ old('purchase_cost', $equipment->purchase_cost) }}"
                     step="0.01" min="0" placeholder="e.g., 25000.00">
              @error('purchase_cost')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- Specifications -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Equipment Specifications</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="max_load_capacity" class="form-label">Max Load Capacity (kg)</label>
              <input type="number" id="max_load_capacity" name="max_load_capacity"
                     class="form-control @error('max_load_capacity') is-invalid @enderror"
                     value="{{ old('max_load_capacity', $equipment->max_load_capacity) }}"
                     step="0.01" min="0" placeholder="e.g., 2500">
              @error('max_load_capacity')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-4 mb-3">
              <label for="max_lift_height" class="form-label">Max Lift Height (meters)</label>
              <input type="number" id="max_lift_height" name="max_lift_height"
                     class="form-control @error('max_lift_height') is-invalid @enderror"
                     value="{{ old('max_lift_height', $equipment->max_lift_height) }}"
                     step="0.01" min="0" placeholder="e.g., 4.5">
              @error('max_lift_height')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-4 mb-3">
              <label for="fuel_type" class="form-label">Fuel/Power Type</label>
              <select id="fuel_type" name="fuel_type" class="form-select @error('fuel_type') is-invalid @enderror">
                <option value="">Select type</option>
                <option value="electric" {{ old('fuel_type', $equipment->fuel_type) == 'electric' ? 'selected' : '' }}>Electric</option>
                <option value="lpg" {{ old('fuel_type', $equipment->fuel_type) == 'lpg' ? 'selected' : '' }}>LPG</option>
                <option value="diesel" {{ old('fuel_type', $equipment->fuel_type) == 'diesel' ? 'selected' : '' }}>Diesel</option>
                <option value="petrol" {{ old('fuel_type', $equipment->fuel_type) == 'petrol' ? 'selected' : '' }}>Petrol</option>
                <option value="manual" {{ old('fuel_type', $equipment->fuel_type) == 'manual' ? 'selected' : '' }}>Manual/None</option>
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
              <input class="form-check-input" type="checkbox" id="requires_license" name="requires_license"
                     value="1" {{ old('requires_license', $equipment->requires_license) ? 'checked' : '' }}>
              <label class="form-check-label" for="requires_license">
                Requires License/Certification to Operate
              </label>
            </div>
          </div>

          <div id="licenseFields" style="display: {{ old('requires_license', $equipment->requires_license) ? 'block' : 'none' }};">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="license_type" class="form-label">License Type Required</label>
                <input type="text" id="license_type" name="license_type"
                       class="form-control @error('license_type') is-invalid @enderror"
                       value="{{ old('license_type', $equipment->license_type) }}"
                       placeholder="e.g., Forklift LF, Boom Lift WP">
                @error('license_type')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6 mb-3">
                <div class="form-check mt-4">
                  <input class="form-check-input" type="checkbox" id="requires_daily_prestart" name="requires_daily_prestart"
                         value="1" {{ old('requires_daily_prestart', $equipment->requires_daily_prestart) ? 'checked' : '' }}>
                  <label class="form-check-label" for="requires_daily_prestart">
                    Requires Daily Pre-Start Inspection
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="ppe_requirements" class="form-label">PPE Requirements</label>
            <textarea id="ppe_requirements" name="ppe_requirements" class="form-control @error('ppe_requirements') is-invalid @enderror"
                      rows="3" placeholder="e.g., Steel-capped boots, high-visibility vest, safety helmet">{{ old('ppe_requirements', $equipment->ppe_requirements) }}</textarea>
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
              <input type="number" id="inspection_frequency_days" name="inspection_frequency_days"
                     class="form-control @error('inspection_frequency_days') is-invalid @enderror"
                     value="{{ old('inspection_frequency_days', $equipment->inspection_frequency_days) }}"
                     min="1" placeholder="e.g., 30 (monthly), 90 (quarterly)">
              @error('inspection_frequency_days')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="next_inspection_due" class="form-label">Next Inspection Due</label>
              <input type="date" id="next_inspection_due" name="next_inspection_due"
                     class="form-control @error('next_inspection_due') is-invalid @enderror"
                     value="{{ old('next_inspection_due', $equipment->next_inspection_due ? $equipment->next_inspection_due->format('Y-m-d') : '') }}">
              @error('next_inspection_due')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <label for="notes" class="form-label">Equipment Notes</label>
              <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror"
                        rows="4" placeholder="Any additional information about this equipment...">{{ old('notes', $equipment->notes) }}</textarea>
              @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
              <i class='bx bx-trash me-1'></i> Delete Equipment
            </button>
            <div>
              <a href="{{ route('warehouse-equipment.index') }}" class="btn btn-outline-secondary me-2">
                <i class='bx bx-x me-1'></i> Cancel
              </a>
              <button type="submit" class="btn btn-primary">
                <i class='bx bx-save me-1'></i> Save Changes
              </button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- Sidebar Column -->
  <div class="col-xl-3 col-lg-4 col-md-4">
    <!-- Equipment Usage Summary -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">Usage Summary</h6>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <small class="text-muted">Total Inspections</small>
          <h5 class="mb-0">{{ $equipment->inspections()->count() }}</h5>
        </div>
        <div class="mb-3">
          <small class="text-muted">Total Checkouts</small>
          <h5 class="mb-0">{{ $equipment->custodyLogs()->count() }}</h5>
        </div>
        <div class="mb-3">
          <small class="text-muted">Currently Checked Out</small>
          <h5 class="mb-0">
            @php
              $activeCheckout = $equipment->custodyLogs()->whereNull('checked_in_at')->first();
            @endphp
            @if($activeCheckout)
              <span class="badge bg-warning">Yes</span>
              <small class="d-block mt-1">{{ $activeCheckout->custodian->name ?? 'Unknown' }}</small>
            @else
              <span class="badge bg-success">No</span>
            @endif
          </h5>
        </div>
        @if($equipment->next_inspection_due)
        <div class="mb-0">
          <small class="text-muted">Next Inspection</small>
          <h5 class="mb-0">
            @php
              $daysUntilInspection = now()->diffInDays($equipment->next_inspection_due, false);
              $inspectionClass = $daysUntilInspection < 0 ? 'danger' : ($daysUntilInspection <= 7 ? 'warning' : 'info');
            @endphp
            <span class="badge bg-{{ $inspectionClass }}">{{ $equipment->next_inspection_due->format('d/m/Y') }}</span>
            <small class="d-block mt-1">
              @if($daysUntilInspection < 0)
                Overdue by {{ abs($daysUntilInspection) }} days
              @else
                Due in {{ $daysUntilInspection }} days
              @endif
            </small>
          </h5>
        </div>
        @endif
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Quick Actions</h6>
      </div>
      <div class="card-body">
        <a href="{{ route('warehouse-equipment.show', $equipment) }}" class="btn btn-outline-primary w-100 mb-2">
          <i class='bx bx-show me-1'></i> View Details
        </a>
        @if(!$equipment->qr_code_path)
        <button type="button" class="btn btn-outline-info w-100 mb-2" onclick="generateQrCode()">
          <i class='bx bx-qr me-1'></i> Generate QR Code
        </button>
        @endif
        <a href="{{ route('warehouse-equipment.index') }}" class="btn btn-outline-secondary w-100">
          <i class='bx bx-arrow-back me-1'></i> Back to List
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Delete Equipment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-danger mb-3">
          <i class='bx bx-error-circle me-2'></i>
          <strong>Warning:</strong> This action cannot be undone!
        </div>
        <p>Are you sure you want to delete <strong>{{ $equipment->equipment_name }}</strong> ({{ $equipment->equipment_code }})?</p>
        <p class="mb-0">This will also delete:</p>
        <ul>
          <li>All inspection records ({{ $equipment->inspections()->count() }})</li>
          <li>All custody/checkout logs ({{ $equipment->custodyLogs()->count() }})</li>
          <li>Associated QR codes and documents</li>
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <form action="{{ route('warehouse-equipment.destroy', $equipment) }}" method="POST" class="d-inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">
            <i class='bx bx-trash me-1'></i> Yes, Delete Equipment
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
<script>
$(document).ready(function() {
  'use strict';

  // License requirement toggle
  $('#requires_license').on('change', function() {
    if ($(this).is(':checked')) {
      $('#licenseFields').slideDown();
      $('#license_type').prop('required', true);
    } else {
      $('#licenseFields').slideUp();
      $('#license_type').prop('required', false);
    }
  });

  // Equipment type change suggestions
  $('#equipment_type').on('change', function() {
    const type = $(this).val();

    // Auto-check license requirement for powered equipment
    if (['forklift', 'reach_truck', 'order_picker', 'scissor_lift'].includes(type)) {
      if (!$('#requires_license').is(':checked')) {
        $('#requires_license').prop('checked', true).trigger('change');
        $('#requires_daily_prestart').prop('checked', true);
      }
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

  // Form validation
  $('#editEquipmentForm').on('submit', function(e) {
    if ($('#requires_license').is(':checked') && !$('#license_type').val()) {
      e.preventDefault();
      alert('Please specify the license type required for this equipment.');
      $('#license_type').focus();
      return false;
    }
  });
});

// Generate QR Code function
function generateQrCode() {
  if (confirm('Generate a QR code for this equipment?')) {
    // Submit form with generate_qr flag
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("warehouse-equipment.update", $equipment) }}';

    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);

    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'PUT';
    form.appendChild(methodField);

    const qrField = document.createElement('input');
    qrField.type = 'hidden';
    qrField.name = 'generate_qr';
    qrField.value = '1';
    form.appendChild(qrField);

    document.body.appendChild(form);
    form.submit();
  }
}
</script>
@endsection
