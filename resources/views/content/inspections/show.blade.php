@extends('layouts.layoutMaster')

@section('title', 'Inspection Details')

@section('content')
@include('layouts.sections.flash-message')

<div class="row">
  <div class="col-12 col-lg-8">
    <!-- Inspection Header -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-1">{{ $inspection->inspection_number }}</h5>
          <p class="mb-0 text-muted">{{ $inspection->vehicle->registration_number }} - {{ $inspection->vehicle->make }} {{ $inspection->vehicle->model }}</p>
        </div>
        <a href="{{ route('inspections.index') }}" class="btn btn-sm btn-outline-secondary">
          <i class="bx bx-arrow-back me-1"></i> Back
        </a>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-4">
            <small class="text-muted">Status</small>
            <p class="fw-semibold">
              @switch($inspection->status)
                @case('pending') <span class="badge bg-secondary">Pending</span> @break
                @case('in_progress') <span class="badge bg-info">In Progress</span> @break
                @case('completed') <span class="badge bg-warning">Completed</span> @break
                @case('approved') <span class="badge bg-success">Approved</span> @break
                @case('rejected') <span class="badge bg-danger">Rejected</span> @break
              @endswitch
            </p>
          </div>
          <div class="col-md-4">
            <small class="text-muted">Overall Result</small>
            <p class="fw-semibold">
              @if($inspection->overall_result)
                @switch($inspection->overall_result)
                  @case('pass') <span class="badge bg-success">Pass</span> @break
                  @case('pass_minor') <span class="badge bg-warning">Pass (Minor)</span> @break
                  @case('fail_major') <span class="badge bg-danger">Fail (Major)</span> @break
                  @case('fail_critical') <span class="badge bg-danger">Fail (Critical)</span> @break
                @endswitch
              @else
                <span class="text-muted">Not completed</span>
              @endif
            </p>
          </div>
          <div class="col-md-4">
            <small class="text-muted">Completion</small>
            <div class="d-flex align-items-center">
              <div class="progress flex-grow-1 me-2" style="height: 8px;">
                <div class="progress-bar" role="progressbar" style="width: {{ $statistics['completion_percentage'] }}%"></div>
              </div>
              <small>{{ $statistics['completion_percentage'] }}%</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Inspection Checklist -->
    @foreach($itemsByCategory as $category => $items)
    <div class="card mb-3">
      <div class="card-header">
        <h6 class="mb-0">{{ $category }}</h6>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm">
            <thead>
              <tr>
                <th style="width: 40%">Item</th>
                <th style="width: 15%">Result</th>
                <th style="width: 20%">Severity</th>
                <th style="width: 25%">Notes</th>
              </tr>
            </thead>
            <tbody>
              @foreach($items as $item)
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    {{ $item->item_name }}
                    @if($item->safety_critical)
                      <span class="badge bg-label-danger ms-2" title="Safety Critical">!</span>
                    @endif
                    @if($item->compliance_item)
                      <span class="badge bg-label-warning ms-1" title="Compliance">C</span>
                    @endif
                  </div>
                  @if($item->item_description)
                    <small class="text-muted">{{ $item->item_description }}</small>
                  @endif
                </td>
                <td>
                  @switch($item->result)
                    @case('pass') <span class="badge bg-success">Pass</span> @break
                    @case('fail') <span class="badge bg-danger">Fail</span> @break
                    @case('na') <span class="badge bg-secondary">N/A</span> @break
                    @default <span class="badge bg-secondary">Pending</span>
                  @endswitch
                </td>
                <td>
                  @if($item->defect_severity && $item->defect_severity !== 'none')
                    @switch($item->defect_severity)
                      @case('critical') <span class="badge bg-danger">Critical</span> @break
                      @case('major') <span class="badge bg-warning">Major</span> @break
                      @case('minor') <span class="badge bg-info">Minor</span> @break
                    @endswitch
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  @if($item->defect_notes)
                    <small>{{ Str::limit($item->defect_notes, 50) }}</small>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    @endforeach

    <!-- Complete Inspection Form -->
    @if($inspection->status === 'in_progress')
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Complete Inspection</h6>
      </div>
      <div class="card-body">
        <form action="{{ route('inspections.complete', $inspection) }}" method="POST">
          @csrf
          <div class="mb-3">
            <label class="form-label">Inspector Notes</label>
            <textarea name="inspector_notes" class="form-control" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Defects Summary</label>
            <textarea name="defects_summary" class="form-control" rows="2"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Recommendations</label>
            <textarea name="recommendations" class="form-control" rows="2"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Next Inspection Due</label>
            <input type="date" name="next_inspection_due" class="form-control" value="{{ now()->addMonth()->format('Y-m-d') }}">
          </div>
          <button type="submit" class="btn btn-primary">Complete Inspection</button>
        </form>
      </div>
    </div>
    @endif
  </div>

  <!-- Sidebar -->
  <div class="col-12 col-lg-4">
    <!-- Statistics Card -->
    <div class="card mb-4">
      <div class="card-body">
        <h6 class="card-title">Statistics</h6>
        <ul class="list-unstyled mb-0">
          <li class="d-flex justify-content-between mb-2">
            <span>Total Items:</span>
            <strong>{{ $statistics['total_items'] }}</strong>
          </li>
          <li class="d-flex justify-content-between mb-2">
            <span>Items Checked:</span>
            <strong>{{ $statistics['items_checked'] }}</strong>
          </li>
          <li class="d-flex justify-content-between mb-2">
            <span>Passed:</span>
            <span class="badge bg-success">{{ $statistics['items_passed'] }}</span>
          </li>
          <li class="d-flex justify-content-between mb-2">
            <span>Failed:</span>
            <span class="badge bg-danger">{{ $statistics['items_failed'] }}</span>
          </li>
          <li class="d-flex justify-content-between mb-2">
            <span>Critical Defects:</span>
            <span class="badge bg-danger">{{ $statistics['critical_defects'] }}</span>
          </li>
          <li class="d-flex justify-content-between mb-2">
            <span>Major Defects:</span>
            <span class="badge bg-warning">{{ $statistics['major_defects'] }}</span>
          </li>
          <li class="d-flex justify-content-between">
            <span>Minor Defects:</span>
            <span class="badge bg-info">{{ $statistics['minor_defects'] }}</span>
          </li>
        </ul>
      </div>
    </div>

    <!-- Actions Card -->
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Actions</h6>
        <div class="d-grid gap-2">
          @if($inspection->status === 'pending')
            <form action="{{ route('inspections.start', $inspection) }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-primary w-100">Start Inspection</button>
            </form>
          @endif

          @if($inspection->status === 'completed')
            <form action="{{ route('inspections.approve', $inspection) }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-success w-100">Approve</button>
            </form>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
          @endif

          @if(in_array($inspection->status, ['pending', 'in_progress']))
            <a href="{{ route('inspections.edit', $inspection) }}" class="btn btn-outline-primary">Edit Details</a>
          @endif

          <a href="{{ route('vehicles.show', $inspection->vehicle) }}" class="btn btn-outline-secondary">View Vehicle</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog">
    <form action="{{ route('inspections.reject', $inspection) }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Reject Inspection</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Rejection Reason *</label>
            <textarea name="rejection_reason" class="form-control" rows="4" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Reject Inspection</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection
