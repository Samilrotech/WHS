@extends('layouts.layoutMaster')

@section('title', 'Emergency Alert Details')

@section('content')
@include('layouts.sections.flash-message')

<div class="row">
  <!-- Alert Details Card -->
  <div class="col-md-8">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center {{ $alert->status === 'triggered' ? 'bg-danger' : 'bg-secondary' }}">
        <h5 class="mb-0 text-white">Emergency Alert #{{ substr($alert->id, 0, 8) }}</h5>
        <div>
          @if($alert->status === 'triggered')
            <span class="badge bg-white text-danger">ACTIVE</span>
          @elseif($alert->status === 'responded')
            <span class="badge bg-white text-warning">RESPONDED</span>
          @elseif($alert->status === 'resolved')
            <span class="badge bg-white text-success">RESOLVED</span>
          @else
            <span class="badge bg-white text-secondary">{{ strtoupper($alert->status) }}</span>
          @endif
        </div>
      </div>
      <div class="card-body">
        <!-- Alert Information -->
        <div class="row mb-3">
          <div class="col-md-6">
            <h6 class="text-muted mb-1">Emergency Type</h6>
            <p class="mb-0">
              <span class="badge bg-label-secondary">{{ ucfirst($alert->type) }}</span>
            </p>
          </div>
          <div class="col-md-6">
            <h6 class="text-muted mb-1">Status</h6>
            <p class="mb-0">
              @if($alert->status === 'triggered')
                <span class="badge bg-danger">Active</span>
              @elseif($alert->status === 'responded')
                <span class="badge bg-warning">Responded</span>
              @elseif($alert->status === 'resolved')
                <span class="badge bg-success">Resolved</span>
              @else
                <span class="badge bg-secondary">{{ ucfirst($alert->status) }}</span>
              @endif
            </p>
          </div>
        </div>

        <hr class="my-3">

        <!-- Triggered By -->
        <div class="row mb-3">
          <div class="col-md-6">
            <h6 class="text-muted mb-1">Triggered By</h6>
            <p class="mb-0"><strong>{{ $alert->user->name }}</strong></p>
            <small class="text-muted">{{ $alert->user->email }}</small>
          </div>
          <div class="col-md-6">
            <h6 class="text-muted mb-1">Branch</h6>
            <p class="mb-0">{{ $alert->branch->name }}</p>
          </div>
        </div>

        <hr class="my-3">

        <!-- Location Information -->
        <div class="mb-3">
          <h6 class="text-muted mb-1">Location</h6>
          @if($alert->location_description)
            <p class="mb-0">{{ $alert->location_description }}</p>
          @else
            <p class="mb-0 text-muted">No location description provided</p>
          @endif
          @if($alert->latitude && $alert->longitude)
            <small class="text-muted">
              Coordinates: {{ $alert->latitude }}, {{ $alert->longitude }}
              <a href="https://www.google.com/maps?q={{ $alert->latitude }},{{ $alert->longitude }}" target="_blank" class="ms-2">
                <i class='icon-base ti ti-map-pin'></i> View on Map
              </a>
            </small>
          @endif
        </div>

        <!-- Additional Details -->
        @if($alert->description)
          <hr class="my-3">
          <div class="mb-3">
            <h6 class="text-muted mb-1">Additional Details</h6>
            <p class="mb-0">{{ $alert->description }}</p>
          </div>
        @endif

        <!-- Response Information -->
        @if($alert->responder || $alert->response_notes)
          <hr class="my-3">
          <div class="mb-3">
            <h6 class="text-muted mb-1">Response Information</h6>
            @if($alert->responder)
              <p class="mb-1"><strong>Responder:</strong> {{ $alert->responder->name }}</p>
            @endif
            @if($alert->response_notes)
              <p class="mb-0"><strong>Notes:</strong> {{ $alert->response_notes }}</p>
            @endif
          </div>
        @endif
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex gap-2 mb-4">
      @if(in_array($alert->status, ['triggered', 'responded']))
        @if($alert->status === 'triggered')
          <form action="{{ route('emergency.respond', $alert) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-warning">
              <i class='icon-base ti ti-user-check me-1'></i> Mark as Responded
            </button>
          </form>
        @endif
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#resolveModal">
          <i class='icon-base ti ti-check me-1'></i> Resolve Alert
        </button>
      @endif
      <a href="{{ route('emergency.edit', $alert) }}" class="btn btn-primary">
        <i class='icon-base ti ti-edit me-1'></i> Edit
      </a>
      <a href="{{ route('emergency.index') }}" class="btn btn-outline-secondary">
        <i class='icon-base ti ti-arrow-left me-1'></i> Back to List
      </a>
      <form action="{{ route('emergency.destroy', $alert) }}" method="POST" class="d-inline ms-auto">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this alert?')">
          <i class='icon-base ti ti-trash me-1'></i> Delete
        </button>
      </form>
    </div>
  </div>

  <!-- Timeline Card -->
  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Timeline</h5>
      </div>
      <div class="card-body">
        <ul class="timeline mb-0">
          <li class="timeline-item timeline-item-transparent">
            <span class="timeline-point timeline-point-danger"></span>
            <div class="timeline-event">
              <div class="timeline-header mb-1">
                <h6 class="mb-0">Alert Triggered</h6>
                <small class="text-muted">{{ $alert->triggered_at->format('d/m/Y H:i') }}</small>
              </div>
              <p class="mb-0 text-muted">Emergency alert created by {{ $alert->user->name }}</p>
            </div>
          </li>

          @if($alert->responded_at)
            <li class="timeline-item timeline-item-transparent">
              <span class="timeline-point timeline-point-warning"></span>
              <div class="timeline-event">
                <div class="timeline-header mb-1">
                  <h6 class="mb-0">Response Initiated</h6>
                  <small class="text-muted">{{ $alert->responded_at->format('d/m/Y H:i') }}</small>
                </div>
                <p class="mb-0 text-muted">{{ $alert->responder?->name ?? 'Unknown' }} responded to alert</p>
                @if($alert->responded_at && $alert->triggered_at)
                  <small class="text-muted">Response time: {{ $alert->triggered_at->diffForHumans($alert->responded_at, true) }}</small>
                @endif
              </div>
            </li>
          @endif

          @if($alert->resolved_at)
            <li class="timeline-item timeline-item-transparent">
              <span class="timeline-point timeline-point-success"></span>
              <div class="timeline-event">
                <div class="timeline-header mb-1">
                  <h6 class="mb-0">Alert Resolved</h6>
                  <small class="text-muted">{{ $alert->resolved_at->format('d/m/Y H:i') }}</small>
                </div>
                <p class="mb-0 text-muted">Emergency resolved</p>
                @if($alert->resolved_at && $alert->triggered_at)
                  <small class="text-muted">Total duration: {{ $alert->triggered_at->diffForHumans($alert->resolved_at, true) }}</small>
                @endif
              </div>
            </li>
          @endif

          @if($alert->status === 'triggered')
            <li class="timeline-item timeline-item-transparent">
              <span class="timeline-point timeline-point-secondary"></span>
              <div class="timeline-event">
                <div class="timeline-header mb-1">
                  <h6 class="mb-0 text-danger">Awaiting Response</h6>
                </div>
                <p class="mb-0 text-muted">Emergency alert is still active</p>
              </div>
            </li>
          @elseif($alert->status === 'responded')
            <li class="timeline-item timeline-item-transparent">
              <span class="timeline-point timeline-point-secondary"></span>
              <div class="timeline-event">
                <div class="timeline-header mb-1">
                  <h6 class="mb-0 text-warning">Awaiting Resolution</h6>
                </div>
                <p class="mb-0 text-muted">Response in progress</p>
              </div>
            </li>
          @endif
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- Resolve Modal -->
<div class="modal fade" id="resolveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('emergency.resolve', $alert) }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Resolve Emergency Alert</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="response_notes" class="form-label">Resolution Notes</label>
            <textarea id="response_notes" name="response_notes" class="form-control" rows="4" required placeholder="Describe how the emergency was resolved..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Resolve Alert</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

