@extends('layouts.layoutMaster')

@section('title', 'Compliance Metrics')

@section('content')
{{-- DO NOT CHANGE LAYOUT  WHS4 Horizontal Avatar Pattern (see FRONTEND_STANDARDS.md) --}}
@include('layouts.sections.flash-message')

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0">Compliance Metrics</h4>
  <a href="{{ route('vehicle-reporting.index', ['period' => $period]) }}" class="btn btn-outline-secondary">
    <i class="bx bx-left-arrow-alt me-1"></i> Back to Dashboard
  </a>
</div>

<!-- Compliance Overview Stats - Vuexy CRM Horizontal Avatar Pattern -->
<div class="row g-3 mb-4">
  <!-- Card 1: Compliance Rate -->
  <div class="col-md-4 col-sm-12">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-{{ $complianceMetrics['compliance_rate'] >= 90 ? 'success' : ($complianceMetrics['compliance_rate'] >= 70 ? 'warning' : 'danger') }}">
              <i class="bx bx-shield-quarter fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Compliance Rate</h6>
            <h4 class="mb-0 text-{{ $complianceMetrics['compliance_rate'] >= 90 ? 'success' : ($complianceMetrics['compliance_rate'] >= 70 ? 'warning' : 'danger') }}">
              {{ number_format($complianceMetrics['compliance_rate'] ?? 0, 1) }}%
            </h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Card 2: Compliant Vehicles -->
  <div class="col-md-4 col-sm-12">
    <div class="card shadow-none">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-success">
              <i class="bx bx-check-circle fs-4"></i>
            </span>
          </div>
          <div class="card-info">
            <h6 class="mb-0">Compliant Vehicles</h6>
            <h4 class="mb-0 text-success">{{ $complianceMetrics['compliant'] ?? 0 }} / {{ $complianceMetrics['total_vehicles'] ?? 0 }}</h4>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Registration Compliance -->
<div class="row g-3 mb-4">
  <div class="col-md-6 col-sm-12">
    <div class="card shadow-none">
      <div class="card-header">
        <h5 class="mb-0">Registration Status</h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12">
            <div class="d-flex align-items-center">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-warning">
                  <i class="bx bx-calendar-exclamation fs-4"></i>
                </span>
              </div>
              <div class="card-info">
                <h6 class="mb-0">Expiring Soon (30 days)</h6>
                <h4 class="mb-0 text-warning">{{ $complianceMetrics['registration_expiring_soon'] ?? 0 }}</h4>
              </div>
            </div>
          </div>
          <div class="col-12">
            <div class="d-flex align-items-center">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-danger">
                  <i class="bx bx-calendar-x fs-4"></i>
                </span>
              </div>
              <div class="card-info">
                <h6 class="mb-0">Expired</h6>
                <h4 class="mb-0 text-danger">{{ $complianceMetrics['registration_expired'] ?? 0 }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Insurance Compliance -->
  <div class="col-md-6 col-sm-12">
    <div class="card shadow-none">
      <div class="card-header">
        <h5 class="mb-0">Insurance Status</h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12">
            <div class="d-flex align-items-center">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-warning">
                  <i class="bx bx-calendar-exclamation fs-4"></i>
                </span>
              </div>
              <div class="card-info">
                <h6 class="mb-0">Expiring Soon (30 days)</h6>
                <h4 class="mb-0 text-warning">{{ $complianceMetrics['insurance_expiring_soon'] ?? 0 }}</h4>
              </div>
            </div>
          </div>
          <div class="col-12">
            <div class="d-flex align-items-center">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-danger">
                  <i class="bx bx-calendar-x fs-4"></i>
                </span>
              </div>
              <div class="card-info">
                <h6 class="mb-0">Expired</h6>
                <h4 class="mb-0 text-danger">{{ $complianceMetrics['insurance_expired'] ?? 0 }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Alerts Section -->
@if(($complianceMetrics['registration_expired'] ?? 0) > 0 || ($complianceMetrics['insurance_expired'] ?? 0) > 0)
<div class="card shadow-none border-danger">
  <div class="card-body">
    <h5 class="text-danger mb-3"><i class="bx bx-error-circle me-2"></i> Immediate Action Required</h5>
    @if(($complianceMetrics['registration_expired'] ?? 0) > 0)
    <p class="mb-2"><strong>{{ $complianceMetrics['registration_expired'] }} vehicle(s)</strong> with expired registration - Vehicle cannot be driven legally</p>
    @endif
    @if(($complianceMetrics['insurance_expired'] ?? 0) > 0)
    <p class="mb-0"><strong>{{ $complianceMetrics['insurance_expired'] }} vehicle(s)</strong> with expired insurance - High financial risk</p>
    @endif
  </div>
</div>
@endif
@endsection
