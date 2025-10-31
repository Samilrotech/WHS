@extends('layouts.layoutMaster')

@section('title', 'Assigned Vehicles')

@section('content')
@include('layouts.sections.flash-message')

@php
  use Illuminate\Support\Str;

  $activeCount = $assignments->count();
  $dailyCount = $dailyAssignments->count();
  $monthlyCount = $monthlyAssignments->count();
@endphp

<div class="whs-shell">
  <x-whs.hero
    eyebrow="Fleet Operations"
    title="Assigned Vehicles"
    subtitle="Review vehicle allocations, complete required inspections, and access log history for the vehicles in your care."
    :metric="true"
    metric-label="Active assignments"
    :metric-value="$activeCount"
    metric-caption="{{ $dailyCount }} daily · {{ $monthlyCount }} scheduled"
  />

  @if ($assignments->isEmpty())
    <x-whs.card class="whs-empty whs-empty--centered">
      <div class="whs-empty__content">
        <i class="icon-base ti ti-car whs-empty__icon"></i>
        <h3>No vehicle assigned</h3>
        <p>Your profile doesn’t have an active vehicle allocation yet. Contact your branch administrator if you’re expecting one.</p>
        <a href="{{ route('dashboard') }}" class="whs-btn-primary">Return to dashboard</a>
      </div>
    </x-whs.card>
  @endif

  @if ($dailyAssignments->isNotEmpty())
    <div class="whs-section-heading">
      <div>
        <h2>Daily prestart checks</h2>
        <p>Forklifts and other critical plant requiring a prestart each shift.</p>
      </div>
    </div>

    <div class="whs-list whs-list--staggered">
      @foreach ($dailyAssignments as $assignment)
        @php
          $vehicle = $assignment->vehicle;
          $latestDaily = $vehicle?->latestPrestartInspection;
          $dailyStatus = $latestDaily && $latestDaily->inspection_date?->isToday()
            ? 'Completed today'
            : 'Due before operating';
        @endphp

        @if(!$vehicle)
          <x-whs.card class="sensei-surface-card">
            <div class="text-muted">The vehicle linked to this assignment is no longer available. Please contact your administrator.</div>
          </x-whs.card>
          @continue
        @endif

        <x-whs.card class="sensei-surface-card">
          <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-label-danger text-uppercase">Daily prestart</span>
                <span class="badge bg-label-primary">{{ $vehicle->branch?->name ?? 'Unassigned branch' }}</span>
              </div>
              <h3 class="mb-1">{{ $vehicle->make }} {{ $vehicle->model }} <span class="text-muted fw-normal">({{ $vehicle->year }})</span></h3>
              <div class="text-muted small d-flex flex-wrap gap-3">
                <span><i class="ti ti-id me-1"></i>{{ $vehicle->registration_state ? $vehicle->registration_state . ' · ' : '' }}{{ $vehicle->registration_number }}</span>
                <span><i class="ti ti-calendar me-1"></i>Assigned {{ $assignment->assigned_date?->format('d M Y') ?? '—' }}</span>
              </div>
            </div>
            <div class="text-end">
              <span class="badge bg-label-success text-uppercase">{{ $dailyStatus }}</span>
              <p class="mb-0 text-muted small mt-2">
                Current odometer<br>
                <strong>{{ number_format($vehicle->odometer_reading ?? 0) }} km</strong>
              </p>
            </div>
          </div>

          <div class="d-flex flex-wrap gap-2 mt-4">
            <a href="{{ route('driver.vehicle-inspections.create', $assignment->id) }}" class="btn btn-primary">
              <i class="ti ti-clipboard-check me-1"></i>Start daily prestart
            </a>
            <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-outline-secondary">
              <i class="ti ti-car me-1"></i>View vehicle
            </a>
            <a href="{{ route('driver.vehicle-inspections.monthly.create', $assignment->id) }}" class="btn btn-text-secondary">
              <i class="ti ti-calendar-stats me-1"></i>Monthly inspection
            </a>
          </div>

          <div class="mt-4 border-top pt-3 sensei-meta-grid">
            <div>
              <span class="sensei-meta-label">Last daily inspection</span>
              <span>{{ $latestDaily?->inspection_date?->format('d M Y · H:i') ?? 'Not recorded yet' }}</span>
            </div>
            <div>
              <span class="sensei-meta-label">Assignment purpose</span>
              <span>{{ $assignment->purpose ?? 'Not specified' }}</span>
            </div>
          </div>
        </x-whs.card>
      @endforeach
    </div>
  @endif

  @if ($monthlyAssignments->isNotEmpty())
    <div class="whs-section-heading mt-5">
      <div>
        <h2>Scheduled inspections</h2>
        <p>Monthly fleet checks and vehicles that only require periodic reporting.</p>
      </div>
    </div>

    <div class="whs-list whs-list--staggered">
      @foreach ($monthlyAssignments as $assignment)
        @php
          $vehicle = $assignment->vehicle;
          $nextDue = $vehicle?->inspection_due_date;
          $overdue = $nextDue && $nextDue->isPast();
        @endphp

        @if(!$vehicle)
          <x-whs.card class="sensei-surface-card">
            <div class="text-muted">The vehicle linked to this assignment is no longer available. Please contact your administrator.</div>
          </x-whs.card>
          @continue
        @endif

        <x-whs.card class="sensei-surface-card">
          <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-label-info text-uppercase">Monthly schedule</span>
                <span class="badge bg-label-primary">{{ $vehicle->branch?->name ?? 'Unassigned branch' }}</span>
              </div>
              <h3 class="mb-1">{{ $vehicle->make }} {{ $vehicle->model }} <span class="text-muted fw-normal">({{ $vehicle->year }})</span></h3>
              <div class="text-muted small d-flex flex-wrap gap-3">
                <span><i class="ti ti-id me-1"></i>{{ $vehicle->registration_state ? $vehicle->registration_state . ' · ' : '' }}{{ $vehicle->registration_number }}</span>
                <span><i class="ti ti-calendar me-1"></i>Assigned {{ $assignment->assigned_date?->format('d M Y') ?? '—' }}</span>
              </div>
            </div>
            <div class="text-end">
              <span class="badge {{ $overdue ? 'bg-label-danger' : 'bg-label-success' }} text-uppercase">
                {{ $overdue ? 'Overdue' : 'On schedule' }}
              </span>
              <p class="mb-0 text-muted small mt-2">
                Next inspection due<br>
                <strong>{{ $nextDue?->format('d M Y') ?? 'Not scheduled' }}</strong>
              </p>
            </div>
          </div>

          <div class="d-flex flex-wrap gap-2 mt-4">
            <a href="{{ route('driver.vehicle-inspections.monthly.create', $assignment->id) }}" class="btn btn-primary">
              <i class="ti ti-calendar-plus me-1"></i>Start monthly inspection
            </a>
            <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-outline-secondary">
              <i class="ti ti-car me-1"></i>View vehicle
            </a>
          </div>

          <div class="mt-4 border-top pt-3 sensei-meta-grid">
            <div>
              <span class="sensei-meta-label">Last inspection</span>
              <span>{{ optional($vehicle?->latestInspection?->inspection_date)->format('d M Y · H:i') ?? 'Not recorded' }}</span>
            </div>
            <div>
              <span class="sensei-meta-label">Assignment purpose</span>
              <span>{{ $assignment->purpose ?? 'Not specified' }}</span>
            </div>
          </div>
        </x-whs.card>
      @endforeach
    </div>
  @endif
</div>
@endsection

