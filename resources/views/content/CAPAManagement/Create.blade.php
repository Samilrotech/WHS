@extends('layouts.layoutMaster')

@section('title', 'Create CAPA')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('content')
@include('layouts.sections.flash-message')

<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Create New CAPA</h5>
        <a href="{{ route('capa.index') }}" class="btn btn-sm btn-outline-secondary">
          <i class="bx bx-arrow-back me-1"></i> Back to List
        </a>
      </div>

      <div class="card-body">
        <form action="{{ route('capa.store') }}" method="POST" id="capaForm">
          @csrf

          <!-- Basic Information Section -->
          <div class="row">
            <div class="col-12">
              <h6 class="mb-3 text-primary"><i class="bx bx-info-circle me-2"></i>Basic Information</h6>
            </div>

            <div class="col-md-6 mb-3">
              <label for="type" class="form-label">CAPA Type <span class="text-danger">*</span></label>
              <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                <option value="">Select CAPA type</option>
                <option value="corrective" {{ old('type') === 'corrective' ? 'selected' : '' }}>Corrective Action</option>
                <option value="preventive" {{ old('type') === 'preventive' ? 'selected' : '' }}>Preventive Action</option>
              </select>
              @error('type')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="form-text text-muted">Corrective: Fix existing issue | Preventive: Prevent future issues</small>
            </div>

            <div class="col-md-6 mb-3">
              <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
              <select id="priority" name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                <option value="">Select priority</option>
                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                <option value="critical" {{ old('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
              </select>
              @error('priority')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 mb-3">
              <label for="title" class="form-label">CAPA Title <span class="text-danger">*</span></label>
              <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror"
                     value="{{ old('title') }}"
                     placeholder="Brief description of the action required"
                     required>
              @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 mb-3">
              <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
              <textarea id="description" name="description" rows="4"
                        class="form-control @error('description') is-invalid @enderror"
                        placeholder="Detailed description of the CAPA"
                        required>{{ old('description') }}</textarea>
              @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="assigned_to_user_id" class="form-label">Assign To</label>
              <select id="assigned_to_user_id" name="assigned_to_user_id" class="form-select select2 @error('assigned_to_user_id') is-invalid @enderror">
                <option value="">Select assignee (optional)</option>
                @foreach(\App\Models\User::where('branch_id', auth()->user()->branch_id)->orderBy('name')->get() as $user)
                  <option value="{{ $user->id }}" {{ old('assigned_to_user_id') == $user->id ? 'selected' : '' }}>
                    {{ $user->name }}
                  </option>
                @endforeach
              </select>
              @error('assigned_to_user_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="incident_id" class="form-label">Linked Incident</label>
              <select id="incident_id" name="incident_id" class="form-select select2 @error('incident_id') is-invalid @enderror">
                <option value="">No linked incident (optional)</option>
                @foreach(\App\Modules\IncidentManagement\Models\Incident::where('branch_id', auth()->user()->branch_id)->latest()->take(50)->get() as $incident)
                  <option value="{{ $incident->id }}" {{ old('incident_id') == $incident->id ? 'selected' : '' }}>
                    #{{ $incident->id }} - {{ str()->limit($incident->title, 40) }}
                  </option>
                @endforeach
              </select>
              @error('incident_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <hr class="my-4">

          <!-- Root Cause Analysis Section -->
          <div class="row">
            <div class="col-12">
              <h6 class="mb-3 text-primary"><i class="bx bx-search-alt-2 me-2"></i>Root Cause Analysis</h6>
            </div>

            <div class="col-12 mb-3">
              <label for="problem_statement" class="form-label">Problem Statement</label>
              <textarea id="problem_statement" name="problem_statement" rows="3"
                        class="form-control @error('problem_statement') is-invalid @enderror"
                        placeholder="Clear statement of the problem to be addressed">{{ old('problem_statement') }}</textarea>
              @error('problem_statement')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 mb-3">
              <label for="root_cause_analysis" class="form-label">Root Cause Analysis</label>
              <textarea id="root_cause_analysis" name="root_cause_analysis" rows="4"
                        class="form-control @error('root_cause_analysis') is-invalid @enderror"
                        placeholder="Detailed analysis of the root cause(s)">{{ old('root_cause_analysis') }}</textarea>
              @error('root_cause_analysis')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 mb-3">
              <label for="five_whys" class="form-label">Five Whys Analysis</label>
              <textarea id="five_whys" name="five_whys" rows="5"
                        class="form-control @error('five_whys') is-invalid @enderror"
                        placeholder="Why 1: [Answer]&#10;Why 2: [Answer]&#10;Why 3: [Answer]&#10;Why 4: [Answer]&#10;Why 5: [Root Cause]">{{ old('five_whys') }}</textarea>
              @error('five_whys')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="form-text text-muted">Ask "Why?" five times to drill down to the root cause</small>
            </div>

            <div class="col-12 mb-3">
              <label for="contributing_factors" class="form-label">Contributing Factors</label>
              <textarea id="contributing_factors" name="contributing_factors" rows="3"
                        class="form-control @error('contributing_factors') is-invalid @enderror"
                        placeholder="Additional factors that contributed to the problem">{{ old('contributing_factors') }}</textarea>
              @error('contributing_factors')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <hr class="my-4">

          <!-- Proposed Action Section -->
          <div class="row">
            <div class="col-12">
              <h6 class="mb-3 text-primary"><i class="bx bx-clipboard me-2"></i>Proposed Action</h6>
            </div>

            <div class="col-12 mb-3">
              <label for="proposed_action" class="form-label">Proposed Action <span class="text-danger">*</span></label>
              <textarea id="proposed_action" name="proposed_action" rows="4"
                        class="form-control @error('proposed_action') is-invalid @enderror"
                        placeholder="Describe the action(s) to be taken"
                        required>{{ old('proposed_action') }}</textarea>
              @error('proposed_action')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 mb-3">
              <label for="resources_required" class="form-label">Resources Required</label>
              <textarea id="resources_required" name="resources_required" rows="3"
                        class="form-control @error('resources_required') is-invalid @enderror"
                        placeholder="Personnel, equipment, budget, etc.">{{ old('resources_required') }}</textarea>
              @error('resources_required')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-4 mb-3">
              <label for="estimated_cost" class="form-label">Estimated Cost ($)</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" id="estimated_cost" name="estimated_cost"
                       class="form-control @error('estimated_cost') is-invalid @enderror"
                       value="{{ old('estimated_cost') }}"
                       min="0"
                       step="0.01"
                       placeholder="0.00">
                @error('estimated_cost')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="col-md-4 mb-3">
              <label for="estimated_hours" class="form-label">Estimated Hours</label>
              <input type="number" id="estimated_hours" name="estimated_hours"
                     class="form-control @error('estimated_hours') is-invalid @enderror"
                     value="{{ old('estimated_hours') }}"
                     min="0"
                     placeholder="Hours to complete">
              @error('estimated_hours')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-4 mb-3">
              <label for="target_completion_date" class="form-label">Target Completion Date <span class="text-danger">*</span></label>
              <input type="text" id="target_completion_date" name="target_completion_date"
                     class="form-control flatpickr-input @error('target_completion_date') is-invalid @enderror"
                     value="{{ old('target_completion_date') }}"
                     placeholder="Select date"
                     required>
              @error('target_completion_date')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <hr class="my-4">

          <!-- Additional Notes Section -->
          <div class="row">
            <div class="col-12">
              <h6 class="mb-3 text-primary"><i class="bx bx-note me-2"></i>Additional Information</h6>
            </div>

            <div class="col-12 mb-3">
              <label for="notes" class="form-label">Notes</label>
              <textarea id="notes" name="notes" rows="3"
                        class="form-control @error('notes') is-invalid @enderror"
                        placeholder="Any additional notes or comments">{{ old('notes') }}</textarea>
              @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="row mt-4">
            <div class="col-12">
              <button type="submit" class="btn btn-primary me-2">
                <i class="bx bx-save me-1"></i> Create CAPA
              </button>
              <a href="{{ route('capa.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-x me-1"></i> Cancel
              </a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
<script type="module">
window.addEventListener('load', function() {
  // Initialize Flatpickr for date picker
  const flatpickrDate = document.querySelector('#target_completion_date');
  if (flatpickrDate) {
    flatpickrDate.flatpickr({
      dateFormat: 'Y-m-d',
      minDate: 'today',
      altInput: true,
      altFormat: 'd/m/Y'
    });
  }

  // Initialize Select2 for user and incident dropdowns
  if (typeof $.fn.select2 !== 'undefined') {
    $('.select2').select2({
      theme: 'bootstrap-5',
      width: '100%'
    });
  }

  // Form validation
  const form = document.getElementById('capaForm');
  if (form) {
    form.addEventListener('submit', function(e) {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      }
      form.classList.add('was-validated');
    });
  }
});
</script>
@endsection
