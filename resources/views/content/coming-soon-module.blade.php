@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', $moduleTitle . ' | WHS4')

@section('content')
{{-- Coming Soon Page for Modules Under Development --}}
@include('layouts.sections.flash-message')

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body text-center py-5">
        <div class="mb-4">
          <i class='bx bx-time-five display-1 text-primary'></i>
        </div>

        <h3 class="mb-3">{{ $moduleTitle }}</h3>
        <h5 class="mb-4 text-muted">Coming Soon</h5>

        <p class="mb-4">
          This module is currently under development and will be available soon.<br>
          The {{ $moduleTitle }} feature is being built with all the necessary functionality<br>
          to support your workplace health and safety requirements.
        </p>

        <div class="alert alert-info mb-4">
          <div class="d-flex align-items-center">
            <i class='bx bx-info-circle me-2'></i>
            <div>
              <strong>Development Status:</strong> This module has backend functionality ready and is pending frontend implementation.
            </div>
          </div>
        </div>

        <div class="mb-4">
          <a href="{{ route('dashboard-analytics') }}" class="btn btn-primary">
            <i class='bx bx-home-alt me-1'></i>
            Return to Dashboard
          </a>
        </div>

        <div class="text-muted small">
          <p>If you need urgent access to this functionality, please contact your system administrator.</p>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Module Information Card --}}
<div class="row mt-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Planned Features</h5>
      </div>
      <div class="card-body">
        @if(isset($plannedFeatures) && count($plannedFeatures) > 0)
          <ul class="list-unstyled mb-0">
            @foreach($plannedFeatures as $feature)
              <li class="mb-2">
                <i class='bx bx-check-circle text-success me-2'></i>
                {{ $feature }}
              </li>
            @endforeach
          </ul>
        @else
          <p class="mb-0">Feature specifications are being finalized.</p>
        @endif
      </div>
    </div>
  </div>
</div>

@endsection
