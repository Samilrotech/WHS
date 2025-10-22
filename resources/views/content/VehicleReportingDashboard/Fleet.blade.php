@extends('layouts.layoutMaster')

@section('title', 'Fleet Overview')

@section('content')
{{-- DO NOT CHANGE LAYOUT  WHS4 Horizontal Avatar Pattern (see FRONTEND_STANDARDS.md) --}}
@include('layouts.sections.flash-message')

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0">Fleet Overview</h4>
  <a href="{{ route('vehicle-reporting.index', ['period' => $period]) }}" class="btn btn-outline-secondary">
    <i class="bx bx-left-arrow-alt me-1"></i> Back to Dashboard
  </a>
</div>

<!-- Fleet Stats - Vuexy CRM Horizontal Avatar Pattern -->
<div class="row g-3 mb-4">
  <!-- Card 1: Total Vehicles -->
  <div class="col-md-2 col-sm-6">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="bx bx-car fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Total</h6>
            <h4 class="mb-0">{{ $fleetMetrics['total_vehicles'] ?? 0 }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Card 2: Active -->
  <div class="col-md-2 col-sm-6">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-success">
              <i class="bx bx-check-circle fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Active</h6>
            <h4 class="mb-0 text-success">{{ $fleetMetrics['active'] ?? 0 }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Card 3: In Maintenance -->
  <div class="col-md-2 col-sm-6">
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
            <h4 class="mb-0 text-warning">{{ $fleetMetrics['in_maintenance'] ?? 0 }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Card 4: Out of Service -->
  <div class="col-md-2 col-sm-6">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-danger">
              <i class="bx bx-x-circle fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Out of Service</h6>
            <h4 class="mb-0 text-danger">{{ $fleetMetrics['out_of_service'] ?? 0 }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Card 5: Availability Rate -->
  <div class="col-md-4 col-sm-12">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-info">
              <i class="bx bx-trending-up fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Availability Rate</h6>
            <h4 class="mb-0">{{ number_format($fleetMetrics['availability_rate'] ?? 0, 1) }}%</h4>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Utilization Metrics -->
<div class="row g-3 mb-4">
  <div class="col-md-3 col-sm-6">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="bx bx-map fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Total Distance</h6>
            <h4 class="mb-0">{{ number_format($utilization['total_distance_km'] ?? 0) }} km</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3 col-sm-6">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-info">
              <i class="bx bx-time fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Total Hours</h6>
            <h4 class="mb-0">{{ number_format($utilization['total_hours'] ?? 0) }} hrs</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3 col-sm-6">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-success">
              <i class="bx bx-map-alt fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Avg Dist/Vehicle</h6>
            <h4 class="mb-0">{{ number_format($utilization['avg_distance_per_vehicle'] ?? 0) }} km</h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-3 col-sm-6">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="bx bx-time-five fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Avg Hrs/Vehicle</h6>
            <h4 class="mb-0">{{ number_format($utilization['avg_hours_per_vehicle'] ?? 0) }} hrs</h4>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
