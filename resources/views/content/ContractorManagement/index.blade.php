@php
  setcookie('contentLayout', 'wide', time() + 60 * 60 * 24 * 365, '/');
  $pageConfigs = ['contentLayout' => 'wide'];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Contractor Management')

@section('page-script')
<script>
  (function ensureWideLayout() {
    var expires = new Date();
    expires.setFullYear(expires.getFullYear() + 1);
    document.cookie = 'contentLayout=wide;path=/;expires=' + expires.toUTCString();
  })();
</script>
@endsection

@section('content')
@include('layouts.sections.flash-message')

@php
  $activeContractors = $contractors->where('status', 'active')->count();
  $withSiteAccess = $contractors->where('site_access_granted', true)->count();
  $currentlyOnSite = $contractors->filter(fn($c) => $c->isSignedIn())->count();

  $filterPills = [
    ['label' => 'All contractors', 'active' => true],
    ['label' => 'Active', 'active' => $activeContractors > 0],
    ['label' => 'With Site Access', 'active' => $withSiteAccess > 0],
    ['label' => 'On-Site Now', 'active' => $currentlyOnSite > 0],
  ];
@endphp

<div class="whs-shell">
  <!-- Expiring Inductions Alert -->
  @if($expiringInductions->isNotEmpty())
  <div class="alert alert-warning alert-dismissible mb-4" role="alert">
    <h5 class="alert-heading mb-2">
      <i class="icon-base ti ti-clock"></i>
      {{ $expiringInductions->count() }} Contractor Induction(s) Expiring Soon
    </h5>
    <p class="mb-0">The following contractors have inductions expiring within 30 days:</p>
    <ul class="mb-0 mt-2">
      @foreach($expiringInductions as $contractor)
      <li>
        <strong>{{ $contractor->first_name }} {{ $contractor->last_name }}</strong>
        ({{ $contractor->company->name ?? 'No Company' }})
        - Expires {{ $contractor->induction_expiry_date->format('d/m/Y') }}
        ({{ $contractor->induction_expiry_date->diffForHumans() }})
      </li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <x-whs.hero
    eyebrow="Contractor & Site Access"
    title="Contractor Management"
    subtitle="Track contractor inductions, site access permissions, and on-site presence with automated sign-in workflows across all branches."
    :metric="true"
    metricLabel="Total contractors"
    :metricValue="$contractors->count()"
    metricCaption="Registered contractor registry"
    :searchRoute="route('contractors.index')"
    searchPlaceholder="Search contractors, companies, status…"
    :createRoute="route('contractors.create')"
    createLabel="Add contractor"
    :filters="$filterPills"
  />

  <section class="whs-metrics">
    <x-whs.metric-card
      icon="ti-users"
      iconVariant="brand"
      label="Total Contractors"
      :value="$contractors->count()"
      meta="All registered contractors"
    />

    <x-whs.metric-card
      icon="ti-circle-check"
      iconVariant="success"
      label="Active"
      :value="$activeContractors"
      meta="Currently active"
      metaClass="text-success"
    />

    <x-whs.metric-card
      icon="ti-key"
      iconVariant="success"
      label="With Site Access"
      :value="$withSiteAccess"
      meta="Authorized for entry"
      metaClass="text-info"
    />

    <x-whs.metric-card
      icon="ti-map-pin"
      iconVariant="warning"
      label="Currently On-Site"
      :value="$currentlyOnSite"
      meta="Signed in now"
      metaClass="text-warning"
    />
  </section>

  <div class="whs-layout">
    <div class="whs-main">
      <div class="whs-section-heading">
        <div>
          <h2>Contractor register</h2>
          <p>All contractors sorted by name with induction status and site access permissions.</p>
        </div>
        <span class="whs-updated">Updated {{ now()->format('H:i') }}</span>
      </div>

      <div class="whs-card-list">
        @forelse ($contractors as $contractor)
          @php
            $severity = !$contractor->hasValidInduction() ? 'critical' : ($contractor->isInductionExpiringSoon() ? 'high' : 'low');
            $statusLabel = match($contractor->status) {
              'active' => 'Active',
              'suspended' => 'Suspended',
              default => 'Inactive'
            };
          @endphp

          <x-whs.card :severity="$severity">
            <div class="whs-card__header">
              <span class="whs-chip whs-chip--id">{{ $contractor->first_name }} {{ $contractor->last_name }}</span>
              <span class="whs-chip whs-chip--status whs-chip--status-{{ strtolower($statusLabel) }}">
                {{ $statusLabel }}
              </span>
            </div>

            <div class="whs-card__body">
              <div>
                <h3>{{ $contractor->company->name ?? 'No Company' }}</h3>
                <p>{{ $contractor->email }} • {{ $contractor->phone }}</p>
              </div>
              <div>
                <span class="whs-location-label">Induction Status</span>
                <span>
                  @if($contractor->hasValidInduction())
                    <span class="whs-chip whs-chip--severity whs-chip--severity-low">Valid until {{ $contractor->induction_expiry_date->format('d/m/Y') }}</span>
                    @if($contractor->isInductionExpiringSoon())
                      <br><small class="text-warning" style="font-size: 0.75rem;">Expires {{ $contractor->induction_expiry_date->diffForHumans() }}</small>
                    @endif
                  @elseif($contractor->induction_completed)
                    <span class="whs-chip whs-chip--severity whs-chip--severity-critical">Expired {{ $contractor->induction_expiry_date->format('d/m/Y') }}</span>
                  @else
                    <span class="whs-chip whs-chip--severity whs-chip--severity-medium">Pending</span>
                  @endif
                </span>
              </div>
              <div>
                <span class="whs-location-label">Site Access</span>
                <span>
                  @if($contractor->site_access_granted)
                    <span class="whs-chip whs-chip--severity whs-chip--severity-low">Granted</span>
                  @else
                    <span class="whs-chip whs-chip--severity whs-chip--severity-medium">No Access</span>
                  @endif
                </span>
              </div>
              <div>
                <span class="whs-location-label">On-Site Status</span>
                <span>
                  @if($contractor->isSignedIn())
                    <span class="whs-chip whs-chip--severity whs-chip--severity-medium">On-Site</span>
                  @else
                    Off-Site
                  @endif
                </span>
              </div>
            </div>

            <div class="whs-card__footer">
              <div class="whs-card__actions">
                <a href="{{ route('contractors.show', $contractor) }}" class="whs-action-btn" aria-label="View contractor">
                  <i class="icon-base ti ti-external-link"></i>
                  <span>Open</span>
                </a>

                <a href="{{ route('contractors.edit', $contractor) }}" class="whs-action-btn" aria-label="Edit contractor">
                  <i class="icon-base ti ti-edit"></i>
                  <span>Edit</span>
                </a>

                @if(!$contractor->hasValidInduction())
                  <button type="button" class="whs-action-btn whs-action-btn--success" onclick="openInductionModal({{ $contractor->id }}, '{{ $contractor->first_name }} {{ $contractor->last_name }}')">
                    <i class="icon-base ti ti-circle-check"></i>
                    <span>Induction</span>
                  </button>
                @endif

                @if($contractor->hasValidInduction() && !$contractor->site_access_granted)
                  <form action="{{ route('contractors.grant-site-access', $contractor) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="whs-action-btn whs-action-btn--success">
                      <i class="icon-base ti ti-key"></i>
                      <span>Grant Access</span>
                    </button>
                  </form>
                @endif

                @if($contractor->site_access_granted && !$contractor->isSignedIn())
                  <button type="button" class="whs-action-btn" onclick="openSignInModal({{ $contractor->id}}, '{{ $contractor->first_name }} {{ $contractor->last_name }}')">
                    <i class="icon-base ti ti-login"></i>
                    <span>Sign In</span>
                  </button>
                @endif

                @if($contractor->isSignedIn())
                  <form action="{{ route('contractors.sign-out', $contractor) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="whs-action-btn">
                      <i class="icon-base ti ti-logout"></i>
                      <span>Sign Out</span>
                    </button>
                  </form>
                @endif

                <button type="button" class="whs-action-btn whs-action-btn--danger" onclick="openDeleteModal({{ $contractor->id }}, '{{ $contractor->first_name }} {{ $contractor->last_name }}')">
                  <i class="icon-base ti ti-trash"></i>
                  <span>Delete</span>
                </button>
              </div>
              <button class="whs-card__more">
                <i class="icon-base ti ti-dots"></i>
              </button>
            </div>
          </x-whs.card>
        @empty
          <div class="whs-empty">
            <div class="whs-empty__content">
              <i class="icon-base ti ti-users whs-empty__icon"></i>
              <h3>No contractors yet</h3>
              <p>No contractors have been registered. Start tracking contractor inductions and site access.</p>
              <a href="{{ route('contractors.create') }}" class="whs-btn-primary whs-btn-primary--ghost">
                <i class="icon-base ti ti-plus me-2"></i>
                Add first contractor
              </a>
            </div>
          </div>
        @endforelse
      </div>
    </div>

    <aside class="whs-sidebar">
      <x-whs.sidebar-panel title="Site access workflow">
        <ul class="whs-sidebar__stats">
          <li>
            <span>Active Contractors</span>
            <strong class="text-success">{{ $activeContractors }}</strong>
          </li>
          <li>
            <span>With Site Access</span>
            <strong class="text-info">{{ $withSiteAccess }}</strong>
          </li>
          <li>
            <span>Currently On-Site</span>
            <strong class="text-warning">{{ $currentlyOnSite }}</strong>
          </li>
        </ul>
        <p class="whs-sidebar__caption">
          Automated induction tracking ensures all contractors complete safety requirements before accessing sites.
        </p>
      </x-whs.sidebar-panel>

      <x-whs.sidebar-panel title="Contractor lifecycle">
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
          <div style="padding: 0.75rem; background: linear-gradient(135deg, rgba(244, 246, 255, 0.96), rgba(228, 233, 255, 0.98)); border-radius: 12px; border: 1px solid rgba(0, 71, 255, 0.12);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">1. Registration</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Add contractor details and company</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">2. Induction</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Complete safety induction (12-month validity)</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">3. Grant Access</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Authorize site access after induction</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">4. Sign In/Out</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Track on-site presence and work details</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">5. Renewal</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Automated expiry reminders</span>
          </div>
        </div>
      </x-whs.sidebar-panel>
    </aside>
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
      <form id="inductionForm" method="POST">
        @csrf
        <div class="modal-body">
          <p>Complete induction for <strong id="inductionContractorName"></strong>?</p>

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
            <i class="icon-base ti ti-info-circle me-2"></i>
            This will mark the contractor as inducted and set expiry date accordingly.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="icon-base ti ti-circle-check me-1"></i> Complete Induction
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
      <form id="revokeAccessForm" method="POST">
        @csrf
        <div class="modal-body">
          <p>Revoke site access for <strong id="revokeContractorName"></strong>?</p>

          <div class="mb-3">
            <label for="revoke_reason" class="form-label">Reason for Revocation *</label>
            <textarea id="revoke_reason" name="reason" class="form-control" rows="3" required
                      placeholder="Explain why site access is being revoked..."></textarea>
          </div>

          <div class="alert alert-warning mb-0">
            <i class="icon-base ti ti-alert-circle me-2"></i>
            This will immediately prevent the contractor from accessing the site.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">
            <i class="icon-base ti ti-lock me-1"></i> Revoke Access
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
      <form id="signInForm" method="POST">
        @csrf
        <div class="modal-body">
          <p class="mb-4">Sign in <strong id="signInContractorName"></strong></p>

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
            <i class="icon-base ti ti-login me-1"></i> Sign In
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
      <form id="deleteForm" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <p>Are you sure you want to delete <strong id="deleteContractorName"></strong>?</p>
          <div class="alert alert-danger mb-0">
            <i class="icon-base ti ti-alert-circle me-2"></i>
            This action cannot be undone. All contractor records will be permanently removed.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="icon-base ti ti-trash me-1"></i> Delete Contractor
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@section('page-script')
<script>
// Complete Induction Modal
function openInductionModal(contractorId, contractorName) {
  document.getElementById('inductionContractorName').textContent = contractorName;
  document.getElementById('inductionForm').action = `/contractors/${contractorId}/complete-induction`;
  new bootstrap.Modal(document.getElementById('inductionModal')).show();
}

// Revoke Site Access Modal
function openRevokeAccessModal(contractorId, contractorName) {
  document.getElementById('revokeContractorName').textContent = contractorName;
  document.getElementById('revokeAccessForm').action = `/contractors/${contractorId}/revoke-site-access`;
  new bootstrap.Modal(document.getElementById('revokeAccessModal')).show();
}

// Sign In Modal
function openSignInModal(contractorId, contractorName) {
  document.getElementById('signInContractorName').textContent = contractorName;
  document.getElementById('signInForm').action = `/contractors/${contractorId}/sign-in`;
  new bootstrap.Modal(document.getElementById('signInModal')).show();
}

// Delete Modal
function openDeleteModal(contractorId, contractorName) {
  document.getElementById('deleteContractorName').textContent = contractorName;
  document.getElementById('deleteForm').action = `/contractors/${contractorId}`;
  new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endsection

