@extends('layouts.layoutMaster')

@section('title', 'Cost Analysis')

@section('content')
{{-- DO NOT CHANGE LAYOUT  WHS4 Horizontal Avatar Pattern (see FRONTEND_STANDARDS.md) --}}
@include('layouts.sections.flash-message')

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0">Cost Analysis</h4>
  <a href="{{ route('vehicle-reporting.index', ['period' => $period]) }}" class="btn btn-outline-secondary">
    <i class="bx bx-left-arrow-alt me-1"></i> Back to Dashboard
  </a>
</div>

<!-- Cost Stats - Vuexy CRM Horizontal Avatar Pattern -->
<div class="row g-3 mb-4">
  <!-- Card 1: Total Costs -->
  <div class="col-md-3 col-sm-6">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="bx bx-dollar-circle fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Total Costs</h6>
            <h4 class="mb-0">${{ number_format($costMetrics['total_costs'] ?? 0, 2) }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Card 2: Maintenance Costs -->
  <div class="col-md-3 col-sm-6">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="bx bx-wrench fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Maintenance</h6>
            <h4 class="mb-0 text-warning">${{ number_format($costMetrics['maintenance_costs'] ?? 0, 2) }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Card 3: Fuel Costs -->
  <div class="col-md-3 col-sm-6">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-info">
              <i class="bx bx-gas-pump fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Fuel</h6>
            <h4 class="mb-0 text-info">${{ number_format($costMetrics['fuel_costs'] ?? 0, 2) }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Card 4: Cost Per Vehicle -->
  <div class="col-md-3 col-sm-6">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-success">
              <i class="bx bx-car fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Cost/Vehicle</h6>
            <h4 class="mb-0">${{ number_format($costMetrics['cost_per_vehicle'] ?? 0, 2) }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Top Cost Vehicles Table -->
@if(!empty($costMetrics['top_cost_vehicles']))
<div class="card shadow-none">
  <div class="card-header">
    <h5 class="mb-0">Top 10 Cost Vehicles</h5>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Rank</th>
            <th>Vehicle ID</th>
            <th class="text-end">Total Cost</th>
          </tr>
        </thead>
        <tbody>
          @foreach($costMetrics['top_cost_vehicles'] as $index => $vehicle)
          <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $vehicle->vehicle_id }}</td>
            <td class="text-end">
              <span class="badge bg-label-{{ $index === 0 ? 'danger' : ($index < 3 ? 'warning' : 'primary') }}">
                ${{ number_format($vehicle->total_cost, 2) }}
              </span>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endif
@endsection
