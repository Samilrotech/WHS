@extends('layouts.layoutMaster')

@section('title', 'Incident #' . $incident->id)

@section('page-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/animate-css/animate.css') }}">
@endsection

@section('content')
@include('layouts.sections.flash-message')

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0">Incident #{{ $incident->id }}</h4>
  <div>
    <a href="{{ route('incidents.edit', $incident) }}" class="btn btn-primary me-2">
      <i class='bx bx-edit'></i> Edit
    </a>
    <a href="{{ route('incidents.index') }}" class="btn btn-outline-secondary">
      <i class='bx bx-arrow-back'></i> Back to List
    </a>
  </div>
</div>

<div class="row">
  <!-- Main Details -->
  <div class="col-md-8">
    <!-- Incident Overview -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Incident Overview</h5>
      </div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <p class="text-muted mb-1">Type</p>
            <span class="badge bg-label-secondary">{{ ucfirst($incident->type) }}</span>
          </div>
          <div class="col-md-6">
            <p class="text-muted mb-1">Severity</p>
            <span class="badge bg-{{ $incident->severity === 'critical' ? 'danger' : ($incident->severity === 'high' ? 'warning' : ($incident->severity === 'medium' ? 'warning' : 'success')) }}">
              {{ ucfirst($incident->severity) }}
            </span>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <p class="text-muted mb-1">Date & Time</p>
            <p class="mb-0">{{ $incident->incident_datetime->format('d/m/Y H:i') }}</p>
          </div>
          <div class="col-md-6">
            <p class="text-muted mb-1">Status</p>
            <span class="badge bg-{{ $incident->status === 'resolved' ? 'success' : ($incident->status === 'investigating' ? 'warning' : 'info') }}">
              {{ ucfirst($incident->status) }}
            </span>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <p class="text-muted mb-1">Branch</p>
            <p class="mb-0">{{ $incident->location_branch }}</p>
          </div>
          <div class="col-md-6">
            <p class="text-muted mb-1">Specific Location</p>
            <p class="mb-0">{{ $incident->location_specific }}</p>
          </div>
        </div>

        @if($incident->gps_latitude && $incident->gps_longitude)
        <div class="row mb-3">
          <div class="col-12">
            <p class="text-muted mb-1">GPS Coordinates</p>
            <p class="mb-0">
              {{ $incident->gps_latitude }}, {{ $incident->gps_longitude }}
              <a href="https://www.google.com/maps?q={{ $incident->gps_latitude }},{{ $incident->gps_longitude }}"
                 target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                <i class='bx bx-map'></i> View on Map
              </a>
            </p>
          </div>
        </div>
        @endif

        <div class="mb-3">
          <p class="text-muted mb-1">Description</p>
          <p class="mb-0">{{ $incident->description }}</p>
        </div>

        @if($incident->immediate_actions)
        <div class="mb-3">
          <p class="text-muted mb-1">Immediate Actions Taken</p>
          <p class="mb-0">{{ $incident->immediate_actions }}</p>
        </div>
        @endif

        @if($incident->root_cause)
        <div class="mb-3">
          <p class="text-muted mb-1">Root Cause Analysis</p>
          <p class="mb-0">{{ $incident->root_cause }}</p>
        </div>
        @endif
      </div>
    </div>

    <!-- Photos -->
    @if($incident->photos && count($incident->photos) > 0)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Photos ({{ count($incident->photos) }})</h5>
      </div>
      <div class="card-body">
        <div class="row">
          @foreach($incident->photos as $photo)
          <div class="col-md-4 mb-3">
            <div class="position-relative">
              <img src="{{ $photo->url }}" class="img-fluid rounded" alt="Incident photo"
                   style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#photoModal{{ $photo->id }}">
              @if(auth()->user()->can('delete', $incident))
              <form action="{{ route('incidents.deletePhoto', $photo) }}" method="POST" class="position-absolute top-0 end-0 m-2">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this photo?')">
                  <i class='bx bx-trash'></i>
                </button>
              </form>
              @endif
            </div>

            <!-- Photo Modal -->
            <div class="modal fade" id="photoModal{{ $photo->id }}" tabindex="-1">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Photo {{ $loop->iteration }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body text-center">
                    <img src="{{ $photo->url }}" class="img-fluid" alt="Incident photo">
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
    @endif

    <!-- Witnesses -->
    @if($incident->witnesses && count($incident->witnesses) > 0)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Witnesses ({{ count($incident->witnesses) }})</h5>
      </div>
      <div class="card-body">
        @foreach($incident->witnesses as $witness)
        <div class="d-flex mb-3">
          <div class="avatar me-3">
            <span class="avatar-initial rounded bg-label-primary">
              <i class='bx bx-user'></i>
            </span>
          </div>
          <div class="flex-grow-1">
            <h6 class="mb-0">{{ $witness->name }}</h6>
            <p class="text-muted mb-1">{{ $witness->email }} | {{ $witness->phone }}</p>
            <p class="mb-0">{{ $witness->statement }}</p>
          </div>
        </div>
        @if(!$loop->last)<hr>@endif
        @endforeach
      </div>
    </div>
    @endif
  </div>

  <!-- Sidebar -->
  <div class="col-md-4">
    <!-- Status Actions -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Actions</h5>
      </div>
      <div class="card-body">
        @if($incident->status === 'reported')
        <form action="{{ route('incidents.assign', $incident) }}" method="POST" class="mb-3">
          @csrf
          <label for="assigned_to" class="form-label">Assign for Investigation</label>
          <select name="assigned_to" id="assigned_to" class="form-select mb-2" required>
            <option value="">Select investigator</option>
            @foreach($investigators ?? [] as $investigator)
            <option value="{{ $investigator->id }}">{{ $investigator->name }}</option>
            @endforeach
          </select>
          <button type="submit" class="btn btn-primary w-100">
            <i class='bx bx-user-check'></i> Assign
          </button>
        </form>
        @endif

        @if($incident->status === 'investigating')
        <form action="{{ route('incidents.close', $incident) }}" method="POST">
          @csrf
          <label for="root_cause" class="form-label">Close with Root Cause</label>
          <textarea name="root_cause" id="root_cause" rows="3" class="form-control mb-2"
                    placeholder="Enter root cause analysis..." required></textarea>
          <button type="submit" class="btn btn-success w-100">
            <i class='bx bx-check-circle'></i> Close Incident
          </button>
        </form>
        @endif

        @if($incident->status === 'resolved')
        <div class="alert alert-success mb-0">
          <i class='bx bx-check-circle'></i> Incident resolved
        </div>
        @endif
      </div>
    </div>

    <!-- Incident Details -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Details</h5>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <p class="text-muted mb-1">Reported By</p>
          <p class="mb-0">{{ $incident->user->name }}</p>
        </div>

        @if($incident->assignedTo)
        <div class="mb-3">
          <p class="text-muted mb-1">Assigned To</p>
          <p class="mb-0">{{ $incident->assignedTo->name }}</p>
        </div>
        @endif

        <div class="mb-3">
          <p class="text-muted mb-1">Created</p>
          <p class="mb-0">{{ $incident->created_at->format('d/m/Y H:i') }}</p>
        </div>

        <div class="mb-3">
          <p class="text-muted mb-1">Last Updated</p>
          <p class="mb-0">{{ $incident->updated_at->format('d/m/Y H:i') }}</p>
        </div>

        @if($incident->requires_emergency)
        <div class="mb-3">
          <span class="badge bg-danger">
            <i class='bx bx-error-circle'></i> Emergency Response Required
          </span>
        </div>
        @endif

        @if($incident->notify_authorities)
        <div class="mb-3">
          <span class="badge bg-warning">
            <i class='bx bx-shield'></i> Authorities Notified
          </span>
        </div>
        @endif
      </div>
    </div>

    <!-- Timeline -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Timeline</h5>
      </div>
      <div class="card-body">
        <ul class="timeline">
          <li class="timeline-item timeline-item-transparent">
            <span class="timeline-point timeline-point-primary"></span>
            <div class="timeline-event">
              <div class="timeline-header mb-1">
                <h6 class="mb-0">Incident Reported</h6>
                <small class="text-muted">{{ $incident->created_at->format('d/m/Y H:i') }}</small>
              </div>
              <p class="mb-0">By {{ $incident->user->name }}</p>
            </div>
          </li>

          @if($incident->assigned_at)
          <li class="timeline-item timeline-item-transparent">
            <span class="timeline-point timeline-point-warning"></span>
            <div class="timeline-event">
              <div class="timeline-header mb-1">
                <h6 class="mb-0">Investigation Started</h6>
                <small class="text-muted">{{ $incident->assigned_at->format('d/m/Y H:i') }}</small>
              </div>
              <p class="mb-0">Assigned to {{ $incident->assignedTo->name ?? 'N/A' }}</p>
            </div>
          </li>
          @endif

          @if($incident->resolved_at)
          <li class="timeline-item timeline-item-transparent">
            <span class="timeline-point timeline-point-success"></span>
            <div class="timeline-event">
              <div class="timeline-header mb-1">
                <h6 class="mb-0">Incident Closed</h6>
                <small class="text-muted">{{ $incident->resolved_at->format('d/m/Y H:i') }}</small>
              </div>
              <p class="mb-0">Root cause identified</p>
            </div>
          </li>
          @endif
        </ul>
      </div>
    </div>
  </div>
</div>
@endsection
