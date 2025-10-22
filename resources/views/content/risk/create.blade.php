@extends('layouts.layoutMaster')

@section('title', 'Add Risk Assessment')

@section('page-style')
{{-- No additional styles needed --}}
@endsection

@section('content')
{{-- DO NOT CHANGE LAYOUT — WHS4 Horizontal Avatar Pattern (see FRONTEND_STANDARDS.md) --}}
@include('layouts.sections.flash-message')

<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Add New Risk Assessment</h5>
        <a href="{{ route('risk.index') }}" class="btn btn-sm btn-outline-secondary">
          <i class='bx bx-arrow-back me-1'></i> Back to List
        </a>
      </div>
      <div class="card-body">
        <form action="{{ route('risk.store') }}" method="POST" class="needs-validation" novalidate>
          @csrf

          <!-- Basic Information -->
          <h6 class="mb-3">Basic Information</h6>

          <!-- Category -->
          <div class="mb-3">
            <label for="category" class="form-label">Risk Category *</label>
            <select id="category"
                    name="category"
                    class="form-select @error('category') is-invalid @enderror"
                    required>
              <option value="">Select category</option>
              @foreach($categories as $key => $label)
                <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
            @error('category')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @else
              <div class="invalid-feedback">Please select a category.</div>
            @enderror
          </div>

          <!-- Task Description -->
          <div class="mb-3">
            <label for="task_description" class="form-label">Task/Activity Description *</label>
            <input type="text"
                   id="task_description"
                   name="task_description"
                   class="form-control @error('task_description') is-invalid @enderror"
                   placeholder="e.g., Forklift operation in confined space"
                   value="{{ old('task_description') }}"
                   maxlength="500"
                   required>
            @error('task_description')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @else
              <div class="invalid-feedback">Please enter a task description.</div>
            @enderror
          </div>

          <!-- Location and Date Row -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="location" class="form-label">Location *</label>
              <input type="text"
                     id="location"
                     name="location"
                     class="form-control @error('location') is-invalid @enderror"
                     placeholder="e.g., Warehouse A, Bay 3"
                     value="{{ old('location') }}"
                     maxlength="255"
                     required>
              @error('location')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @else
                <div class="invalid-feedback">Please enter a location.</div>
              @enderror
            </div>

            <div class="col-md-6 mb-3">
              <label for="assessment_date" class="form-label">Assessment Date *</label>
              <input type="date"
                     id="assessment_date"
                     name="assessment_date"
                     class="form-control @error('assessment_date') is-invalid @enderror"
                     value="{{ old('assessment_date', date('Y-m-d')) }}"
                     required>
              @error('assessment_date')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @else
                <div class="invalid-feedback">Please select an assessment date.</div>
              @enderror
            </div>
          </div>

          <hr class="my-4">

          <!-- Initial Risk (Before Controls) -->
          <h6 class="mb-3">Initial Risk (Before Controls)</h6>

          <div class="row">
            <!-- Initial Likelihood -->
            <div class="col-md-4 mb-3">
              <label for="initial_likelihood" class="form-label">Likelihood *</label>
              <select id="initial_likelihood"
                      name="initial_likelihood"
                      class="form-select @error('initial_likelihood') is-invalid @enderror"
                      required>
                <option value="">Select</option>
                <option value="1" {{ old('initial_likelihood') == '1' ? 'selected' : '' }}>1 - Rare</option>
                <option value="2" {{ old('initial_likelihood') == '2' ? 'selected' : '' }}>2 - Unlikely</option>
                <option value="3" {{ old('initial_likelihood') == '3' ? 'selected' : '' }}>3 - Possible</option>
                <option value="4" {{ old('initial_likelihood') == '4' ? 'selected' : '' }}>4 - Likely</option>
                <option value="5" {{ old('initial_likelihood') == '5' ? 'selected' : '' }}>5 - Almost Certain</option>
              </select>
              @error('initial_likelihood')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @else
                <div class="invalid-feedback">Please select likelihood.</div>
              @enderror
              <small class="text-muted">How likely is this risk to occur?</small>
            </div>

            <!-- Initial Consequence -->
            <div class="col-md-4 mb-3">
              <label for="initial_consequence" class="form-label">Consequence *</label>
              <select id="initial_consequence"
                      name="initial_consequence"
                      class="form-select @error('initial_consequence') is-invalid @enderror"
                      required>
                <option value="">Select</option>
                <option value="1" {{ old('initial_consequence') == '1' ? 'selected' : '' }}>1 - Insignificant</option>
                <option value="2" {{ old('initial_consequence') == '2' ? 'selected' : '' }}>2 - Minor</option>
                <option value="3" {{ old('initial_consequence') == '3' ? 'selected' : '' }}>3 - Moderate</option>
                <option value="4" {{ old('initial_consequence') == '4' ? 'selected' : '' }}>4 - Major</option>
                <option value="5" {{ old('initial_consequence') == '5' ? 'selected' : '' }}>5 - Catastrophic</option>
              </select>
              @error('initial_consequence')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @else
                <div class="invalid-feedback">Please select consequence.</div>
              @enderror
              <small class="text-muted">What's the potential impact?</small>
            </div>

            <!-- Initial Risk Score (Auto-calculated) -->
            <div class="col-md-4 mb-3">
              <label for="initial_risk_score" class="form-label">Initial Risk Score</label>
              <input type="number"
                     id="initial_risk_score"
                     class="form-control"
                     value="0"
                     readonly>
              <small class="text-muted">Automatically calculated</small>
              <div id="initial-risk-badge" class="mt-2"></div>
            </div>
          </div>

          <hr class="my-4">

          <!-- Residual Risk (After Controls) -->
          <h6 class="mb-3">Residual Risk (After Controls)</h6>

          <div class="row">
            <!-- Residual Likelihood -->
            <div class="col-md-4 mb-3">
              <label for="residual_likelihood" class="form-label">Likelihood *</label>
              <select id="residual_likelihood"
                      name="residual_likelihood"
                      class="form-select @error('residual_likelihood') is-invalid @enderror"
                      required>
                <option value="">Select</option>
                <option value="1" {{ old('residual_likelihood') == '1' ? 'selected' : '' }}>1 - Rare</option>
                <option value="2" {{ old('residual_likelihood') == '2' ? 'selected' : '' }}>2 - Unlikely</option>
                <option value="3" {{ old('residual_likelihood') == '3' ? 'selected' : '' }}>3 - Possible</option>
                <option value="4" {{ old('residual_likelihood') == '4' ? 'selected' : '' }}>4 - Likely</option>
                <option value="5" {{ old('residual_likelihood') == '5' ? 'selected' : '' }}>5 - Almost Certain</option>
              </select>
              @error('residual_likelihood')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @else
                <div class="invalid-feedback">Please select likelihood.</div>
              @enderror
              <small class="text-muted">With controls in place</small>
            </div>

            <!-- Residual Consequence -->
            <div class="col-md-4 mb-3">
              <label for="residual_consequence" class="form-label">Consequence *</label>
              <select id="residual_consequence"
                      name="residual_consequence"
                      class="form-select @error('residual_consequence') is-invalid @enderror"
                      required>
                <option value="">Select</option>
                <option value="1" {{ old('residual_consequence') == '1' ? 'selected' : '' }}>1 - Insignificant</option>
                <option value="2" {{ old('residual_consequence') == '2' ? 'selected' : '' }}>2 - Minor</option>
                <option value="3" {{ old('residual_consequence') == '3' ? 'selected' : '' }}>3 - Moderate</option>
                <option value="4" {{ old('residual_consequence') == '4' ? 'selected' : '' }}>4 - Major</option>
                <option value="5" {{ old('residual_consequence') == '5' ? 'selected' : '' }}>5 - Catastrophic</option>
              </select>
              @error('residual_consequence')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @else
                <div class="invalid-feedback">Please select consequence.</div>
              @enderror
              <small class="text-muted">With controls in place</small>
            </div>

            <!-- Residual Risk Score (Auto-calculated) -->
            <div class="col-md-4 mb-3">
              <label for="residual_risk_score" class="form-label">Residual Risk Score</label>
              <input type="number"
                     id="residual_risk_score"
                     class="form-control"
                     value="0"
                     readonly>
              <small class="text-muted">Automatically calculated</small>
              <div id="residual-risk-badge" class="mt-2"></div>
            </div>
          </div>

          <hr class="my-4">

          <!-- Optional: Review Date -->
          <div class="mb-3">
            <label for="review_date" class="form-label">Review Date (Optional)</label>
            <input type="date"
                   id="review_date"
                   name="review_date"
                   class="form-control @error('review_date') is-invalid @enderror"
                   value="{{ old('review_date') }}">
            @error('review_date')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <small class="text-muted">When should this risk assessment be reviewed?</small>
          </div>

          <!-- Form Actions -->
          <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('risk.index') }}" class="btn btn-outline-secondary">
              <i class='bx bx-x me-1'></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
              <i class='bx bx-check me-1'></i> Save Risk Assessment
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script type="module">
// Wait for window load to ensure all Vite modules (jQuery) are ready
window.addEventListener('load', function() {
  // Auto-calculate initial risk score
  function calculateInitialRiskScore() {
    const likelihood = parseInt($('#initial_likelihood').val()) || 0;
    const consequence = parseInt($('#initial_consequence').val()) || 0;
    const riskScore = likelihood * consequence;

    $('#initial_risk_score').val(riskScore);

    // Update risk level badge
    updateRiskBadge(riskScore, '#initial-risk-badge');
  }

  // Auto-calculate residual risk score
  function calculateResidualRiskScore() {
    const likelihood = parseInt($('#residual_likelihood').val()) || 0;
    const consequence = parseInt($('#residual_consequence').val()) || 0;
    const riskScore = likelihood * consequence;

    $('#residual_risk_score').val(riskScore);

    // Update risk level badge
    updateRiskBadge(riskScore, '#residual-risk-badge');
  }

  // Update risk badge with appropriate color and text
  function updateRiskBadge(riskScore, badgeSelector) {
    let badgeClass = '';
    let badgeText = '';

    if (riskScore === 0) {
      $(badgeSelector).html('');
    } else if (riskScore >= 20) {
      badgeClass = 'bg-danger';
      badgeText = 'Critical Risk';
    } else if (riskScore >= 12) {
      badgeClass = 'bg-warning';
      badgeText = 'High Risk';
    } else if (riskScore >= 6) {
      badgeClass = 'bg-warning';
      badgeText = 'Medium Risk';
    } else {
      badgeClass = 'bg-success';
      badgeText = 'Low Risk';
    }

    if (riskScore > 0) {
      $(badgeSelector).html('<span class="badge ' + badgeClass + '">' + badgeText + ' (' + riskScore + ')</span>');
    }
  }

  // Calculate on page load (for old values)
  calculateInitialRiskScore();
  calculateResidualRiskScore();

  // Recalculate when values change
  $('#initial_likelihood, #initial_consequence').on('change', function() {
    calculateInitialRiskScore();
  });

  $('#residual_likelihood, #residual_consequence').on('change', function() {
    calculateResidualRiskScore();
  });

  // Bootstrap form validation
  const form = document.querySelector('.needs-validation');
  form.addEventListener('submit', function(event) {
    if (!form.checkValidity()) {
      event.preventDefault();
      event.stopPropagation();
    }
    form.classList.add('was-validated');
  }, false);
});
</script>
@endsection
