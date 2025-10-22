@extends('layouts.layoutMaster')

@section('title', 'CAPA Details')

@section('vendor-style')
@vite('resources/assets/vendor/libs/bs-stepper/bs-stepper.scss')
@endsection

@section('content')
@include('layouts.sections.flash-message')

<div class="row">
  <!-- CAPA Details Column -->
  <div class="col-lg-8">
    <!-- Header Card -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h5 class="mb-1">{{ $capa->capa_number }}</h5>
            <h4 class="mb-2">{{ $capa->title }}</h4>
            <div class="d-flex gap-2 flex-wrap">
              <span class="badge bg-label-{{ $capa->type === 'corrective' ? 'warning' : 'info' }}">
                {{ ucfirst($capa->type) }}
              </span>
              <span class="badge bg-{{ $capa->priority === 'critical' ? 'danger' : ($capa->priority === 'high' ? 'warning' : 'primary') }}">
                {{ ucfirst($capa->priority) }} Priority
              </span>
              @if($capa->status === 'draft')
                <span class="badge bg-secondary">Draft</span>
              @elseif($capa->status === 'submitted')
                <span class="badge bg-info">Pending Approval</span>
              @elseif($capa->status === 'approved')
                <span class="badge bg-success">Approved</span>
              @elseif($capa->status === 'in_progress')
                <span class="badge bg-warning">In Progress</span>
              @elseif($capa->status === 'completed')
                <span class="badge bg-primary">Pending Verification</span>
              @elseif($capa->status === 'verified')
                <span class="badge bg-info">Verified</span>
              @elseif($capa->status === 'closed')
                <span class="badge bg-success">Closed</span>
              @endif
              @if($capa->isOverdue())
                <span class="badge bg-danger"><i class="bx bx-time-five"></i> Overdue</span>
              @endif
            </div>
          </div>
          <a href="{{ route('capa.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back
          </a>
        </div>

        <!-- Progress Bar -->
        <div class="mb-3">
          <div class="d-flex justify-content-between mb-1">
            <span class="text-muted small">Overall Progress</span>
            <span class="text-muted small">{{ $capa->completion_percentage }}%</span>
          </div>
          <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-{{ $capa->completion_percentage >= 100 ? 'success' : ($capa->completion_percentage >= 50 ? 'warning' : 'danger') }}"
                 role="progressbar"
                 style="width: {{ $capa->completion_percentage }}%"
                 aria-valuenow="{{ $capa->completion_percentage }}"
                 aria-valuemin="0"
                 aria-valuemax="100">
            </div>
          </div>
        </div>

        <!-- Timeline Info -->
        <div class="row g-3">
          <div class="col-sm-4">
            <small class="text-muted d-block">Target Date</small>
            <strong>{{ $capa->target_completion_date->format('d/m/Y') }}</strong>
            <small class="text-muted d-block">
              ({{ $capa->days_until_due > 0 ? $capa->days_until_due . ' days left' : abs($capa->days_until_due) . ' days overdue' }})
            </small>
          </div>
          <div class="col-sm-4">
            <small class="text-muted d-block">Raised By</small>
            <strong>{{ $capa->raisedBy->name }}</strong>
            <small class="text-muted d-block">{{ $capa->created_at->format('d/m/Y') }}</small>
          </div>
          <div class="col-sm-4">
            <small class="text-muted d-block">Assigned To</small>
            <strong>{{ $capa->assignedTo?->name ?? 'Unassigned' }}</strong>
          </div>
        </div>
      </div>
    </div>

    <!-- Description Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Description</h5>
      </div>
      <div class="card-body">
        <p class="mb-0">{{ $capa->description }}</p>
      </div>
    </div>

    <!-- Root Cause Analysis Card -->
    @if($capa->problem_statement || $capa->root_cause_analysis || $capa->five_whys || $capa->contributing_factors)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0"><i class="bx bx-search-alt-2 me-2"></i>Root Cause Analysis</h5>
      </div>
      <div class="card-body">
        @if($capa->problem_statement)
        <h6 class="text-primary mb-2">Problem Statement</h6>
        <p class="mb-3">{{ $capa->problem_statement }}</p>
        @endif

        @if($capa->root_cause_analysis)
        <h6 class="text-primary mb-2">Root Cause Analysis</h6>
        <p class="mb-3">{{ $capa->root_cause_analysis }}</p>
        @endif

        @if($capa->five_whys)
        <h6 class="text-primary mb-2">Five Whys</h6>
        <pre class="mb-3 bg-lighter p-3 rounded">{{ $capa->five_whys }}</pre>
        @endif

        @if($capa->contributing_factors)
        <h6 class="text-primary mb-2">Contributing Factors</h6>
        <p class="mb-0">{{ $capa->contributing_factors }}</p>
        @endif
      </div>
    </div>
    @endif

    <!-- Proposed Action Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0"><i class="bx bx-clipboard me-2"></i>Proposed Action</h5>
      </div>
      <div class="card-body">
        <p class="mb-3">{{ $capa->proposed_action }}</p>

        <div class="row g-3">
          @if($capa->resources_required)
          <div class="col-12">
            <h6 class="text-primary mb-2">Resources Required</h6>
            <p class="mb-0">{{ $capa->resources_required }}</p>
          </div>
          @endif

          @if($capa->estimated_cost)
          <div class="col-sm-6">
            <small class="text-muted d-block">Estimated Cost</small>
            <strong>${{ number_format($capa->estimated_cost, 2) }}</strong>
          </div>
          @endif

          @if($capa->estimated_hours)
          <div class="col-sm-6">
            <small class="text-muted d-block">Estimated Hours</small>
            <strong>{{ $capa->estimated_hours }} hours</strong>
          </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Action Items Card -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bx bx-list-check me-2"></i>Action Items</h5>
        @if($capa->status === 'in_progress')
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addActionModal">
          <i class="bx bx-plus me-1"></i> Add Action
        </button>
        @endif
      </div>
      <div class="card-body">
        @if($capa->actions->count() > 0)
        <div class="list-group list-group-flush">
          @foreach($capa->actions as $action)
          <div class="list-group-item px-0">
            <div class="d-flex justify-content-between align-items-start">
              <div class="flex-grow-1">
                <div class="d-flex align-items-center mb-2">
                  @if($action->is_completed)
                    <i class="bx bx-check-circle text-success fs-5 me-2"></i>
                  @else
                    <i class="bx bx-circle text-muted fs-5 me-2"></i>
                  @endif
                  <h6 class="mb-0 {{ $action->is_completed ? 'text-decoration-line-through' : '' }}">
                    {{ $action->title }}
                  </h6>
                </div>
                @if($action->description)
                <p class="text-muted small mb-2">{{ $action->description }}</p>
                @endif
                <div class="d-flex gap-3 small text-muted">
                  <span><i class="bx bx-user me-1"></i>{{ $action->assignedTo?->name ?? 'Unassigned' }}</span>
                  <span><i class="bx bx-calendar me-1"></i>Due: {{ $action->due_date->format('d/m/Y') }}</span>
                  @if($action->is_completed)
                  <span class="text-success"><i class="bx bx-check me-1"></i>Completed: {{ $action->completed_date->format('d/m/Y') }}</span>
                  @endif
                </div>
              </div>
              @if(!$action->is_completed && $capa->status === 'in_progress')
              <button type="button" class="btn btn-sm btn-success"
                      onclick="document.getElementById('completeActionForm{{ $action->id }}').submit()">
                <i class="bx bx-check"></i> Complete
              </button>
              <form id="completeActionForm{{ $action->id }}"
                    action="{{ route('capa.completeAction', $action) }}"
                    method="POST"
                    class="d-none">
                @csrf
              </form>
              @endif
            </div>
          </div>
          @endforeach
        </div>
        @else
        <p class="text-muted text-center mb-0">No action items yet. Add actions to track implementation progress.</p>
        @endif
      </div>
    </div>

    <!-- Notes Card -->
    @if($capa->notes)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0"><i class="bx bx-note me-2"></i>Notes</h5>
      </div>
      <div class="card-body">
        <p class="mb-0">{{ $capa->notes }}</p>
      </div>
    </div>
    @endif
  </div>

  <!-- Actions Sidebar -->
  <div class="col-lg-4">
    <!-- Workflow Actions Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0"><i class="bx bx-cog me-2"></i>Actions</h5>
      </div>
      <div class="card-body">
        @if($capa->status === 'draft')
          <form action="{{ route('capa.submit', $capa) }}" method="POST" class="mb-2">
            @csrf
            <button type="submit" class="btn btn-primary w-100">
              <i class="bx bx-send me-1"></i> Submit for Approval
            </button>
          </form>
          <a href="{{ route('capa.edit', $capa) }}" class="btn btn-outline-primary w-100 mb-2">
            <i class="bx bx-edit me-1"></i> Edit CAPA
          </a>
        @endif

        @if($capa->status === 'submitted')
          <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#approveModal">
            <i class="bx bx-check-circle me-1"></i> Approve CAPA
          </button>
          <button type="button" class="btn btn-outline-danger w-100 mb-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
            <i class="bx bx-x-circle me-1"></i> Reject CAPA
          </button>
        @endif

        @if($capa->status === 'approved')
          <form action="{{ route('capa.start', $capa) }}" method="POST" class="mb-2">
            @csrf
            <button type="submit" class="btn btn-warning w-100">
              <i class="bx bx-play me-1"></i> Start Implementation
            </button>
          </form>
        @endif

        @if($capa->status === 'in_progress')
          <form action="{{ route('capa.complete', $capa) }}" method="POST" class="mb-2">
            @csrf
            <button type="submit" class="btn btn-primary w-100">
              <i class="bx bx-check me-1"></i> Mark as Complete
            </button>
          </form>
        @endif

        @if($capa->status === 'completed')
          <button type="button" class="btn btn-info w-100 mb-2" data-bs-toggle="modal" data-bs-target="#verifyModal">
            <i class="bx bx-check-double me-1"></i> Verify Effectiveness
          </button>
        @endif

        @if($capa->status === 'verified')
          <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#closeModal">
            <i class="bx bx-check-square me-1"></i> Close CAPA
          </button>
        @endif

        <form action="{{ route('capa.destroy', $capa) }}" method="POST" class="mt-3">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-outline-danger w-100"
                  onclick="return confirm('Are you sure you want to delete this CAPA?')">
            <i class="bx bx-trash me-1"></i> Delete CAPA
          </button>
        </form>
      </div>
    </div>

    <!-- Status Workflow Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0"><i class="bx bx-git-branch me-2"></i>Workflow Status</h5>
      </div>
      <div class="card-body">
        <div class="bs-stepper vertical">
          <div class="bs-stepper-content">
            <!-- Draft -->
            <div class="step-item {{ $capa->status === 'draft' ? 'active' : 'completed' }}">
              <div class="d-flex align-items-center mb-3">
                <div class="step-icon {{ $capa->status === 'draft' ? 'bg-primary' : 'bg-success' }}">
                  <i class="bx bx-edit"></i>
                </div>
                <div class="ms-3">
                  <h6 class="mb-0">Draft</h6>
                  <small class="text-muted">Initial creation</small>
                </div>
              </div>
            </div>

            <!-- Submitted -->
            <div class="step-item {{ $capa->status === 'submitted' ? 'active' : (in_array($capa->status, ['approved', 'in_progress', 'completed', 'verified', 'closed']) ? 'completed' : '') }}">
              <div class="d-flex align-items-center mb-3">
                <div class="step-icon {{ $capa->status === 'submitted' ? 'bg-primary' : (in_array($capa->status, ['approved', 'in_progress', 'completed', 'verified', 'closed']) ? 'bg-success' : 'bg-secondary') }}">
                  <i class="bx bx-send"></i>
                </div>
                <div class="ms-3">
                  <h6 class="mb-0">Pending Approval</h6>
                  <small class="text-muted">Awaiting review</small>
                </div>
              </div>
            </div>

            <!-- Approved -->
            <div class="step-item {{ $capa->status === 'approved' ? 'active' : (in_array($capa->status, ['in_progress', 'completed', 'verified', 'closed']) ? 'completed' : '') }}">
              <div class="d-flex align-items-center mb-3">
                <div class="step-icon {{ $capa->status === 'approved' ? 'bg-primary' : (in_array($capa->status, ['in_progress', 'completed', 'verified', 'closed']) ? 'bg-success' : 'bg-secondary') }}">
                  <i class="bx bx-check-circle"></i>
                </div>
                <div class="ms-3">
                  <h6 class="mb-0">Approved</h6>
                  <small class="text-muted">Ready to implement</small>
                  @if($capa->approvedBy)
                  <small class="text-muted d-block">By: {{ $capa->approvedBy->name }}</small>
                  @endif
                </div>
              </div>
            </div>

            <!-- In Progress -->
            <div class="step-item {{ $capa->status === 'in_progress' ? 'active' : (in_array($capa->status, ['completed', 'verified', 'closed']) ? 'completed' : '') }}">
              <div class="d-flex align-items-center mb-3">
                <div class="step-icon {{ $capa->status === 'in_progress' ? 'bg-warning' : (in_array($capa->status, ['completed', 'verified', 'closed']) ? 'bg-success' : 'bg-secondary') }}">
                  <i class="bx bx-loader-alt"></i>
                </div>
                <div class="ms-3">
                  <h6 class="mb-0">In Progress</h6>
                  <small class="text-muted">Implementation ongoing</small>
                </div>
              </div>
            </div>

            <!-- Completed -->
            <div class="step-item {{ $capa->status === 'completed' ? 'active' : (in_array($capa->status, ['verified', 'closed']) ? 'completed' : '') }}">
              <div class="d-flex align-items-center mb-3">
                <div class="step-icon {{ $capa->status === 'completed' ? 'bg-primary' : (in_array($capa->status, ['verified', 'closed']) ? 'bg-success' : 'bg-secondary') }}">
                  <i class="bx bx-clipboard-check"></i>
                </div>
                <div class="ms-3">
                  <h6 class="mb-0">Completed</h6>
                  <small class="text-muted">Awaiting verification</small>
                </div>
              </div>
            </div>

            <!-- Verified -->
            <div class="step-item {{ $capa->status === 'verified' ? 'active' : ($capa->status === 'closed' ? 'completed' : '') }}">
              <div class="d-flex align-items-center mb-3">
                <div class="step-icon {{ $capa->status === 'verified' ? 'bg-info' : ($capa->status === 'closed' ? 'bg-success' : 'bg-secondary') }}">
                  <i class="bx bx-check-double"></i>
                </div>
                <div class="ms-3">
                  <h6 class="mb-0">Verified</h6>
                  <small class="text-muted">Effectiveness confirmed</small>
                  @if($capa->verifiedBy)
                  <small class="text-muted d-block">By: {{ $capa->verifiedBy->name }}</small>
                  @endif
                </div>
              </div>
            </div>

            <!-- Closed -->
            <div class="step-item {{ $capa->status === 'closed' ? 'completed' : '' }}">
              <div class="d-flex align-items-center">
                <div class="step-icon {{ $capa->status === 'closed' ? 'bg-success' : 'bg-secondary' }}">
                  <i class="bx bx-check-square"></i>
                </div>
                <div class="ms-3">
                  <h6 class="mb-0">Closed</h6>
                  <small class="text-muted">CAPA complete</small>
                  @if($capa->closedBy)
                  <small class="text-muted d-block">By: {{ $capa->closedBy->name }}</small>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Linked Incident Card -->
    @if($capa->incident)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0"><i class="bx bx-link me-2"></i>Linked Incident</h5>
      </div>
      <div class="card-body">
        <h6>{{ $capa->incident->title }}</h6>
        <p class="text-muted small mb-2">{{ $capa->incident->incident_datetime->format('d/m/Y H:i') }}</p>
        <a href="{{ route('incidents.show', $capa->incident) }}" class="btn btn-sm btn-outline-primary">
          <i class="bx bx-show me-1"></i> View Incident
        </a>
      </div>
    </div>
    @endif
  </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('capa.approve', $capa) }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Approve CAPA</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="approval_notes" class="form-label">Approval Notes (Optional)</label>
            <textarea id="approval_notes" name="approval_notes" class="form-control" rows="4"
                      placeholder="Add any comments or conditions for approval"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class="bx bx-check-circle me-1"></i> Approve CAPA
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('capa.reject', $capa) }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Reject CAPA</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="rejection_reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
            <textarea id="rejection_reason" name="rejection_reason" class="form-control" rows="4"
                      placeholder="Explain why this CAPA is being rejected" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="bx bx-x-circle me-1"></i> Reject CAPA
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Verify Modal -->
<div class="modal fade" id="verifyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('capa.verify', $capa) }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Verify CAPA Effectiveness</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="verification_method" class="form-label">Verification Method</label>
            <textarea id="verification_method" name="verification_method" class="form-control" rows="2"
                      placeholder="How was effectiveness verified?"></textarea>
          </div>
          <div class="mb-3">
            <label for="verification_results" class="form-label">Verification Results <span class="text-danger">*</span></label>
            <textarea id="verification_results" name="verification_results" class="form-control" rows="4"
                      placeholder="Document the results of verification" required></textarea>
          </div>
          <div class="mb-3">
            <label for="effectiveness_confirmed" class="form-label">Effectiveness Confirmed? <span class="text-danger">*</span></label>
            <select id="effectiveness_confirmed" name="effectiveness_confirmed" class="form-select" required>
              <option value="">Select...</option>
              <option value="1">Yes - Action was effective</option>
              <option value="0">No - Action was not effective</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-info">
            <i class="bx bx-check-double me-1"></i> Verify
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Close Modal -->
<div class="modal fade" id="closeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('capa.close', $capa) }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Close CAPA</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="closure_notes" class="form-label">Closure Notes (Optional)</label>
            <textarea id="closure_notes" name="closure_notes" class="form-control" rows="4"
                      placeholder="Final comments before closing"></textarea>
          </div>
          <div class="alert alert-info mb-0">
            <i class="bx bx-info-circle me-2"></i>
            This CAPA will be marked as closed and archived.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class="bx bx-check-square me-1"></i> Close CAPA
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Add Action Modal -->
<div class="modal fade" id="addActionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('capa.createAction', $capa) }}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Action Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="action_title" class="form-label">Action Title <span class="text-danger">*</span></label>
            <input type="text" id="action_title" name="title" class="form-control"
                   placeholder="What needs to be done?" required>
          </div>
          <div class="mb-3">
            <label for="action_description" class="form-label">Description</label>
            <textarea id="action_description" name="description" class="form-control" rows="3"
                      placeholder="Additional details about this action"></textarea>
          </div>
          <div class="mb-3">
            <label for="action_assigned_to" class="form-label">Assign To</label>
            <select id="action_assigned_to" name="assigned_to_user_id" class="form-select">
              <option value="">Unassigned</option>
              @foreach(\App\Models\User::where('branch_id', auth()->user()->branch_id)->orderBy('name')->get() as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="action_due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
            <input type="date" id="action_due_date" name="due_date" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Add Action
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/bs-stepper/bs-stepper.js')
@endsection

@section('page-script')
<style>
.step-item {
  position: relative;
  padding-left: 0;
}

.step-item:not(:last-child)::after {
  content: '';
  position: absolute;
  left: 18px;
  top: 40px;
  height: calc(100% - 20px);
  width: 2px;
  background-color: #ddd;
}

.step-item.completed:not(:last-child)::after {
  background-color: #28c76f;
}

.step-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 18px;
}

.bg-lighter {
  background-color: rgba(var(--bs-body-bg-rgb), 0.05);
}
</style>
@endsection
