@extends('layouts.layoutMaster')

@section('title', 'Vehicle Details')

@section('content')
@include('layouts.sections.flash-message')

<div class="row">
  <!-- Vehicle Details Card -->
  <div class="col-md-8">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $vehicle->registration_number }} - {{ $vehicle->make }} {{ $vehicle->model }}</h5>
        <div>
          @if($vehicle->status === 'active')
            <span class="badge bg-success">Active</span>
          @elseif($vehicle->status === 'maintenance')
            <span class="badge bg-warning">Maintenance</span>
          @elseif($vehicle->status === 'inactive')
            <span class="badge bg-secondary">Inactive</span>
          @else
            <span class="badge bg-dark">{{ ucfirst($vehicle->status) }}</span>
          @endif
        </div>
      </div>
      <div class="card-body">
        <!-- Basic Vehicle Information -->
        <h6 class="text-muted mb-3">Vehicle Information</h6>
        <div class="row mb-3">
          <div class="col-md-6">
            <p class="mb-2"><strong>Registration Number:</strong> {{ $vehicle->registration_number }}</p>
            <p class="mb-2"><strong>Make:</strong> {{ $vehicle->make }}</p>
            <p class="mb-2"><strong>Model:</strong> {{ $vehicle->model }}</p>
            <p class="mb-2"><strong>Year:</strong> {{ $vehicle->year }}</p>
          </div>
          <div class="col-md-6">
            @if($vehicle->vin_number)
              <p class="mb-2"><strong>VIN:</strong> {{ $vehicle->vin_number }}</p>
            @endif
            @if($vehicle->color)
              <p class="mb-2"><strong>Color:</strong> {{ $vehicle->color }}</p>
            @endif
            @if($vehicle->fuel_type)
              <p class="mb-2"><strong>Fuel Type:</strong> {{ $vehicle->fuel_type }}</p>
            @endif
            <p class="mb-2"><strong>Odometer:</strong> {{ number_format($vehicle->odometer_reading) }} km</p>
          </div>
        </div>

        <hr class="my-3">

        <!-- Assignment Status -->
        <h6 class="text-muted mb-3">Assignment Status</h6>
        @if($vehicle->isAssigned())
          <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class='icon-base ti ti-user-check me-2'></i>
            <div>
              <strong>Currently Assigned To:</strong> {{ $vehicle->currentAssignment->user->name }}<br>
              <small class="text-muted">Assigned on {{ $vehicle->currentAssignment->assigned_date->format('d/m/Y') }}</small>
              @if($vehicle->currentAssignment->purpose)
                <br><small class="text-muted">Purpose: {{ $vehicle->currentAssignment->purpose }}</small>
              @endif
            </div>
          </div>
        @else
          <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class='icon-base ti ti-circle-check me-2'></i>
            <strong>Available for Assignment</strong>
          </div>
        @endif

        <hr class="my-3">

        <!-- Financial Information -->
        @if($vehicle->purchase_date || $vehicle->purchase_price)
          <h6 class="text-muted mb-3">Financial Information</h6>
          <div class="row mb-3">
            <div class="col-md-6">
              @if($vehicle->purchase_date)
                <p class="mb-2"><strong>Purchase Date:</strong> {{ $vehicle->purchase_date->format('d/m/Y') }}</p>
              @endif
              @if($vehicle->purchase_price)
                <p class="mb-2"><strong>Purchase Price:</strong> ${{ number_format($vehicle->purchase_price, 2) }}</p>
              @endif
              @if($vehicle->current_value)
                <p class="mb-2"><strong>Current Value:</strong> ${{ number_format($vehicle->current_value, 2) }}</p>
              @endif
            </div>
            <div class="col-md-6">
              <p class="mb-2"><strong>Depreciation Method:</strong> {{ ucwords(str_replace('_', ' ', $vehicle->depreciation_method)) }}</p>
              <p class="mb-2"><strong>Depreciation Rate:</strong> {{ $vehicle->depreciation_rate }}%</p>
              @if($total_depreciation > 0)
                <p class="mb-2"><strong>Total Depreciation:</strong> ${{ number_format($total_depreciation, 2) }}</p>
              @endif
            </div>
          </div>
          <hr class="my-3">
        @endif

        <!-- Insurance Information -->
        @if($vehicle->insurance_company || $vehicle->insurance_policy_number)
          <h6 class="text-muted mb-3">Insurance Information</h6>
          <div class="row mb-3">
            <div class="col-md-6">
              @if($vehicle->insurance_company)
                <p class="mb-2"><strong>Insurance Company:</strong> {{ $vehicle->insurance_company }}</p>
              @endif
              @if($vehicle->insurance_policy_number)
                <p class="mb-2"><strong>Policy Number:</strong> {{ $vehicle->insurance_policy_number }}</p>
              @endif
            </div>
            <div class="col-md-6">
              @if($vehicle->insurance_expiry_date)
                <p class="mb-2">
                  <strong>Expiry Date:</strong>
                  @if($vehicle->isInsuranceExpiring())
                    <span class="badge bg-danger">{{ $vehicle->insurance_expiry_date->format('d/m/Y') }}</span>
                  @else
                    {{ $vehicle->insurance_expiry_date->format('d/m/Y') }}
                  @endif
                </p>
              @endif
              @if($vehicle->insurance_premium)
                <p class="mb-2"><strong>Premium:</strong> ${{ number_format($vehicle->insurance_premium, 2) }}</p>
              @endif
            </div>
          </div>
          <hr class="my-3">
        @endif

        <!-- Compliance Information -->
        <h6 class="text-muted mb-3">Compliance</h6>
        <div class="row mb-3">
          <div class="col-md-6">
            @if($vehicle->rego_expiry_date)
              <p class="mb-2">
                <strong>Registration Expiry:</strong>
                @if($vehicle->isRegistrationExpiring())
                  <span class="badge bg-danger">{{ $vehicle->rego_expiry_date->format('d/m/Y') }}</span>
                @else
                  {{ $vehicle->rego_expiry_date->format('d/m/Y') }}
                @endif
              </p>
            @else
              <p class="mb-2 text-muted">Registration expiry not set</p>
            @endif
          </div>
          <div class="col-md-6">
            @if($vehicle->inspection_due_date)
              <p class="mb-2">
                <strong>Inspection Due:</strong>
                @if($vehicle->isInspectionDue())
                  <span class="badge bg-danger">{{ $vehicle->inspection_due_date->format('d/m/Y') }}</span>
                @else
                  {{ $vehicle->inspection_due_date->format('d/m/Y') }}
                @endif
              </p>
            @else
              <p class="mb-2 text-muted">Inspection due date not set</p>
            @endif
          </div>
        </div>

        <!-- Additional Notes -->
        @if($vehicle->notes)
          <hr class="my-3">
          <h6 class="text-muted mb-3">Notes</h6>
          <p class="mb-0">{{ $vehicle->notes }}</p>
        @endif
      </div>
    </div>

    <!-- Depreciation Schedule Card -->
    @if($depreciation_schedule && count($depreciation_schedule) > 0)
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Depreciation Schedule</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Year</th>
                  <th>Opening Value</th>
                  <th>Depreciation</th>
                  <th>Closing Value</th>
                </tr>
              </thead>
              <tbody>
                @foreach($depreciation_schedule as $schedule)
                  <tr>
                    <td>{{ $schedule['year'] }}</td>
                    <td>${{ number_format($schedule['opening_value'], 2) }}</td>
                    <td>${{ number_format($schedule['depreciation'], 2) }}</td>
                    <td>${{ number_format($schedule['closing_value'], 2) }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    @endif

    <!-- Service History Card -->
    @if($vehicle->serviceRecords->count() > 0)
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Service History</h5>
          <span class="badge bg-label-primary">{{ $service_stats['count'] }} services</span>
        </div>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-4">
              <small class="text-muted">Total Services</small>
              <h6 class="mb-0">{{ $service_stats['count'] }}</h6>
            </div>
            <div class="col-md-4">
              <small class="text-muted">Average Cost</small>
              <h6 class="mb-0">${{ number_format($service_stats['average_cost'], 2) }}</h6>
            </div>
            <div class="col-md-4">
              <small class="text-muted">Last Service</small>
              <h6 class="mb-0">{{ $service_stats['last_service'] ? $service_stats['last_service']->format('d/m/Y') : 'N/A' }}</h6>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Type</th>
                  <th>Cost</th>
                  <th>Odometer</th>
                </tr>
              </thead>
              <tbody>
                @foreach($vehicle->serviceRecords->take(10) as $service)
                  <tr>
                    <td>{{ $service->service_date->format('d/m/Y') }}</td>
                    <td>{{ $service->service_type }}</td>
                    <td>${{ number_format($service->cost, 2) }}</td>
                    <td>{{ number_format($service->odometer_reading) }} km</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    @endif

    <!-- Action Buttons -->
    <div class="d-flex gap-2 mb-4">
      @if(!$vehicle->isAssigned())
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignModal">
          <i class='icon-base ti ti-user-plus me-1'></i> Assign Vehicle
        </button>
      @else
        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#returnModal">
          <i class='icon-base ti ti-arrow-back-up me-1'></i> Return Vehicle
        </button>
      @endif
      <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-primary">
        <i class='icon-base ti ti-edit me-1'></i> Edit
      </a>
      @if($qr_code_url)
        <a href="{{ $qr_code_url }}" target="_blank" class="btn btn-info">
          <i class='icon-base ti ti-qrcode me-1'></i> View QR Code
        </a>
      @endif
      <a href="{{ route('vehicles.index') }}" class="btn btn-outline-secondary">
        <i class='icon-base ti ti-arrow-left me-1'></i> Back to List
      </a>
      <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST" class="d-inline ms-auto">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this vehicle?')">
          <i class='icon-base ti ti-trash me-1'></i> Delete
        </button>
      </form>
    </div>
  </div>

  <!-- Info Sidebar -->
  <div class="col-md-4">
    <!-- Quick Stats Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Quick Stats</h5>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <small class="text-muted">Branch</small>
          <h6 class="mb-0">{{ $vehicle->branch->name }}</h6>
        </div>
        <div class="mb-3">
          <small class="text-muted">Status</small>
          <h6 class="mb-0">
            @if($vehicle->status === 'active')
              <span class="badge bg-success">Active</span>
            @elseif($vehicle->status === 'maintenance')
              <span class="badge bg-warning">Maintenance</span>
            @elseif($vehicle->status === 'inactive')
              <span class="badge bg-secondary">Inactive</span>
            @else
              <span class="badge bg-dark">{{ ucfirst($vehicle->status) }}</span>
            @endif
          </h6>
        </div>
        <div class="mb-3">
          <small class="text-muted">Created</small>
          <h6 class="mb-0">{{ $vehicle->created_at->format('d/m/Y') }}</h6>
        </div>
        <div class="mb-0">
          <small class="text-muted">Last Updated</small>
          <h6 class="mb-0">{{ $vehicle->updated_at->format('d/m/Y H:i') }}</h6>
        </div>
      </div>
    </div>

    <!-- Assignment History Card -->
    @if($vehicle->assignments->count() > 0)
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Assignment History</h5>
        </div>
        <div class="card-body">
          <ul class="timeline mb-0">
            @foreach($vehicle->assignments->take(5) as $assignment)
              <li class="timeline-item timeline-item-transparent">
                <span class="timeline-point {{ $assignment->returned_date ? 'timeline-point-secondary' : 'timeline-point-primary' }}"></span>
                <div class="timeline-event">
                  <div class="timeline-header mb-1">
                    <h6 class="mb-0">{{ $assignment->user->name }}</h6>
                    <small class="text-muted">{{ $assignment->assigned_date->format('d/m/Y') }}</small>
                  </div>
                  @if($assignment->returned_date)
                    <p class="mb-0 text-muted">Returned {{ $assignment->returned_date->format('d/m/Y') }}</p>
                    @if($assignment->odometer_start && $assignment->odometer_end)
                      <small class="text-muted">Distance: {{ number_format($assignment->odometer_end - $assignment->odometer_start) }} km</small>
                    @endif
                  @else
                    <p class="mb-0 text-primary"><strong>Currently Assigned</strong></p>
                  @endif
                </div>
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    @endif
  </div>
</div>

<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('vehicles.assign', $vehicle) }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Assign Vehicle</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="user_id" class="form-label">Assign To *</label>
            <select id="user_id" name="user_id" class="form-select" required>
              <option value="">Select user</option>
              {{-- Users will be populated via controller --}}
            </select>
          </div>
          <div class="mb-3">
            <label for="assigned_date" class="form-label">Assignment Date *</label>
            <input type="date" id="assigned_date" name="assigned_date" class="form-control" value="{{ date('Y-m-d') }}" required>
          </div>
          <div class="mb-3">
            <label for="odometer_start" class="form-label">Current Odometer Reading</label>
            <input type="number" id="odometer_start" name="odometer_start" class="form-control" value="{{ $vehicle->odometer_reading }}" min="0">
          </div>
          <div class="mb-3">
            <label for="purpose" class="form-label">Purpose</label>
            <input type="text" id="purpose" name="purpose" class="form-control" placeholder="e.g., Site visit, Delivery">
          </div>
          <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Assign Vehicle</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Return Modal -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('vehicles.return', $vehicle) }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Return Vehicle</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="returned_date" class="form-label">Return Date *</label>
            <input type="date" id="returned_date" name="returned_date" class="form-control" value="{{ date('Y-m-d') }}" required>
          </div>
          <div class="mb-3">
            <label for="odometer_end" class="form-label">Final Odometer Reading</label>
            <input type="number" id="odometer_end" name="odometer_end" class="form-control" value="{{ $vehicle->odometer_reading }}" min="{{ $vehicle->currentAssignment?->odometer_start ?? 0 }}">
          </div>
          <div class="mb-3">
            <label for="return_notes" class="form-label">Return Notes</label>
            <textarea id="return_notes" name="notes" class="form-control" rows="3" placeholder="Any issues or observations during use..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">Return Vehicle</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

