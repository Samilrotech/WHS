@extends('layouts/layoutMaster')

@section('title', 'Create Safety Inspection')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-1">Create Safety Inspection</h4>
    <p class="mb-0">Schedule a new safety inspection or audit</p>
  </div>
  <div>
    <a href="{{ route('safety-inspections.index') }}" class="btn btn-outline-secondary">
      <i class='bx bx-arrow-back me-1'></i> Back to List
    </a>
  </div>
</div>

<form action="{{ route('safety-inspections.store') }}" method="POST" id="safetyInspectionForm">
  @csrf

  <div class="row">
    <!-- Main Form Card -->
    <div class="col-md-8">
      <!-- Template Selection (Optional) -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Inspection Method</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Create From Template (Optional)</label>
            <select id="template_id" name="template_id" class="form-select @error('template_id') is-invalid @enderror">
              <option value="">-- Create Manual Inspection --</option>
              @foreach($templates as $template)
              <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                {{ $template->template_name }}
                ({{ ucfirst(str_replace('_', ' ', $template->category)) }})
                @if($template->is_mandatory)
                  <span class="text-danger">*</span>
                @endif
              </option>
              @endforeach
            </select>
            @error('template_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">
              Select a template for pre-configured checklist, or leave blank to create a custom inspection
            </small>
          </div>

          <div class="alert alert-info mb-0">
            <i class='bx bx-info-circle me-2'></i>
            <strong>Template vs Manual:</strong> Templates provide standardized checklists. Manual inspections allow custom item creation.
          </div>
        </div>
      </div>

      <!-- Basic Information -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Inspection Details</h5>
        </div>
        <div class="card-body">
          <!-- Inspection Type -->
          <div class="mb-3">
            <label for="inspection_type" class="form-label">Inspection Type *</label>
            <select id="inspection_type" name="inspection_type" class="form-select @error('inspection_type') is-invalid @enderror" required>
              <option value="">Select inspection type</option>
              <option value="workplace_safety" {{ old('inspection_type') == 'workplace_safety' ? 'selected' : '' }}>Workplace Safety Audit</option>
              <option value="equipment_safety" {{ old('inspection_type') == 'equipment_safety' ? 'selected' : '' }}>Equipment Safety Check</option>
              <option value="contractor_induction" {{ old('inspection_type') == 'contractor_induction' ? 'selected' : '' }}>Contractor Induction</option>
              <option value="pre_start_checklist" {{ old('inspection_type') == 'pre_start_checklist' ? 'selected' : '' }}>Pre-Start Checklist</option>
              <option value="safety_audit" {{ old('inspection_type') == 'safety_audit' ? 'selected' : '' }}>Safety System Audit</option>
              <option value="adhoc_inspection" {{ old('inspection_type') == 'adhoc_inspection' ? 'selected' : '' }}>Ad-hoc Inspection</option>
              <option value="warehouse_safety" {{ old('inspection_type') == 'warehouse_safety' ? 'selected' : '' }}>Warehouse Safety</option>
              <option value="office_safety" {{ old('inspection_type') == 'office_safety' ? 'selected' : '' }}>Office Safety</option>
              <option value="vehicle_safety" {{ old('inspection_type') == 'vehicle_safety' ? 'selected' : '' }}>Vehicle Safety Inspection</option>
            </select>
            @error('inspection_type')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Scheduled Date -->
          <div class="mb-3">
            <label for="scheduled_date" class="form-label">Scheduled Date</label>
            <input type="date"
                   id="scheduled_date"
                   name="scheduled_date"
                   class="form-control @error('scheduled_date') is-invalid @enderror"
                   value="{{ old('scheduled_date', now()->format('Y-m-d')) }}">
            @error('scheduled_date')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Location -->
          <div class="mb-3">
            <label for="location" class="form-label">Location</label>
            <input type="text"
                   id="location"
                   name="location"
                   class="form-control @error('location') is-invalid @enderror"
                   placeholder="e.g., Workshop, Site 5, Office Building A"
                   value="{{ old('location') }}">
            @error('location')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Area / Zone -->
          <div class="mb-3">
            <label for="area" class="form-label">Area / Zone</label>
            <input type="text"
                   id="area"
                   name="area"
                   class="form-control @error('area') is-invalid @enderror"
                   placeholder="e.g., Assembly Line 2, Loading Dock, Admin Floor 3"
                   value="{{ old('area') }}">
            @error('area')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <!-- Asset Tag (Optional) -->
          <div class="mb-3">
            <label for="asset_tag" class="form-label">Asset Tag (Optional)</label>
            <div class="input-group">
              <input type="text"
                     id="asset_tag"
                     name="asset_tag"
                     class="form-control @error('asset_tag') is-invalid @enderror"
                     placeholder="e.g., ASSET-001, EQ-12345"
                     value="{{ old('asset_tag') }}">
              <button type="button" class="btn btn-outline-secondary" id="scanQRButton" disabled>
                <i class='bx bx-qr-scan me-1'></i> Scan QR
              </button>
            </div>
            @error('asset_tag')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">QR code scanning coming soon</small>
          </div>

          <!-- Vehicle Selection (Conditional) -->
          <div id="vehicleSelection" style="display: none;">
            <div class="mb-3">
              <label for="vehicle_id" class="form-label">Vehicle</label>
              <select id="vehicle_id" name="vehicle_id" class="form-select @error('vehicle_id') is-invalid @enderror">
                <option value="">Select vehicle</option>
                @foreach(\App\Modules\VehicleManagement\Models\Vehicle::select('id', 'registration_number', 'make', 'model')->get() as $vehicle)
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
        </div>
      </div>

      <!-- Template Preview (When Selected) -->
      <div id="templatePreview" class="card mb-4" style="display: none;">
        <div class="card-header">
          <h5 class="mb-0">Template Preview</h5>
        </div>
        <div class="card-body">
          <div id="templateInfo">
            <!-- Dynamic template information loaded via JavaScript -->
          </div>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
      <!-- Inspector Assignment -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Inspector</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Assigned Inspector</label>
            <div class="d-flex align-items-center">
              <div class="avatar avatar-md me-3">
                <span class="avatar-initial rounded-circle bg-label-primary">
                  {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </span>
              </div>
              <div>
                <h6 class="mb-0">{{ auth()->user()->name }}</h6>
                <small class="text-muted">{{ auth()->user()->email }}</small>
              </div>
            </div>
            <small class="form-text text-muted mt-2 d-block">You will be assigned as the inspector</small>
          </div>
        </div>
      </div>

      <!-- Quick Info -->
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Inspection Info</h5>
        </div>
        <div class="card-body">
          <div class="mb-2">
            <small class="text-muted">Branch</small>
            <p class="mb-0">{{ auth()->user()->branch->name }}</p>
          </div>
          <div class="mb-2">
            <small class="text-muted">Status</small>
            <p class="mb-0"><span class="badge bg-primary">Scheduled</span></p>
          </div>
          <div class="mb-2">
            <small class="text-muted">Inspection Number</small>
            <p class="mb-0 text-muted">Auto-generated on save</p>
          </div>
        </div>
      </div>

      <!-- Offline Capability Notice -->
      <div class="card mb-4 border-info">
        <div class="card-body">
          <div class="d-flex align-items-start">
            <i class='bx bx-info-circle text-info me-2 fs-4'></i>
            <div>
              <h6 class="mb-1">Offline Support</h6>
              <small class="text-muted">This inspection can be conducted offline. Data will sync automatically when connection is restored.</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="card">
        <div class="card-body">
          <button type="submit" class="btn btn-primary w-100 mb-2">
            <i class='bx bx-save me-1'></i> Create Inspection
          </button>
          <a href="{{ route('safety-inspections.index') }}" class="btn btn-outline-secondary w-100">
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
  // Handle inspection type changes
  $('#inspection_type').on('change', function() {
    const inspectionType = $(this).val();

    // Show vehicle selection for vehicle safety inspections
    if (inspectionType === 'vehicle_safety') {
      $('#vehicleSelection').slideDown();
      $('#vehicle_id').prop('required', true);
    } else {
      $('#vehicleSelection').slideUp();
      $('#vehicle_id').prop('required', false);
    }
  });

  // Handle template selection changes
  $('#template_id').on('change', function() {
    const templateId = $(this).val();

    if (templateId) {
      // Show template preview
      $('#templatePreview').slideDown();

      // Load template details via AJAX (future enhancement)
      $('#templateInfo').html(`
        <div class="text-center py-3">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2 mb-0 text-muted">Loading template details...</p>
        </div>
      `);

      // Simulate template loading (replace with actual AJAX call)
      setTimeout(function() {
        const selectedOption = $('#template_id option:selected');
        const templateName = selectedOption.text().split('(')[0].trim();

        $('#templateInfo').html(`
          <h6 class="mb-2">${templateName}</h6>
          <p class="text-muted mb-2">This template includes:</p>
          <ul class="mb-0">
            <li>Pre-configured checklist items</li>
            <li>Standardized scoring system</li>
            <li>Pass/fail thresholds</li>
            <li>Compliance requirements</li>
          </ul>
        `);
      }, 800);
    } else {
      $('#templatePreview').slideUp();
    }
  });

  // Trigger inspection type check on page load (for old input)
  if ($('#inspection_type').val() === 'vehicle_safety') {
    $('#vehicleSelection').show();
    $('#vehicle_id').prop('required', true);
  }

  // QR Code scanning button (placeholder for future implementation)
  $('#scanQRButton').on('click', function() {
    alert('QR code scanning will be implemented in Phase 4 with html5-qrcode library.');
  });

  // Form validation
  $('#safetyInspectionForm').on('submit', function(e) {
    const inspectionType = $('#inspection_type').val();

    if (!inspectionType) {
      e.preventDefault();
      alert('Please select an inspection type.');
      $('#inspection_type').focus();
      return false;
    }
  });
});
</script>
@endsection
