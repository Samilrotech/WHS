@extends('layouts.layoutMaster')

@section('title', 'Daily Vehicle Check')

@section('content')
@include('layouts.sections.flash-message')

<div class="row justify-content-center">
  <div class="col-xl-8 col-lg-9">
    @if (!$assignment)
      <div class="card sensei-surface-card border border-dashed rounded-3">
        <div class="card-body text-center py-5">
          <div class="avatar avatar-xl mb-3 bg-label-primary">
            <i class="ti ti-car fs-3"></i>
          </div>
          <h4 class="mb-2">No vehicle assigned yet</h4>
          <p class="text-muted mb-4">
            Daily inspections are available once a vehicle has been assigned to you.
            Please contact your branch admin so they can link a vehicle to your profile.
          </p>
          <a href="{{ route('driver.vehicle-inspections.index') }}" class="btn btn-outline-primary">
            <i class="ti ti-arrow-left me-1"></i>
            Return to dashboard
          </a>
        </div>
      </div>
    @else
      <div class="card border-0 shadow-sm sensei-surface-card sensei-glass sensei-sticky-card mb-4">
        <div class="card-body p-4 p-lg-5">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
            <div>
              <span class="badge bg-label-primary text-uppercase mb-2">Assigned vehicle</span>
              <h3 class="mb-1 text-capitalize">{{ $vehicle->make }} {{ $vehicle->model }} <span class="fw-normal text-muted">({{ $vehicle->year }})</span></h3>
              <div class="d-flex flex-wrap align-items-center gap-3 text-muted small">
                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2">
                  <i class="ti ti-id me-1"></i>
                  {{ $vehicle->registration_state ? $vehicle->registration_state . ' · ' : '' }}{{ $vehicle->registration_number }}
                </span>
                <span><i class="ti ti-map me-1"></i>{{ $vehicle->branch?->name ?? 'Branch updated' }}</span>
                <span><i class="ti ti-calendar me-1"></i>Assigned {{ $assignment->assigned_date->format('d M Y') }}</span>
              </div>
            </div>
            <div class="text-md-end w-100 w-md-auto">
              <p class="text-muted mb-1 small text-uppercase">Current Odometer</p>
              <h4 class="mb-3">{{ number_format($vehicle->odometer_reading ?? 0) }} km</h4>
              <div class="alert alert-info mb-0 small py-2 px-3">
                <i class="ti ti-bulb me-1"></i> Log anything unusual — the safety team reviews these checks daily.
              </div>
            </div>
          </div>
        </div>
      </div>

      <form action="{{ route('driver.vehicle-inspections.store') }}" method="POST" class="sensei-form-card needs-validation" novalidate>
        @csrf
        <input type="hidden" name="vehicle_assignment_id" value="{{ $assignment->id }}">

        <div class="card sensei-surface-card mb-4 border-0">
          <div class="card-body p-4">
            <div class="row g-4">
              <div class="col-md-6">
                <label for="odometer_reading" class="form-label">Odometer reading</label>
                <div class="input-group">
                  <input
                    type="number"
                    class="form-control @error('odometer_reading') is-invalid @enderror"
                    id="odometer_reading"
                    name="odometer_reading"
                    min="0"
                    value="{{ old('odometer_reading', $vehicle->odometer_reading ?? null) }}"
                    placeholder="e.g. 128450">
                  <span class="input-group-text">km</span>
                  @error('odometer_reading')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
                <small class="text-muted">Enter the exact reading from the dash before driving.</small>
              </div>
              <div class="col-md-6">
                <label for="location" class="form-label">Inspection location <span class="text-muted">(optional)</span></label>
                <input
                  type="text"
                  class="form-control @error('location') is-invalid @enderror"
                  id="location"
                  name="location"
                  value="{{ old('location') }}"
                  placeholder="Depot yard, field gate, etc.">
                @error('location')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                <small class="text-muted">Helps the fleet team track where the check occurred.</small>
              </div>
              <div class="col-12">
                <label for="inspector_notes" class="form-label">Overall notes <span class="text-muted">(optional)</span></label>
                <textarea
                  id="inspector_notes"
                  name="inspector_notes"
                  rows="3"
                  class="form-control @error('inspector_notes') is-invalid @enderror"
                  placeholder="Share anything the maintenance team should know.">{{ old('inspector_notes') }}</textarea>
                @error('inspector_notes')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
        </div>

        <div class="mb-3 d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-1">Rapid safety checklist</h5>
            <p class="text-muted mb-0 small">Mark each item before heading out. Choose <strong>Needs attention</strong> if something doesn’t look right.</p>
          </div>
          <span class="badge bg-label-primary text-uppercase">Approx. 2 mins</span>
        </div>

        @foreach ($checklist as $item)
          @php
            $slug = $item['slug'];
            $selected = old("checks.$slug", 'pass');
          @endphp
          <div class="card border border-dashed rounded-4 mb-3 driver-check" data-driver-check="{{ $slug }}">
            <div class="card-body p-4">
              <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                <div>
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-label-info text-uppercase">{{ $item['category'] }}</span>
                    @if (!empty($item['safety_critical']))
                      <span class="badge bg-label-danger text-uppercase">Critical</span>
                    @endif
                  </div>
                  <h6 class="mb-1">{{ $item['name'] }}</h6>
                  <p class="mb-0 text-muted small">{{ $item['description'] }}</p>
                </div>
                <div class="btn-group btn-group-sm flex-shrink-0" role="group" aria-label="Inspection outcome">
                  <input type="radio" class="btn-check" name="checks[{{ $slug }}]" id="{{ $slug }}-pass" value="pass" {{ $selected === 'pass' ? 'checked' : '' }}>
                  <label class="btn btn-outline-success px-3" for="{{ $slug }}-pass">
                    <i class="ti ti-circle-check me-1"></i>Pass
                  </label>

                  <input type="radio" class="btn-check" name="checks[{{ $slug }}]" id="{{ $slug }}-fail" value="fail" {{ $selected === 'fail' ? 'checked' : '' }}>
                  <label class="btn btn-outline-danger px-3" for="{{ $slug }}-fail">
                    <i class="ti ti-alert-circle me-1"></i>Needs attention
                  </label>

                  <input type="radio" class="btn-check" name="checks[{{ $slug }}]" id="{{ $slug }}-na" value="na" {{ $selected === 'na' ? 'checked' : '' }}>
                  <label class="btn btn-outline-secondary px-3" for="{{ $slug }}-na">
                    <i class="ti ti-circle-minus me-1"></i>N/A
                  </label>
                </div>
              </div>

              <div class="driver-note mt-3 {{ $selected === 'fail' ? '' : 'd-none' }}" data-driver-note>
                <label for="notes-{{ $slug }}" class="form-label mb-1 small text-uppercase text-muted">Tell us what you saw</label>
                <textarea
                  class="form-control @error('notes.' . $slug) is-invalid @enderror"
                  id="notes-{{ $slug }}"
                  name="notes[{{ $slug }}]"
                  rows="2"
                  placeholder="Describe the issue, warning light, or noise.">{{ old("notes.$slug") }}</textarea>
                @error('notes.' . $slug)
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              @error('checks.' . $slug)
                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
              @enderror
            </div>
          </div>
        @endforeach

        <div class="d-flex flex-column flex-md-row gap-2 justify-content-end mt-4">
          <a href="{{ route('driver.vehicle-inspections.index') }}" class="btn btn-text-secondary">
            Cancel
          </a>
          <button type="submit" class="btn btn-primary btn-lg px-4">
            Submit inspection
            <i class="ti ti-send ms-1"></i>
          </button>
        </div>
      </form>
    @endif
  </div>
</div>
@endsection

@push('page-script')
<script>
  (function () {
    'use strict';

    const groups = document.querySelectorAll('[data-driver-check]');

    const toggleNote = (group) => {
      const selected = group.querySelector('input[type="radio"]:checked');
      const note = group.querySelector('[data-driver-note]');

      if (!note) {
        return;
      }

      group.classList.remove('border-danger', 'border-success', 'shadow-sm');

      if (selected && selected.value === 'fail') {
        note.classList.remove('d-none');
        group.classList.add('border-danger', 'shadow-sm');
      } else {
        note.classList.add('d-none');
        if (selected && selected.value === 'pass') {
          group.classList.add('border-success');
        }
      }
    };

    groups.forEach((group) => {
      group.querySelectorAll('input[type="radio"]').forEach((radio) => {
        radio.addEventListener('change', () => toggleNote(group));
      });

      toggleNote(group);
    });

    // Bootstrap validation feedback
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
      form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  })();
</script>
@endpush

