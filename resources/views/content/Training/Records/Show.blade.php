@extends('layouts/layoutMaster')

@section('title', 'Training Record Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-1">Training Record Details</h4>
    <p class="mb-0">{{ $record->trainingTypeLabel() }} - {{ $record->user->name }}</p>
  </div>
  <div>
    <a href="{{ route('training.records.edit', $record) }}" class="btn btn-primary me-2">
      <i class='bx bx-edit me-1'></i> Edit
    </a>
    <a href="{{ route('training.records.index') }}" class="btn btn-outline-secondary">
      <i class='bx bx-arrow-back me-1'></i> Back to List
    </a>
  </div>
</div>

<div class="row">
  <!-- Main Content -->
  <div class="col-md-8">
    <!-- Employee & Training Type -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <div class="d-flex align-items-center mb-3">
              <div class="avatar avatar-lg me-3">
                <span class="avatar-initial rounded-circle bg-label-primary">
                  {{ strtoupper(substr($record->user->name, 0, 2)) }}
                </span>
              </div>
              <div>
                <h5 class="mb-0">{{ $record->user->name }}</h5>
                <small class="text-muted">{{ $record->user->email }}</small>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex align-items-center mb-3">
              <div class="avatar avatar-lg me-3">
                <span class="avatar-initial rounded-circle bg-label-info">
                  <i class='bx {{ $record->trainingTypeIcon() }} bx-lg'></i>
                </span>
              </div>
              <div>
                <h5 class="mb-0">{{ $record->trainingTypeLabel() }}</h5>
                <small class="text-muted">
                  @if($record->isCritical())
                  <span class="badge badge-light-danger">Critical Certification</span>
                  @else
                  <span class="badge bg-secondary">Standard Training</span>
                  @endif
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Certification Details -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Certification Details</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Certification Number</label>
            <p class="mb-0">{{ $record->certification_number ?? 'N/A' }}</p>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Issuing Authority</label>
            <p class="mb-0">{{ $record->issuing_authority }}</p>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Issue Date</label>
            <p class="mb-0">{{ $record->issue_date->format('d/m/Y') }}</p>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Expiry Date</label>
            <p class="mb-0">
              @if($record->expiry_date)
              {{ $record->expiry_date->format('d/m/Y') }}
              <br>
              <small class="{{ $record->isExpiringSoon() ? 'text-danger' : 'text-success' }}">
                {{ $record->expiryStatusText() }}
              </small>
              @else
              <span class="text-muted">No expiry</span>
              @endif
            </p>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Training Provider</label>
            <p class="mb-0">{{ $record->training_provider ?? 'N/A' }}</p>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Duration</label>
            <p class="mb-0">{{ number_format($record->training_duration_hours, 1) }} hours</p>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Training Cost</label>
            <p class="mb-0">
              @if($record->cost)
              ${{ number_format($record->cost, 2) }}
              @else
              <span class="text-muted">Not recorded</span>
              @endif
            </p>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Proficiency Level</label>
            <p class="mb-0">
              <span class="badge bg-info">{{ ucfirst($record->proficiency_level) }}</span>
            </p>
          </div>

          @if($record->requires_renewal)
          <div class="col-md-6 mb-3">
            <label class="form-label text-muted">Renewal Required</label>
            <p class="mb-0">
              <span class="badge bg-warning">Every {{ $record->renewal_interval_months }} months</span>
            </p>
          </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Assessment Results -->
    @if($record->assessment_score !== null)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Assessment Results</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="form-label text-muted">Assessment Score</label>
            <h3 class="mb-0">{{ $record->assessment_score }}/100</h3>
            <div class="progress mt-2" style="height: 10px;">
              <div class="progress-bar {{ $record->assessment_passed ? 'bg-success' : 'bg-danger' }}"
                   role="progressbar"
                   style="width: {{ $record->assessment_score }}%"
                   aria-valuenow="{{ $record->assessment_score }}"
                   aria-valuemin="0"
                   aria-valuemax="100"></div>
            </div>
          </div>

          <div class="col-md-4 mb-3">
            <label class="form-label text-muted">Result</label>
            <p class="mb-0">
              @if($record->assessment_passed)
              <span class="badge bg-success fs-6">PASSED</span>
              @else
              <span class="badge bg-danger fs-6">FAILED</span>
              @endif
            </p>
          </div>

          <div class="col-md-4 mb-3">
            <label class="form-label text-muted">Pass Threshold</label>
            <p class="mb-0">80%</p>
          </div>

          @if($record->trainer_name)
          <div class="col-md-12 mb-3">
            <label class="form-label text-muted">Trainer/Assessor</label>
            <p class="mb-0">{{ $record->trainer_name }}</p>
          </div>
          @endif
        </div>
      </div>
    </div>
    @endif

    <!-- Additional Notes -->
    @if($record->notes)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Additional Notes</h5>
      </div>
      <div class="card-body">
        <p class="mb-0">{{ $record->notes }}</p>
      </div>
    </div>
    @endif

    <!-- Attachments -->
    @if($record->getMedia('certificates')->count() > 0)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Certificate Attachments</h5>
      </div>
      <div class="card-body">
        <div class="list-group">
          @foreach($record->getMedia('certificates') as $media)
          <div class="list-group-item d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
              <i class='bx bx-file me-2 fs-4'></i>
              <div>
                <h6 class="mb-0">{{ $media->file_name }}</h6>
                <small class="text-muted">{{ $media->human_readable_size }} - Uploaded {{ $media->created_at->format('d/m/Y') }}</small>
              </div>
            </div>
            <div>
              <a href="{{ $media->getUrl() }}" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class='bx bx-download'></i> Download
              </a>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
    @endif
  </div>

  <!-- Sidebar -->
  <div class="col-md-4">
    <!-- Status Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Current Status</h5>
      </div>
      <div class="card-body">
        @php
        $statusClass = match($record->status) {
          'active' => 'success',
          'expired' => 'danger',
          'renewed' => 'info',
          'suspended' => 'warning',
          default => 'secondary'
        };
        @endphp
        <div class="mb-3 text-center">
          <span class="badge bg-{{ $statusClass }} fs-5">{{ ucfirst($record->status) }}</span>
        </div>

        @if($record->expiry_date)
        <div class="alert {{ $record->expiryAlertClass() }} mb-0">
          <div class="d-flex align-items-start">
            <i class='bx bx-calendar me-2 fs-4'></i>
            <div>
              <strong>{{ $record->expiryStatusText() }}</strong>
              <br>
              <small>Expires: {{ $record->expiry_date->format('d/m/Y') }}</small>
            </div>
          </div>
        </div>
        @endif

        @if($record->isCritical() && $record->status === 'expired')
        <div class="alert alert-danger mt-3 mb-0">
          <div class="d-flex align-items-start">
            <i class='bx bx-error-circle me-2 fs-4'></i>
            <div>
              <strong>Critical Certification Expired</strong>
              <br>
              <small>Employee access to related equipment is suspended until renewal.</small>
            </div>
          </div>
        </div>
        @endif
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Quick Actions</h5>
      </div>
      <div class="card-body">
        <a href="{{ route('training.records.edit', $record) }}" class="btn btn-primary w-100 mb-2">
          <i class='bx bx-edit me-1'></i> Edit Record
        </a>

        @if($record->requires_renewal && $record->status === 'active')
        <button type="button" class="btn btn-warning w-100 mb-2" data-bs-toggle="modal" data-bs-target="#renewModal">
          <i class='bx bx-refresh me-1'></i> Renew Certification
        </button>
        @endif

        @if($record->status === 'active')
        <button type="button" class="btn btn-outline-warning w-100 mb-2" data-bs-toggle="modal" data-bs-target="#suspendModal">
          <i class='bx bx-pause-circle me-1'></i> Suspend
        </button>
        @endif

        @if($record->status === 'suspended')
        <form action="{{ route('training.records.reactivate', $record) }}" method="POST">
          @csrf
          <button type="submit" class="btn btn-success w-100 mb-2">
            <i class='bx bx-play-circle me-1'></i> Reactivate
          </button>
        </form>
        @endif

        <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteModal">
          <i class='bx bx-trash me-1'></i> Delete Record
        </button>
      </div>
    </div>

    <!-- Record Metadata -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Record Information</h5>
      </div>
      <div class="card-body">
        <div class="mb-2">
          <small class="text-muted">Branch</small>
          <p class="mb-0">{{ $record->branch->name }}</p>
        </div>
        <div class="mb-2">
          <small class="text-muted">Record ID</small>
          <p class="mb-0"><code>{{ $record->id }}</code></p>
        </div>
        <div class="mb-2">
          <small class="text-muted">Created</small>
          <p class="mb-0">{{ $record->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div class="mb-2">
          <small class="text-muted">Last Updated</small>
          <p class="mb-0">{{ $record->updated_at->format('d/m/Y H:i') }}</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Renew Modal -->
<div class="modal fade" id="renewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Renew Certification</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('training.records.renew', $record) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="new_expiry_date" class="form-label">New Expiry Date *</label>
            <input type="date"
                   id="new_expiry_date"
                   name="new_expiry_date"
                   class="form-control"
                   required>
          </div>

          <div class="mb-3">
            <label for="renewal_notes" class="form-label">Renewal Notes</label>
            <textarea id="renewal_notes"
                      name="renewal_notes"
                      rows="3"
                      class="form-control"
                      placeholder="Optional notes about renewal..."></textarea>
          </div>

          <div class="alert alert-info mb-0">
            <i class='bx bx-info-circle me-2'></i>
            Current certification will be marked as "renewed" and a new record will be created.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">
            <i class='bx bx-refresh me-1'></i> Renew Certification
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Suspend Modal -->
<div class="modal fade" id="suspendModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Suspend Certification</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('training.records.suspend', $record) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="suspension_reason" class="form-label">Reason for Suspension *</label>
            <textarea id="suspension_reason"
                      name="suspension_reason"
                      rows="3"
                      class="form-control"
                      placeholder="Explain why this certification is being suspended..."
                      required></textarea>
          </div>

          <div class="alert alert-warning mb-0">
            <i class='bx bx-error-circle me-2'></i>
            <strong>Warning:</strong> Suspending a critical certification (driver license, forklift) will block employee access to related equipment.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">
            <i class='bx bx-pause-circle me-1'></i> Suspend Certification
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Delete Training Record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('training.records.destroy', $record) }}" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <p>Are you sure you want to delete this training record?</p>
          <div class="alert alert-danger mb-0">
            <i class='bx bx-error-circle me-2'></i>
            <strong>Warning:</strong> This action cannot be undone. The record will be soft-deleted and can be restored by administrators if needed.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class='bx bx-trash me-1'></i> Delete Record
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
