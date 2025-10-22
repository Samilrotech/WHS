@extends('layouts.layoutMaster')

@section('title', 'Trigger Emergency Alert')

@section('content')
@include('layouts.sections.flash-message')

<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center bg-danger">
        <h5 class="mb-0 text-white">Trigger Emergency Alert</h5>
        <a href="{{ route('emergency.index') }}" class="btn btn-sm btn-light">
          <i class='bx bx-arrow-back me-1'></i> Back to List
        </a>
      </div>
      <div class="card-body">
        <form action="{{ route('emergency.store') }}" method="POST" class="needs-validation" novalidate>
          @csrf

          <!-- Emergency Type -->
          <div class="mb-3">
            <label for="type" class="form-label">Emergency Type *</label>
            <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
              <option value="">Select type</option>
              @foreach($types as $key => $label)
                <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
            @error('type')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @else
              <div class="invalid-feedback">Please select an emergency type.</div>
            @enderror
          </div>

          <!-- Location Description -->
          <div class="mb-3">
            <label for="location_description" class="form-label">Location Description</label>
            <input type="text" id="location_description" name="location_description" 
                   class="form-control @error('location_description') is-invalid @enderror"
                   placeholder="e.g., Warehouse A, Bay 3"
                   value="{{ old('location_description') }}" maxlength="500">
            @error('location_description')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <!-- Description -->
          <div class="mb-3">
            <label for="description" class="form-label">Additional Details</label>
            <textarea id="description" name="description" 
                      class="form-control @error('description') is-invalid @enderror"
                      rows="4" maxlength="2000">{{ old('description') }}</textarea>
            @error('description')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <!-- Form Actions -->
          <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('emergency.index') }}" class="btn btn-outline-secondary">
              <i class='bx bx-x me-1'></i> Cancel
            </a>
            <button type="submit" class="btn btn-danger">
              <i class='bx bx-alarm me-1'></i> Trigger Emergency Alert
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
window.addEventListener('load', function() {
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
