@extends('layouts/layoutMaster')

@section('title', $equipment->equipment_name . ' - Warehouse Equipment')

@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
@endsection

@section('content')
<div class="row">
  <!-- Main Content Column -->
  <div class="col-xl-9 col-lg-8 col-md-8">
    <!-- Equipment Overview Card -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Equipment Details</h5>
        <div>
          <a href="{{ route('warehouse-equipment.edit', $equipment) }}" class="btn btn-sm btn-primary">
            <i class='bx bx-edit me-1'></i> Edit
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- Equipment Code & QR -->
          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Equipment Code</label>
            <h4 class="mb-0">{{ $equipment->equipment_code }}</h4>
          </div>
          <div class="col-md-6 mb-3 text-md-end">
            @if($equipment->qr_code_path)
              <img src="{{ asset('storage/' . $equipment->qr_code_path) }}" alt="QR Code" style="max-width: 120px; height: auto;">
              <div class="mt-2">
                <a href="{{ asset('storage/' . $equipment->qr_code_path) }}" download class="btn btn-sm btn-outline-secondary">
                  <i class='bx bx-download me-1'></i> Download QR
                </a>
              </div>
            @else
              <div class="alert alert-warning mb-0">
                <i class='bx bx-info-circle me-1'></i>
                No QR code generated yet
              </div>
            @endif
          </div>
        </div>

        <hr class="my-3">

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Equipment Name</label>
            <h5 class="mb-0">{{ $equipment->equipment_name }}</h5>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label text-muted">Type</label>
            <h5 class="mb-0">
              @php
                $typeIcons = [
                  'forklift' => 'bx-package',
                  'pallet_jack' => 'bx-cart',
                  'reach_truck' => 'bx-package',
                  'order_picker' => 'bx-body',
                  'scissor_lift' => 'bx-trending-up',
                  'hand_truck' => 'bx-cart',
                  'conveyor' => 'bx-transfer',
                  'shelving' => 'bx-grid-alt',
                  'other' => 'bx-box'
                ];
                $icon = $typeIcons[$equipment->equipment_type] ?? 'bx-box';
              @endphp
              <i class='bx {{ $icon }} me-1'></i>
              {{ ucfirst(str_replace('_', ' ', $equipment->equipment_type)) }}
            </h5>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label text-muted">Status</label>
            <h5 class="mb-0">
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
              <span class="badge bg-{{ $statusClass }} fs-6">{{ ucfirst(str_replace('_', ' ', $equipment->status)) }}</span>
            </h5>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="form-label text-muted">Manufacturer</label>
            <p class="mb-0">{{ $equipment->manufacturer ?? 'Not specified' }}</p>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label text-muted">Model</label>
            <p class="mb-0">{{ $equipment->model ?? 'Not specified' }}</p>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label text-muted">Serial Number</label>
            <p class="mb-0">{{ $equipment->serial_number ?? 'Not specified' }}</p>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Current Location</label>
            <p class="mb-0">
              <i class='bx bx-map me-1'></i>
              {{ $equipment->location ?? 'Location not set' }}
            </p>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label text-muted">Purchase Date</label>
            <p class="mb-0">{{ $equipment->purchase_date ? $equipment->purchase_date->format('d/m/Y') : 'Not specified' }}</p>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label text-muted">Purchase Cost</label>
            <p class="mb-0">{{ $equipment->purchase_cost ? '$' . number_format($equipment->purchase_cost, 2) : 'Not specified' }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Specifications Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Technical Specifications</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="form-label text-muted">Max Load Capacity</label>
            <h5 class="mb-0">
              @if($equipment->max_load_capacity)
                {{ number_format($equipment->max_load_capacity, 0) }} kg
              @else
                <span class="text-muted">Not specified</span>
              @endif
            </h5>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label text-muted">Max Lift Height</label>
            <h5 class="mb-0">
              @if($equipment->max_lift_height)
                {{ number_format($equipment->max_lift_height, 2) }} m
              @else
                <span class="text-muted">Not specified</span>
              @endif
            </h5>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label text-muted">Fuel/Power Type</label>
            <h5 class="mb-0">
              @if($equipment->fuel_type)
                <i class='bx bx-bolt me-1'></i>
                {{ ucfirst($equipment->fuel_type) }}
              @else
                <span class="text-muted">Not specified</span>
              @endif
            </h5>
          </div>
        </div>
      </div>
    </div>

    <!-- License & Safety Requirements Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">License & Safety Requirements</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">License Required</label>
            <h5 class="mb-0">
              @if($equipment->requires_license)
                <span class="badge bg-warning fs-6">
                  <i class='bx bx-certification me-1'></i> Yes - {{ $equipment->license_type }}
                </span>
              @else
                <span class="badge bg-success fs-6">No License Required</span>
              @endif
            </h5>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Daily Pre-Start Required</label>
            <h5 class="mb-0">
              @if($equipment->requires_daily_prestart)
                <span class="badge bg-info fs-6">
                  <i class='bx bx-check-circle me-1'></i> Yes
                </span>
              @else
                <span class="badge bg-secondary fs-6">No</span>
              @endif
            </h5>
          </div>
        </div>

        @if($equipment->ppe_requirements)
        <div class="row">
          <div class="col-12">
            <label class="form-label text-muted">PPE Requirements</label>
            <div class="alert alert-info mb-0">
              <i class='bx bx-shield me-2'></i>
              {{ $equipment->ppe_requirements }}
            </div>
          </div>
        </div>
        @endif
      </div>
    </div>

    <!-- Maintenance Schedule Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Maintenance Schedule</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Inspection Frequency</label>
            <h5 class="mb-0">
              @if($equipment->inspection_frequency_days)
                Every {{ $equipment->inspection_frequency_days }} days
                @if($equipment->inspection_frequency_days == 30)
                  <span class="text-muted">(Monthly)</span>
                @elseif($equipment->inspection_frequency_days == 90)
                  <span class="text-muted">(Quarterly)</span>
                @elseif($equipment->inspection_frequency_days == 180)
                  <span class="text-muted">(Semi-Annually)</span>
                @elseif($equipment->inspection_frequency_days == 365)
                  <span class="text-muted">(Annually)</span>
                @endif
              @else
                <span class="text-muted">Not set</span>
              @endif
            </h5>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Next Inspection Due</label>
            <h5 class="mb-0">
              @if($equipment->next_inspection_due)
                @php
                  $daysUntilInspection = now()->diffInDays($equipment->next_inspection_due, false);
                  $inspectionClass = $daysUntilInspection < 0 ? 'danger' : ($daysUntilInspection <= 7 ? 'warning' : 'success');
                @endphp
                <span class="badge bg-{{ $inspectionClass }} fs-6">{{ $equipment->next_inspection_due->format('d/m/Y') }}</span>
                <small class="d-block mt-1">
                  @if($daysUntilInspection < 0)
                    <span class="text-danger">Overdue by {{ abs($daysUntilInspection) }} days</span>
                  @elseif($daysUntilInspection <= 7)
                    <span class="text-warning">Due in {{ $daysUntilInspection }} days</span>
                  @else
                    <span class="text-muted">Due in {{ $daysUntilInspection }} days</span>
                  @endif
                </small>
              @else
                <span class="text-muted">Not scheduled</span>
              @endif
            </h5>
          </div>
        </div>
      </div>
    </div>

    <!-- Equipment Notes -->
    @if($equipment->notes)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Equipment Notes</h5>
      </div>
      <div class="card-body">
        <p class="mb-0">{{ $equipment->notes }}</p>
      </div>
    </div>
    @endif

    <!-- Inspection History -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Inspection History</h5>
      </div>
      <div class="card-body">
        @if($equipment->inspections->count() > 0)
        <div class="table-responsive">
          <table id="inspectionsTable" class="table table-hover">
            <thead>
              <tr>
                <th>Date</th>
                <th>Inspector</th>
                <th>Status</th>
                <th>Issues Found</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($equipment->inspections as $inspection)
              <tr>
                <td>{{ $inspection->inspection_date->format('d/m/Y H:i') }}</td>
                <td>{{ $inspection->inspector->name ?? 'Unknown' }}</td>
                <td>
                  @php
                    $statusClass = match($inspection->status) {
                      'passed' => 'success',
                      'failed' => 'danger',
                      'conditional' => 'warning',
                      default => 'secondary'
                    };
                  @endphp
                  <span class="badge bg-{{ $statusClass }}">{{ ucfirst($inspection->status) }}</span>
                </td>
                <td>{{ $inspection->issues_found ?? 'None' }}</td>
                <td>
                  <a href="{{ route('inspections.show', $inspection) }}" class="btn btn-sm btn-icon">
                    <i class='bx bx-show'></i>
                  </a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="alert alert-info mb-0">
          <i class='bx bx-info-circle me-2'></i>
          No inspection records found for this equipment.
        </div>
        @endif
      </div>
    </div>

    <!-- Custody/Checkout History -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Custody/Checkout History</h5>
      </div>
      <div class="card-body">
        @if($equipment->custodyLogs->count() > 0)
        <div class="table-responsive">
          <table id="custodyTable" class="table table-hover">
            <thead>
              <tr>
                <th>Checked Out</th>
                <th>Checked In</th>
                <th>Custodian</th>
                <th>Duration</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach($equipment->custodyLogs as $log)
              <tr>
                <td>{{ $log->checked_out_at->format('d/m/Y H:i') }}</td>
                <td>
                  @if($log->checked_in_at)
                    {{ $log->checked_in_at->format('d/m/Y H:i') }}
                  @else
                    <span class="badge bg-warning">Still Out</span>
                  @endif
                </td>
                <td>{{ $log->custodian->name ?? 'Unknown' }}</td>
                <td>
                  @if($log->checked_in_at)
                    {{ $log->checked_out_at->diffForHumans($log->checked_in_at, true) }}
                  @else
                    {{ $log->checked_out_at->diffForHumans() }}
                  @endif
                </td>
                <td>
                  @if($log->checked_in_at)
                    <span class="badge bg-success">Returned</span>
                  @else
                    <span class="badge bg-primary">In Use</span>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="alert alert-info mb-0">
          <i class='bx bx-info-circle me-2'></i>
          No custody/checkout records found for this equipment.
        </div>
        @endif
      </div>
    </div>

    <!-- Record Timestamps -->
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-4">
            <small class="text-muted">Created</small>
            <p class="mb-0">{{ $equipment->created_at->format('d/m/Y H:i') }}</p>
          </div>
          <div class="col-md-4">
            <small class="text-muted">Last Updated</small>
            <p class="mb-0">{{ $equipment->updated_at->format('d/m/Y H:i') }}</p>
          </div>
          <div class="col-md-4">
            <small class="text-muted">Branch</small>
            <p class="mb-0">{{ $equipment->branch->name ?? 'Unknown' }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Sidebar Column -->
  <div class="col-xl-3 col-lg-4 col-md-4">
    <!-- Quick Actions -->
    <div class="card mb-4">
      <div class="card-header">
        <h6 class="mb-0">Quick Actions</h6>
      </div>
      <div class="card-body">
        <a href="{{ route('warehouse-equipment.edit', $equipment) }}" class="btn btn-primary w-100 mb-2">
          <i class='bx bx-edit me-1'></i> Edit Equipment
        </a>

        @php
          $activeCheckout = $equipment->custodyLogs()->whereNull('checked_in_at')->first();
        @endphp

        @if(!$activeCheckout && $equipment->status === 'available')
        <button type="button" class="btn btn-info w-100 mb-2" data-bs-toggle="modal" data-bs-target="#checkoutModal">
          <i class='bx bx-log-out me-1'></i> Checkout Equipment
        </button>
        @elseif($activeCheckout)
        <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#checkinModal">
          <i class='bx bx-log-in me-1'></i> Check In Equipment
        </button>
        @endif

        @if($equipment->status === 'available')
        <button type="button" class="btn btn-warning w-100 mb-2" data-bs-toggle="modal" data-bs-target="#maintenanceModal">
          <i class='bx bx-wrench me-1'></i> Mark for Maintenance
        </button>
        @endif

        <button type="button" class="btn btn-outline-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#inspectionModal">
          <i class='bx bx-check-circle me-1'></i> Record Inspection
        </button>

        @if($equipment->qr_code_path)
        <a href="{{ asset('storage/' . $equipment->qr_code_path) }}" download class="btn btn-outline-secondary w-100 mb-2">
          <i class='bx bx-download me-1'></i> Download QR Code
        </a>
        @endif

        <hr>

        <button type="button" class="btn btn-danger w-100 mb-2" data-bs-toggle="modal" data-bs-target="#deleteModal">
          <i class='bx bx-trash me-1'></i> Delete Equipment
        </button>

        <a href="{{ route('warehouse-equipment.index') }}" class="btn btn-outline-secondary w-100">
          <i class='bx bx-arrow-back me-1'></i> Back to List
        </a>
      </div>
    </div>

    <!-- Statistics Summary -->
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Usage Statistics</h6>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <small class="text-muted">Total Inspections</small>
          <h4 class="mb-0">{{ $equipment->inspections()->count() }}</h4>
        </div>
        <div class="mb-3">
          <small class="text-muted">Passed Inspections</small>
          <h4 class="mb-0 text-success">{{ $equipment->inspections()->where('status', 'passed')->count() }}</h4>
        </div>
        <div class="mb-3">
          <small class="text-muted">Failed Inspections</small>
          <h4 class="mb-0 text-danger">{{ $equipment->inspections()->where('status', 'failed')->count() }}</h4>
        </div>
        <div class="mb-3">
          <small class="text-muted">Total Checkouts</small>
          <h4 class="mb-0">{{ $equipment->custodyLogs()->count() }}</h4>
        </div>
        <div class="mb-0">
          <small class="text-muted">Currently Checked Out</small>
          <h4 class="mb-0">
            @if($activeCheckout)
              <span class="badge bg-warning">Yes</span>
            @else
              <span class="badge bg-success">No</span>
            @endif
          </h4>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Checkout Equipment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('warehouse-equipment.checkout', $equipment) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="custodian_id" class="form-label">Custodian *</label>
            <select id="custodian_id" name="custodian_id" class="form-select" required>
              <option value="">Select employee</option>
              @foreach(\App\Models\User::where('branch_id', $equipment->branch_id)->get() as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="purpose" class="form-label">Purpose</label>
            <textarea id="purpose" name="purpose" class="form-control" rows="3" placeholder="Purpose of checkout..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-info">
            <i class='bx bx-log-out me-1'></i> Checkout
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Check In Modal -->
<div class="modal fade" id="checkinModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Check In Equipment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('warehouse-equipment.checkin', $equipment) }}" method="POST">
        @csrf
        <div class="modal-body">
          @if($activeCheckout)
          <div class="alert alert-info mb-3">
            <i class='bx bx-info-circle me-2'></i>
            Currently checked out to: <strong>{{ $activeCheckout->custodian->name ?? 'Unknown' }}</strong><br>
            Since: {{ $activeCheckout->checked_out_at->format('d/m/Y H:i') }}
          </div>
          @endif
          <div class="mb-3">
            <label for="condition_notes" class="form-label">Condition Notes</label>
            <textarea id="condition_notes" name="condition_notes" class="form-control" rows="3" placeholder="Any issues or damage noticed..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class='bx bx-log-in me-1'></i> Check In
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Maintenance Modal -->
<div class="modal fade" id="maintenanceModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Mark for Maintenance</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('warehouse-equipment.maintenance', $equipment) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="maintenance_reason" class="form-label">Reason for Maintenance *</label>
            <textarea id="maintenance_reason" name="maintenance_reason" class="form-control" rows="3" required placeholder="Describe the issue or maintenance required..."></textarea>
          </div>
          <div class="alert alert-warning mb-0">
            <i class='bx bx-info-circle me-2'></i>
            This will change the equipment status to "Maintenance" and make it unavailable for checkout.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">
            <i class='bx bx-wrench me-1'></i> Mark for Maintenance
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Inspection Modal -->
<div class="modal fade" id="inspectionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Record Inspection</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('inspections.create') }}" method="GET">
        <input type="hidden" name="equipment_id" value="{{ $equipment->id }}">
        <div class="modal-body">
          <p>This will redirect you to the full inspection form.</p>
          <div class="alert alert-info mb-0">
            <i class='bx bx-info-circle me-2'></i>
            Equipment: <strong>{{ $equipment->equipment_name }}</strong> ({{ $equipment->equipment_code }})
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class='bx bx-check-circle me-1'></i> Go to Inspection Form
          </button>
        </div>
      </form>
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
<script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
<script>
$(document).ready(function() {
  'use strict';

  // Initialize DataTables for inspections
  if ($('#inspectionsTable').length) {
    $('#inspectionsTable').DataTable({
      order: [[0, 'desc']],
      pageLength: 10,
      responsive: true,
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
    });
  }

  // Initialize DataTables for custody logs
  if ($('#custodyTable').length) {
    $('#custodyTable').DataTable({
      order: [[0, 'desc']],
      pageLength: 10,
      responsive: true,
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
    });
  }
});
</script>
@endsection
