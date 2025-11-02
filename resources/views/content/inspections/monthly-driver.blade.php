@extends('layouts.layoutMaster')

@section('title', 'Monthly Vehicle Inspection')

@section('content')
@include('layouts.sections.flash-message')

@php
  $conditionOptions = [
    'good' => ['label' => 'Good', 'badge' => 'success'],
    'attention' => ['label' => 'Needs attention', 'badge' => 'warning'],
    'poor' => ['label' => 'Poor', 'badge' => 'danger'],
  ];

  $conditionChecks = [
    'exterior_condition' => ['label' => 'Exterior condition', 'help' => 'Overall paint, panels, fittings, and accessories.'],
    'lights_condition' => ['label' => 'Lights, mirrors, glass, wipers', 'help' => 'Headlights, indicators, mirrors, windscreen, wipers.'],
    'body_condition' => ['label' => 'Body condition (scratches, dents, rust)', 'help' => 'Report any new marks, dents, or corrosion.'],
    'interior_condition' => ['label' => 'Interior condition', 'help' => 'Cab cleanliness, trim, dashboard, floor mats.'],
    'seatbelt_condition' => ['label' => 'Seats and seatbelts', 'help' => 'Seat mounts secure, belts latch and retract correctly.'],
    'tire_condition' => ['label' => 'Tyre condition', 'help' => 'Visible damage, wear patterns, inflation.'],
    'tread_condition' => ['label' => 'Tyre tread condition', 'help' => 'Tread depth across each tyre.'],
  ];

  $binaryChecks = [
    'dashboard_lights' => ['label' => 'Dashboard warning lights working', 'help' => 'No persistent warning lights with ignition on.'],
    'air_conditioning' => ['label' => 'Air conditioning / heater operational', 'help' => 'Blows at expected temperature in both modes.'],
    'brakes_normal' => ['label' => 'Brakes feel normal', 'help' => 'Pedal response and stopping distance feel correct.'],
    'steering_smooth' => ['label' => 'Steering smooth', 'help' => 'Vehicle tracks straight with no stiffness or pull.'],
    'noise_smoke' => ['label' => 'No unusual noise or smoke', 'help' => 'No new engine, exhaust, or driveline noises/smoke.'],
  ];
@endphp

<div class="row justify-content-center">
  <div class="col-xl-8 col-lg-9">
    @if (!$assignment || !$vehicle)
      <div class="card sensei-surface-card border border-dashed rounded-3">
        <div class="card-body text-center py-5">
          <div class="avatar avatar-xl mb-3 bg-label-primary">
            <i class="ti ti-alert-triangle fs-3"></i>
          </div>
          <h4 class="mb-2">Vehicle assignment unavailable</h4>
          <p class="text-muted mb-4">We could not load your vehicle details. Please return to the dashboard and try again.</p>
          <a href="{{ route('driver.vehicle-inspections.index') }}" class="btn btn-outline-primary">
            <i class="ti ti-arrow-left me-1"></i> Back to vehicles
          </a>
        </div>
      </div>
    @else
      <div class="card border-0 shadow-sm sensei-surface-card sensei-glass mb-4">
        <div class="card-body p-4 p-lg-5">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
            <div>
              <span class="badge bg-label-info text-uppercase mb-2">Monthly inspection</span>
              <h3 class="mb-1 text-capitalize">{{ $vehicle->make }} {{ $vehicle->model }} <span class="fw-normal text-muted">({{ $vehicle->year }})</span></h3>
              <div class="d-flex flex-wrap align-items-center gap-3 text-muted small">
                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2">
                  <i class="ti ti-id me-1"></i>{{ $vehicle->registration_state ? $vehicle->registration_state . ' · ' : '' }}{{ $vehicle->registration_number }}
                </span>
                <span><i class="ti ti-map-pin me-1"></i>{{ $vehicle->branch?->name ?? 'Branch pending' }}</span>
                <span><i class="ti ti-calendar me-1"></i>Assigned {{ optional($assignment->assigned_date)->format('d M Y') ?? '—' }}</span>
              </div>
            </div>
            <div class="text-md-end w-100 w-md-auto">
              <p class="text-muted mb-1 small text-uppercase">Current odometer</p>
              <h4 class="mb-3">{{ number_format($vehicle->odometer_reading ?? 0) }} km</h4>
              <div class="alert alert-info mb-0 small py-2 px-3">
                <i class="ti ti-camera me-1"></i> Photos are required for tyres, sides, and interior each month.
                <div class="mt-1">
                  <i class="ti ti-bolt me-1"></i> <strong>Instant upload:</strong> Photos are automatically optimized before upload for faster submission.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <form action="{{ route('driver.vehicle-inspections.monthly.store', $assignment) }}" method="POST" enctype="multipart/form-data" class="sensei-form-card needs-validation" novalidate>
        @csrf

        <div class="card sensei-surface-card mb-4 border-0">
          <div class="card-body p-4">
            <div class="row g-4">
              <div class="col-md-6">
                <label for="odometer_reading" class="form-label">Current odometer *</label>
                <div class="input-group">
                  <input
                    type="number"
                    class="form-control @error('odometer_reading') is-invalid @enderror"
                    id="odometer_reading"
                    name="odometer_reading"
                    min="0"
                    value="{{ old('odometer_reading', $vehicle->odometer_reading ?? null) }}"
                    required>
                  <span class="input-group-text">km</span>
                </div>
                @error('odometer_reading')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6">
                <label for="location" class="form-label">Inspection location <span class="text-muted">(optional)</span></label>
                <input
                  type="text"
                  class="form-control @error('location') is-invalid @enderror"
                  id="location"
                  name="location"
                  value="{{ old('location') }}"
                  placeholder="Depot yard, customer site, roadside">
                @error('location')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
        </div>

        <div class="card sensei-surface-card mb-4 border-0">
          <div class="card-header bg-transparent border-0 pb-0">
            <h5 class="mb-1">Vehicle condition</h5>
            <p class="text-muted mb-0">Select the option that best describes each area. Choose “Needs attention” or “Poor” if anything requires follow-up.</p>
          </div>
          <div class="card-body p-4">
            @foreach($conditionChecks as $name => $meta)
              <div class="monthly-check border rounded-3 p-3 mb-3" data-condition-group>
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                  <div>
                    <label class="form-label mb-1">{{ $meta['label'] }} *</label>
                    <p class="text-muted small mb-0">{{ $meta['help'] }}</p>
                  </div>
                </div>
                <div class="monthly-check__options" role="group" aria-label="{{ $meta['label'] }}">
                  @foreach($conditionOptions as $value => $option)
                    <input
                      type="radio"
                      class="btn-check"
                      name="{{ $name }}"
                      id="{{ $name }}-{{ $value }}"
                      value="{{ $value }}"
                      {{ old($name, 'good') === $value ? 'checked' : '' }}
                      required>
                    <label class="btn btn-outline-{{ $option['badge'] }} px-3" for="{{ $name }}-{{ $value }}">
                      {{ $option['label'] }}
                    </label>
                  @endforeach
                </div>
                @error($name)
                  <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                @enderror
              </div>
            @endforeach
          </div>
        </div>

        <div class="card sensei-surface-card mb-4 border-0">
          <div class="card-header bg-transparent border-0 pb-0">
            <h5 class="mb-1">System checks</h5>
            <p class="text-muted mb-0">Confirm the essential systems are operating correctly.</p>
          </div>
          <div class="card-body p-4">
            @foreach($binaryChecks as $name => $meta)
              <div class="monthly-binary border rounded-3 p-3 mb-3" data-binary-group>
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                  <div>
                    <label class="form-label mb-1">{{ $meta['label'] }} *</label>
                    <p class="text-muted small mb-0">{{ $meta['help'] }}</p>
                  </div>
                </div>
                <div class="monthly-binary__options" role="group" aria-label="{{ $meta['label'] }}">
                  <input
                    type="radio"
                    class="btn-check"
                    name="{{ $name }}"
                    id="{{ $name }}-yes"
                    value="yes"
                    {{ old($name, 'yes') === 'yes' ? 'checked' : '' }}
                    required>
                  <label class="btn btn-outline-success px-3" for="{{ $name }}-yes">
                    <i class="ti ti-circle-check me-1"></i>Yes
                  </label>

                  <input
                    type="radio"
                    class="btn-check"
                    name="{{ $name }}"
                    id="{{ $name }}-no"
                    value="no"
                    {{ old($name) === 'no' ? 'checked' : '' }}
                    required>
                  <label class="btn btn-outline-danger px-3" for="{{ $name }}-no">
                    <i class="ti ti-alert-circle me-1"></i>No
                  </label>
                </div>
                @error($name)
                  <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                @enderror
              </div>
            @endforeach
          </div>
        </div>

        <div class="card sensei-surface-card mb-4 border-0">
          <div class="card-header bg-transparent border-0 pb-0">
            <h5 class="mb-1">Tyre photo evidence *</h5>
            <p class="text-muted mb-0">Upload a clear photo of each tyre showing tread and sidewall.</p>
          </div>
          <div class="card-body p-4">
            <div class="row g-3">
              @php
                $tyrePhotos = [
                  'tire_front_left_photo' => 'Front left tyre',
                  'tire_front_right_photo' => 'Front right tyre',
                  'tire_rear_left_photo' => 'Rear left tyre',
                  'tire_rear_right_photo' => 'Rear right tyre',
                ];
              @endphp
              @foreach($tyrePhotos as $name => $label)
                <div class="col-md-6">
                  <label for="{{ $name }}" class="form-label">{{ $label }} *</label>
                  <input
                    type="file"
                    class="form-control @error($name) is-invalid @enderror"
                    id="{{ $name }}"
                    name="{{ $name }}"
                    accept="image/*"
                    required>
                  @error($name)
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
              @endforeach
            </div>
          </div>
        </div>

        <div class="card sensei-surface-card mb-4 border-0">
          <div class="card-header bg-transparent border-0 pb-0">
            <h5 class="mb-1">Repairs or issues</h5>
            <p class="text-muted mb-0">Tell us about any problems and include a supporting photo if possible.</p>
          </div>
          <div class="card-body p-4">
            <div class="mb-3">
              <label for="issue_description" class="form-label">Any problem noticed? <span class="text-muted">(optional)</span></label>
              <textarea
                id="issue_description"
                name="issue_description"
                rows="3"
                class="form-control @error('issue_description') is-invalid @enderror"
                placeholder="Describe any faults, noises, or concerns.">{{ old('issue_description') }}</textarea>
              @error('issue_description')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div>
              <label for="issue_photo" class="form-label">Upload photo if needed <span class="text-muted">(optional)</span></label>
              <input
                type="file"
                class="form-control @error('issue_photo') is-invalid @enderror"
                id="issue_photo"
                name="issue_photo"
                accept="image/*">
              @error('issue_photo')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>

        <div class="card sensei-surface-card mb-4 border-0">
          <div class="card-header bg-transparent border-0 pb-0">
            <h5 class="mb-1">Incidents</h5>
            <p class="text-muted mb-0">Let us know about any accidents, damage, or near misses this month.</p>
          </div>
          <div class="card-body p-4">
            <div class="monthly-binary border rounded-3 p-3 mb-3" data-incident-group>
              <label class="form-label mb-2">Any accident or damage this month? *</label>
              <div class="btn-group" role="group" aria-label="Incidents">
                <input
                  type="radio"
                  class="btn-check"
                  name="incident_occurred"
                  id="incident_occurred-no"
                  value="no"
                  {{ old('incident_occurred', 'no') === 'no' ? 'checked' : '' }}
                  required>
                <label class="btn btn-outline-success px-3" for="incident_occurred-no">
                  <i class="ti ti-circle-check me-1"></i>No
                </label>

                <input
                  type="radio"
                  class="btn-check"
                  name="incident_occurred"
                  id="incident_occurred-yes"
                  value="yes"
                  {{ old('incident_occurred') === 'yes' ? 'checked' : '' }}
                  required>
                <label class="btn btn-outline-danger px-3" for="incident_occurred-yes">
                  <i class="ti ti-alert-triangle me-1"></i>Yes
                </label>
              </div>
              @error('incident_occurred')
                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
              @enderror

              <div class="incident-notes mt-3 {{ old('incident_occurred') === 'yes' ? '' : 'd-none' }}" data-incident-notes>
                <label for="incident_description" class="form-label">Describe what happened *</label>
                <textarea
                  id="incident_description"
                  name="incident_description"
                  rows="3"
                  class="form-control @error('incident_description') is-invalid @enderror"
                  placeholder="Provide details of the accident, damage, or near miss.">{{ old('incident_description') }}</textarea>
                @error('incident_description')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
        </div>

        <div class="card sensei-surface-card mb-4 border-0">
          <div class="card-header bg-transparent border-0 pb-0">
            <h5 class="mb-1">Next servicing</h5>
            <p class="text-muted mb-0">Record the upcoming service so the fleet team can schedule reminders.</p>
          </div>
          <div class="card-body p-4">
            <div class="row g-4">
              <div class="col-md-6">
                <label for="next_service_date" class="form-label">Next service date *</label>
                <input
                  type="date"
                  class="form-control @error('next_service_date') is-invalid @enderror"
                  id="next_service_date"
                  name="next_service_date"
                  value="{{ old('next_service_date', optional($vehicle->next_service_due)->format('Y-m-d')) }}"
                  required>
                @error('next_service_date')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6">
                <label for="next_service_kilometre" class="form-label">Next service kilometre *</label>
                <div class="input-group">
                  <input
                    type="number"
                    class="form-control @error('next_service_kilometre') is-invalid @enderror"
                    id="next_service_kilometre"
                    name="next_service_kilometre"
                    min="0"
                    value="{{ old('next_service_kilometre', $vehicle->next_service_odometer) }}"
                    required>
                  <span class="input-group-text">km</span>
                </div>
                @error('next_service_kilometre')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
        </div>

        <div class="card sensei-surface-card mb-4 border-0">
          <div class="card-header bg-transparent border-0 pb-0">
            <h5 class="mb-1">Vehicle photos *</h5>
            <p class="text-muted mb-0">Capture the vehicle from all sides and the interior to verify its condition.</p>
          </div>
          <div class="card-body p-4">
            <div class="row g-3">
              @php
                $vehiclePhotos = [
                  'vehicle_photo_front' => 'Front view',
                  'vehicle_photo_rear' => 'Rear view',
                  'vehicle_photo_driver_side' => 'Driver side',
                  'vehicle_photo_passenger_side' => 'Passenger side',
                  'vehicle_photo_interior' => 'Interior',
                ];
              @endphp
              @foreach($vehiclePhotos as $name => $label)
                <div class="col-md-6">
                  <label for="{{ $name }}" class="form-label">{{ $label }} *</label>
                  <input
                    type="file"
                    class="form-control @error($name) is-invalid @enderror"
                    id="{{ $name }}"
                    name="{{ $name }}"
                    accept="image/*"
                    required>
                  @error($name)
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
              @endforeach
            </div>
          </div>
        </div>

        <div class="card sensei-surface-card border-0">
          <div class="card-body p-4">
            <div class="form-check mb-3">
              <input
                class="form-check-input @error('employee_confirmation') is-invalid @enderror"
                type="checkbox"
                value="1"
                id="employee_confirmation"
                name="employee_confirmation"
                {{ old('employee_confirmation') ? 'checked' : '' }}
                required>
              <label class="form-check-label" for="employee_confirmation">
                I confirm the information and photos supplied are accurate for this monthly inspection.
              </label>
              @error('employee_confirmation')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="d-flex flex-column flex-md-row gap-2 justify-content-end">
              <a href="{{ route('driver.vehicle-inspections.index') }}" class="btn btn-text-secondary">
                Cancel
              </a>
              <button type="submit" class="btn btn-primary btn-lg px-4">
                Submit and sign inspection
                <i class="ti ti-send ms-1"></i>
              </button>
            </div>
          </div>
        </div>
      </form>
    @endif
  </div>
</div>
@endsection

@push('page-style')
<style>
  .monthly-check__options,
  .monthly-binary__options {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    width: 100%;
  }

  .monthly-check__options .btn {
    flex: 1 1 calc(33.333% - 0.5rem);
    min-width: 120px;
    text-transform: none;
  }

  .monthly-binary__options .btn {
    flex: 1 1 calc(50% - 0.5rem);
    min-width: 140px;
    text-transform: none;
  }

  @media (max-width: 575.98px) {
    .monthly-check__options .btn,
    .monthly-binary__options .btn {
      flex: 1 1 100%;
    }
  }
</style>
@endpush

@push('page-script')
<!-- Image Compression -->
<script src="{{ asset('js/image-compressor.js') }}"></script>

<script>
  (function () {
    'use strict';

    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
      form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        } else {
          // Show loading state on submit button
          const submitBtn = form.querySelector('button[type="submit"]');
          if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Uploading inspection...';
          }
        }
        form.classList.add('was-validated');
      }, false);
    });

    const incidentRadios = document.querySelectorAll('input[name="incident_occurred"]');
    const incidentNotes = document.querySelector('[data-incident-notes]');

    const toggleIncidentNotes = () => {
      const selected = document.querySelector('input[name="incident_occurred"]:checked');
      if (!incidentNotes || !selected) {
        return;
      }
      if (selected.value === 'yes') {
        incidentNotes.classList.remove('d-none');
      } else {
        incidentNotes.classList.add('d-none');
      }
    };

    incidentRadios.forEach(radio => radio.addEventListener('change', toggleIncidentNotes));
    toggleIncidentNotes();
  })();
</script>
@endpush
