@extends('layouts/layoutMaster')

@section('title', $contractor->first_name . ' ' . $contractor->last_name . ' - Contractor Details')

@section('content')
<div class="row">
  <!-- Main Content -->
  <div class="col-12 col-lg-8">
    <!-- Contractor Header Card -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h4 class="mb-1">{{ $contractor->first_name }} {{ $contractor->last_name }}</h4>
            <p class="mb-0 text-muted">
              {{ $contractor->company->name ?? 'No Company' }} " ID: {{ $contractor->id }}
            </p>
          </div>
          <div>
            @if($contractor->status === 'active')
              <span class="badge bg-success">Active</span>
            @elseif($contractor->status === 'suspended')
              <span class="badge bg-danger">Suspended</span>
            @else
              <span class="badge bg-secondary">Inactive</span>
            @endif
          </div>
        </div>

        <div class="row g-3">
          <div class="col-md-6">
            <small class="text-muted d-block">Email</small>
            <strong>{{ $contractor->email }}</strong>
          </div>
          <div class="col-md-6">
            <small class="text-muted d-block">Phone</small>
            <strong>{{ $contractor->phone }}</strong>
          </div>
          @if($contractor->date_of_birth)
          <div class="col-md-6">
            <small class="text-muted d-block">Date of Birth</small>
            <strong>{{ $contractor->date_of_birth->format('d/m/Y') }}</strong>
            <small class="text-muted">({{ $contractor->date_of_birth->age }} years old)</small>
          </div>
          @endif
          @if($contractor->emergency_contact_name)
          <div class="col-md-6">
            <small class="text-muted d-block">Emergency Contact</small>
            <strong>{{ $contractor->emergency_contact_name }}</strong>
            @if($contractor->emergency_contact_phone)
              <br><small class="text-muted">{{ $contractor->emergency_contact_phone }}</small>
            @endif
          </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Induction Status Card -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Induction Status</h5>
        @if(!$contractor->hasValidInduction())
          <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#inductionModal">
            <i class="bx bx-check-circle me-1"></i> Complete Induction
          </button>
        @endif
      </div>
      <div class="card-body">
        @if($contractor->hasValidInduction())
          <div class="alert alert-success">
            <div class="d-flex align-items-center">
              <i class="bx bx-check-circle me-2 fs-4"></i>
              <div>
                <h6 class="alert-heading mb-1">Valid Induction</h6>
                <p class="mb-0">
                  Completed on {{ $contractor->induction_completion_date->format('d/m/Y') }}
                  by {{ $contractor->inductor->name ?? 'Unknown' }}
                </p>
                <p class="mb-0">
                  <strong>Expires:</strong> {{ $contractor->induction_expiry_date->format('d/m/Y') }}
                  <span class="text-muted">({{ $contractor->induction_expiry_date->diffForHumans() }})</span>
                </p>
                @if($contractor->isInductionExpiringSoon())
                  <p class="mb-0 text-warning mt-2">
                    <i class="bx bx-error-circle me-1"></i>
                    <strong>Warning:</strong> Induction expiring soon!
                  </p>
                @endif
              </div>
            </div>
          </div>
        @elseif($contractor->induction_completed)
          <div class="alert alert-danger">
            <div class="d-flex align-items-center">
              <i class="bx bx-x-circle me-2 fs-4"></i>
              <div>
                <h6 class="alert-heading mb-1">Expired Induction</h6>
                <p class="mb-0">
                  Expired on {{ $contractor->induction_expiry_date->format('d/m/Y') }}
                  ({{ $contractor->induction_expiry_date->diffForHumans() }})
                </p>
                <p class="mb-0 mt-2">
                  <strong>Action Required:</strong> Re-induction needed before site access can be granted.
                </p>
              </div>
            </div>
          </div>
        @else
          <div class="alert alert-warning">
            <div class="d-flex align-items-center">
              <i class="bx bx-time me-2 fs-4"></i>
              <div>
                <h6 class="alert-heading mb-1">Pending Induction</h6>
                <p class="mb-0">Contractor has not completed site induction yet.</p>
              </div>
            </div>
          </div>
        @endif

        @if($contractor->inductions->isNotEmpty())
          <h6 class="mt-4 mb-3">Induction Modules Completed</h6>
          <div class="list-group">
            @foreach($contractor->inductions as $induction)
            <div class="list-group-item">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <strong>{{ $induction->inductionModule->name ?? 'Unknown Module' }}</strong>
                  <br><small class="text-muted">
                    Completed {{ $induction->completed_at->format('d/m/Y H:i') }}
                    by {{ $induction->inductedBy->name ?? 'Unknown' }}
                  </small>
                </div>
                <span class="badge bg-success">
                  <i class="bx bx-check"></i> Completed
                </span>
              </div>
            </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>

    <!-- Site Access Card -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Site Access</h5>
        <div>
          @if($contractor->hasValidInduction() && !$contractor->site_access_granted)
            <form action="{{ route('contractors.grant-site-access', $contractor) }}" method="POST" class="d-inline">
              @csrf
              <button type="submit" class="btn btn-sm btn-success">
                <i class="bx bx-key me-1"></i> Grant Access
              </button>
            </form>
          @endif
          @if($contractor->site_access_granted)
            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#revokeAccessModal">
              <i class="bx bx-lock me-1"></i> Revoke Access
            </button>
          @endif
        </div>
      </div>
      <div class="card-body">
        @if($contractor->site_access_granted)
          <div class="alert alert-success">
            <i class="bx bx-check-circle me-2"></i>
            <strong>Site Access Granted</strong> - Contractor is authorized to access the site
          </div>
        @else
          <div class="alert alert-secondary">
            <i class="bx bx-x-circle me-2"></i>
            <strong>No Site Access</strong>
            @if(!$contractor->hasValidInduction())
              - Valid induction required before granting access
            @endif
          </div>
        @endif

        @if($contractor->isSignedIn())
          <div class="alert alert-warning mb-0">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <i class="bx bx-map-pin me-2"></i>
                <strong>Currently On-Site</strong> - Contractor is signed in
              </div>
              <form action="{{ route('contractors.sign-out', $contractor) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-dark">
                  <i class="bx bx-log-out me-1"></i> Sign Out
                </button>
              </form>
            </div>
          </div>
        @elseif($contractor->site_access_granted)
          <div class="text-center mt-3">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#signInModal">
              <i class="bx bx-log-in me-1"></i> Sign In Contractor
            </button>
          </div>
        @endif
      </div>
    </div>

    <!-- Driver License Card -->
    @if($contractor->driver_license_number)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Driver License Information</h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <small class="text-muted d-block">License Number</small>
            <strong>{{ $contractor->driver_license_number }}</strong>
          </div>
          @if($contractor->driver_license_expiry)
          <div class="col-md-6">
            <small class="text-muted d-block">Expiry Date</small>
            <strong>{{ $contractor->driver_license_expiry->format('d/m/Y') }}</strong>
            @if($contractor->driver_license_expiry->isPast())
              <br><span class="badge bg-danger">Expired</span>
            @elseif($contractor->driver_license_expiry->diffInDays() < 30)
              <br><span class="badge bg-warning">Expiring Soon</span>
            @endif
          </div>
          @endif
        </div>
      </div>
    </div>
    @endif

    <!-- Certifications Card -->
    @if($contractor->certifications->isNotEmpty())
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Certifications & Qualifications</h5>
      </div>
      <div class="card-body">
        <div class="list-group">
          @foreach($contractor->certifications as $cert)
          <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <strong>{{ $cert->name }}</strong>
                <br><small class="text-muted">{{ $cert->issuing_organization }}</small>
                @if($cert->certificate_number)
                  <br><small class="text-muted">Certificate #{{ $cert->certificate_number }}</small>
                @endif
              </div>
              <div class="text-end">
                <small class="text-muted d-block">Issued: {{ $cert->issue_date->format('d/m/Y') }}</small>
                @if($cert->expiry_date)
                  <small class="text-muted d-block">Expires: {{ $cert->expiry_date->format('d/m/Y') }}</small>
                  @if($cert->expiry_date->isPast())
                    <span class="badge bg-danger">Expired</span>
                  @elseif($cert->expiry_date->diffInDays() < 30)
                    <span class="badge bg-warning">Expiring Soon</span>
                  @else
                    <span class="badge bg-success">Valid</span>
                  @endif
                @endif
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
    @endif

    <!-- Sign-In History Card -->
    @if($contractor->signInLogs->isNotEmpty())
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Recent Sign-In History</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm">
            <thead>
              <tr>
                <th>Date</th>
                <th>Location</th>
                <th>Purpose</th>
                <th>Sign In</th>
                <th>Sign Out</th>
                <th>Duration</th>
              </tr>
            </thead>
            <tbody>
              @foreach($contractor->signInLogs as $log)
              <tr>
                <td>{{ $log->sign_in_time->format('d/m/Y') }}</td>
                <td>{{ $log->location ?? '-' }}</td>
                <td>{{ $log->purpose ?? '-' }}</td>
                <td>{{ $log->sign_in_time->format('H:i') }}</td>
                <td>
                  @if($log->sign_out_time)
                    {{ $log->sign_out_time->format('H:i') }}
                  @else
                    <span class="badge bg-warning">On-Site</span>
                  @endif
                </td>
                <td>
                  @if($log->sign_out_time)
                    {{ gmdate('H:i', $log->sign_out_time->diffInSeconds($log->sign_in_time)) }}
                  @else
                    -
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    @endif

    <!-- Notes Card -->
    @if($contractor->notes)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Additional Notes</h5>
      </div>
      <div class="card-body">
        <p class="mb-0">{{ $contractor->notes }}</p>
      </div>
    </div>
    @endif
  </div>

  <!-- Sidebar -->
  <div class="col-12 col-lg-4">
    <!-- Quick Actions Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Quick Actions</h5>
      </div>
      <div class="card-body">
        <a href="{{ route('contractors.edit', $contractor) }}" class="btn btn-outline-primary w-100 mb-2">
          <i class="bx bx-edit me-1"></i> Edit Contractor
        </a>

        @if(!$contractor->hasValidInduction())
          <button type="button" class="btn btn-outline-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#inductionModal">
            <i class="bx bx-check-circle me-1"></i> Complete Induction
          </button>
        @endif

        @if($contractor->hasValidInduction() && !$contractor->site_access_granted)
          <form action="{{ route('contractors.grant-site-access', $contractor) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-success w-100 mb-2">
              <i class="bx bx-key me-1"></i> Grant Site Access
            </button>
          </form>
        @endif

        @if($contractor->site_access_granted && !$contractor->isSignedIn())
          <button type="button" class="btn btn-outline-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#signInModal">
            <i class="bx bx-log-in me-1"></i> Sign In
          </button>
        @endif

        @if($contractor->isSignedIn())
          <form action="{{ route('contractors.sign-out', $contractor) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-warning w-100 mb-2">
              <i class="bx bx-log-out me-1"></i> Sign Out
            </button>
          </form>
        @endif

        <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteModal">
          <i class="bx bx-trash me-1"></i> Delete Contractor
        </button>
      </div>
    </div>

    <!-- Summary Statistics Card -->
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Summary</h5>
      </div>
      <div class="card-body">
        <ul class="list-unstyled mb-0">
          <li class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted">Registered</span>
            <strong>{{ $contractor->created_at->format('d/m/Y') }}</strong>
          </li>
          <li class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted">Total Sign-Ins</span>
            <strong>{{ $contractor->signInLogs->count() }}</strong>
          </li>
          <li class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted">Certifications</span>
            <strong>{{ $contractor->certifications->count() }}</strong>
          </li>
          <li class="d-flex justify-content-between align-items-center">
            <span class="text-muted">Last Updated</span>
            <strong>{{ $contractor->updated_at->diffForHumans() }}</strong>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- Complete Induction Modal -->
<div class="modal fade" id="inductionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Complete Induction</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('contractors.complete-induction', $contractor) }}" method="POST">
        @csrf
        <div class="modal-body">
          <p>Complete induction for <strong>{{ $contractor->first_name }} {{ $contractor->last_name }}</strong>?</p>

          <div class="mb-3">
            <label for="validity_months" class="form-label">Induction Validity Period *</label>
            <select id="validity_months" name="validity_months" class="form-select" required>
              <option value="12" selected>12 months</option>
              <option value="6">6 months</option>
              <option value="3">3 months</option>
              <option value="24">24 months</option>
            </select>
            <div class="form-text">Standard validity is 12 months</div>
          </div>

          <div class="alert alert-info mb-0">
            <i class="bx bx-info-circle me-2"></i>
            This will mark the contractor as inducted and set expiry date accordingly.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-check-circle me-1"></i> Complete Induction
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Revoke Site Access Modal -->
<div class="modal fade" id="revokeAccessModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Revoke Site Access</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('contractors.revoke-site-access', $contractor) }}" method="POST">
        @csrf
        <div class="modal-body">
          <p>Revoke site access for <strong>{{ $contractor->first_name }} {{ $contractor->last_name }}</strong>?</p>

          <div class="mb-3">
            <label for="reason" class="form-label">Reason for Revocation *</label>
            <textarea id="reason" name="reason" class="form-control" rows="3" required
                      placeholder="Explain why site access is being revoked..."></textarea>
          </div>

          <div class="alert alert-warning mb-0">
            <i class="bx bx-error-circle me-2"></i>
            This will immediately prevent the contractor from accessing the site.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">
            <i class="bx bx-lock me-1"></i> Revoke Access
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Sign In Modal -->
<div class="modal fade" id="signInModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Sign In Contractor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('contractors.sign-in', $contractor) }}" method="POST">
        @csrf
        <div class="modal-body">
          <p class="mb-4">Sign in <strong>{{ $contractor->first_name }} {{ $contractor->last_name }}</strong></p>

          <div class="row g-3">
            <div class="col-md-6">
              <label for="location" class="form-label">Location *</label>
              <input type="text" id="location" name="location" class="form-control"
                     placeholder="e.g., Warehouse 3, Office Building" required>
            </div>

            <div class="col-md-6">
              <label for="entry_method" class="form-label">Entry Method *</label>
              <select id="entry_method" name="entry_method" class="form-select" required>
                <option value="">Select method</option>
                <option value="main_gate">Main Gate</option>
                <option value="side_entrance">Side Entrance</option>
                <option value="contractor_entrance">Contractor Entrance</option>
                <option value="delivery_bay">Delivery Bay</option>
              </select>
            </div>

            <div class="col-12">
              <label for="purpose" class="form-label">Purpose of Visit *</label>
              <input type="text" id="purpose" name="purpose" class="form-control"
                     placeholder="e.g., Electrical maintenance, Equipment installation" required>
            </div>

            <div class="col-12">
              <label for="work_description" class="form-label">Work Description</label>
              <textarea id="work_description" name="work_description" class="form-control" rows="2"
                        placeholder="Describe the work to be performed..."></textarea>
            </div>

            <div class="col-12">
              <label for="areas_accessed" class="form-label">Areas to be Accessed</label>
              <input type="text" id="areas_accessed" name="areas_accessed" class="form-control"
                     placeholder="e.g., Main workshop, Storage area">
            </div>

            <div class="col-12">
              <label for="ppe_items" class="form-label">PPE Items Required</label>
              <input type="text" id="ppe_items" name="ppe_items" class="form-control"
                     placeholder="e.g., Hard hat, safety glasses, steel toe boots">
            </div>

            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="ppe_acknowledged" name="ppe_acknowledged" value="1" required>
                <label class="form-check-label" for="ppe_acknowledged">
                  I acknowledge that I have the required PPE
                </label>
              </div>
            </div>

            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="emergency_procedures_acknowledged"
                       name="emergency_procedures_acknowledged" value="1" required>
                <label class="form-check-label" for="emergency_procedures_acknowledged">
                  I have been briefed on emergency procedures
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-log-in me-1"></i> Sign In
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
        <h5 class="modal-title">Delete Contractor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('contractors.destroy', $contractor) }}" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <p>Are you sure you want to delete <strong>{{ $contractor->first_name }} {{ $contractor->last_name }}</strong>?</p>
          <div class="alert alert-danger mb-0">
            <i class="bx bx-error-circle me-2"></i>
            This action cannot be undone. All contractor records will be permanently removed.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="bx bx-trash me-1"></i> Delete Contractor
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
