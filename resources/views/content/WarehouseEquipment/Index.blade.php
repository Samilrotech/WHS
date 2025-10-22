@php
  setcookie('contentLayout', 'wide', time() + 60 * 60 * 24 * 365, '/');
  $pageConfigs = ['contentLayout' => 'wide'];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Warehouse Equipment')

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
  $filterPills = [
    ['label' => 'All equipment', 'active' => true],
    ['label' => 'Inspection Overdue', 'active' => $statistics['inspection_overdue'] > 0],
    ['label' => 'Maintenance Due', 'active' => $statistics['maintenance_due'] > 0],
    ['label' => 'In Use', 'active' => $statistics['in_use'] > 0],
  ];
@endphp

<div class="whs-shell">
  <x-whs.hero
    eyebrow="Warehouse Operations"
    title="Warehouse Equipment"
    subtitle="Forklift, pallet jack, and warehouse equipment management with QR code tracking, license verification, and checkout workflows across all branches."
    :metric="true"
    metricLabel="Total equipment"
    :metricValue="$statistics['total_equipment']"
    metricCaption="Warehouse equipment inventory"
    :searchRoute="route('warehouse-equipment.index')"
    searchPlaceholder="Search equipment, codes, types…"
    :createRoute="null"
    createLabel=""
    :filters="$filterPills"
  />

  <section class="whs-metrics">
    <x-whs.metric-card
      icon="ti-package"
      iconVariant="brand"
      label="Total Equipment"
      :value="$statistics['total_equipment']"
      meta="All warehouse equipment"
    />

    <x-whs.metric-card
      icon="ti-circle-check"
      iconVariant="success"
      label="Available"
      :value="$statistics['available']"
      meta="Ready for use"
      metaClass="text-success"
    />

    <x-whs.metric-card
      icon="ti-user"
      iconVariant="info"
      label="In Use"
      :value="$statistics['in_use']"
      meta="Currently checked out"
      metaClass="text-info"
    />

    <x-whs.metric-card
      icon="ti-alert-triangle"
      iconVariant="critical"
      label="Inspection Overdue"
      :value="$statistics['inspection_overdue']"
      meta="Requires inspection"
      metaClass="text-danger"
    />
  </section>

  <div class="whs-layout">
    <div class="whs-main">
      <div class="whs-section-heading">
        <div>
          <h2>Equipment register</h2>
          <p>All warehouse equipment sorted by equipment code.</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
          <span class="whs-updated">Updated {{ now()->format('H:i') }}</span>
          <button type="button" class="whs-btn-primary" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">
            <i class="icon-base ti ti-plus"></i>
            Add equipment
          </button>
        </div>
      </div>

      <div class="whs-card-list">
        @forelse ($equipment as $item)
          @php
            $severity = $item->isInspectionOverdue() ? 'critical' :
                       ($item->days_until_inspection <= 7 ? 'high' :
                       ($item->status === 'out_of_service' ? 'medium' : 'low'));
            $statusLabel = match($item->status) {
              'available' => 'Available',
              'in_use' => 'In Use',
              'maintenance' => 'Maintenance',
              'out_of_service' => 'Out of Service',
              default => ucfirst(str_replace('_', ' ', $item->status))
            };
            $typeLabel = ucfirst(str_replace('_', ' ', $item->equipment_type));
          @endphp

          <x-whs.card :severity="$severity">
            <div class="whs-card__header">
              <span class="whs-chip whs-chip--id">
                {{ $item->equipment_code }}
                @if($item->qr_code_path)
                  <i class="icon-base ti ti-qrcode ms-1" title="QR Code Available"></i>
                @endif
              </span>
              <span class="whs-chip whs-chip--status whs-chip--status-{{ strtolower(str_replace(' ', '-', $statusLabel)) }}">
                {{ $statusLabel }}
              </span>
            </div>

            <div class="whs-card__body">
              <div>
                <h3>
                  {{ $item->equipment_name }}
                  @if($item->requires_license)
                    <i class="icon-base ti ti-certificate ms-1 text-warning" title="License Required: {{ $item->license_type }}"></i>
                  @endif
                </h3>
                <p>{{ $typeLabel }} • {{ $item->location ?? 'Location Not Set' }}</p>
              </div>
              <div>
                <span class="whs-location-label">Next Inspection</span>
                <span>
                  @if($item->next_inspection_due)
                    @if($item->isInspectionOverdue())
                      <span class="text-danger fw-bold">
                        {{ $item->next_inspection_due->format('d M Y') }}
                        <span class="whs-chip whs-chip--severity whs-chip--severity-critical ms-1">Overdue</span>
                      </span>
                    @elseif($item->days_until_inspection <= 7)
                      <span class="text-warning fw-bold">
                        {{ $item->next_inspection_due->format('d M Y') }}
                        <span class="whs-chip whs-chip--severity whs-chip--severity-high ms-1">Due Soon</span>
                      </span>
                    @else
                      {{ $item->next_inspection_due->format('d M Y') }}
                    @endif
                  @else
                    <span class="text-muted">Not Scheduled</span>
                  @endif
                </span>
              </div>
              <div>
                <span class="whs-location-label">License Required</span>
                <span>
                  @if($item->requires_license)
                    <span class="whs-chip whs-chip--severity whs-chip--severity-medium">{{ $item->license_type }}</span>
                  @else
                    No
                  @endif
                </span>
              </div>
              <div>
                <span class="whs-location-label">Manufacturer</span>
                <span>{{ $item->manufacturer ?? 'Not Set' }} {{ $item->model ? '• ' . $item->model : '' }}</span>
              </div>
            </div>

            <div class="whs-card__footer">
              <div class="whs-card__actions">
                <a href="{{ route('warehouse-equipment.show', $item) }}" class="whs-action-btn" aria-label="View equipment">
                  <i class="icon-base ti ti-external-link"></i>
                  <span>Open</span>
                </a>

                <button type="button" class="whs-action-btn" onclick="openEditModal('{{ $item->id }}', '{{ $item->equipment_name }}', '{{ $item->equipment_type }}', '{{ $item->status }}', '{{ $item->location }}')">
                  <i class="icon-base ti ti-edit"></i>
                  <span>Edit</span>
                </button>

                @if($item->status === 'available')
                  <button type="button" class="whs-action-btn whs-action-btn--success" onclick="openCheckoutModal('{{ $item->id }}', '{{ $item->equipment_name }}')">
                    <i class="icon-base ti ti-logout"></i>
                    <span>Check Out</span>
                  </button>
                @endif

                @if($item->status === 'in_use')
                  <button type="button" class="whs-action-btn" onclick="openReturnModal('{{ $item->id }}', '{{ $item->equipment_name }}')">
                    <i class="icon-base ti ti-login"></i>
                    <span>Return</span>
                  </button>
                @endif

                @if($item->qr_code_path)
                  <a href="{{ asset('storage/' . $item->qr_code_path) }}" target="_blank" class="whs-action-btn">
                    <i class="icon-base ti ti-qrcode"></i>
                    <span>View QR</span>
                  </a>
                @else
                  <button type="button" class="whs-action-btn" onclick="generateQrCode('{{ $item->id }}')">
                    <i class="icon-base ti ti-qrcode"></i>
                    <span>Generate QR</span>
                  </button>
                @endif

                <button type="button" class="whs-action-btn whs-action-btn--danger" onclick="confirmDelete('{{ $item->id }}', '{{ $item->equipment_name }}')">
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
              <i class="icon-base ti ti-package whs-empty__icon"></i>
              <h3>No equipment yet</h3>
              <p>No warehouse equipment has been registered. Start tracking your forklifts, pallet jacks, and equipment.</p>
              <button type="button" class="whs-btn-primary whs-btn-primary--ghost" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">
                <i class="icon-base ti ti-plus me-2"></i>
                Add first equipment
              </button>
            </div>
          </div>
        @endforelse
      </div>
    </div>

    <aside class="whs-sidebar">
      <x-whs.sidebar-panel title="Equipment status">
        <ul class="whs-sidebar__stats">
          <li>
            <span>Available</span>
            <strong class="text-success">{{ $statistics['available'] }}</strong>
          </li>
          <li>
            <span>In Use</span>
            <strong class="text-info">{{ $statistics['in_use'] }}</strong>
          </li>
          <li>
            <span>Under Maintenance</span>
            <strong class="text-warning">{{ $statistics['maintenance'] }}</strong>
          </li>
          <li>
            <span>Inspection Overdue</span>
            <strong class="text-danger">{{ $statistics['inspection_overdue'] }}</strong>
          </li>
          <li>
            <span>Maintenance Due</span>
            <strong class="text-warning">{{ $statistics['maintenance_due'] }}</strong>
          </li>
        </ul>
        <p class="whs-sidebar__caption">
          QR code tracking enables instant equipment identification and checkout workflows for efficient warehouse operations.
        </p>
      </x-whs.sidebar-panel>

      <x-whs.sidebar-panel title="Equipment types">
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
          <div style="padding: 0.75rem; background: linear-gradient(135deg, rgba(244, 246, 255, 0.96), rgba(228, 233, 255, 0.98)); border-radius: 12px; border: 1px solid rgba(0, 71, 255, 0.12);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Forklift</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">License required, regular inspections</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Pallet Jack</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Manual and electric powered</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Reach Truck</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">High-level storage access</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Order Picker</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Elevated picking operations</span>
          </div>
          <div style="padding: 0.75rem; background: rgba(248, 249, 253, 0.8); border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.18);">
            <strong style="display: block; font-size: 0.85rem; color: var(--whs-slate-900); margin-bottom: 0.25rem;">Scissor Lift</strong>
            <span style="font-size: 0.8rem; color: rgba(51, 65, 85, 0.75);">Temporary elevation platform</span>
          </div>
        </div>
      </x-whs.sidebar-panel>
    </aside>
  </div>
</div>

<!-- Add Equipment Modal -->
<div class="modal fade" id="addEquipmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add New Equipment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('warehouse-equipment.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="equipment_code" class="form-label">Equipment Code *</label>
              <input type="text" id="equipment_code" name="equipment_code" class="form-control" required placeholder="e.g., FK-001">
            </div>
            <div class="col-md-6">
              <label for="equipment_name" class="form-label">Equipment Name *</label>
              <input type="text" id="equipment_name" name="equipment_name" class="form-control" required placeholder="e.g., Forklift #1">
            </div>
            <div class="col-md-6">
              <label for="equipment_type" class="form-label">Equipment Type *</label>
              <select id="equipment_type" name="equipment_type" class="form-select" required>
                <option value="">Select Type</option>
                <option value="forklift">Forklift</option>
                <option value="pallet_jack">Pallet Jack</option>
                <option value="reach_truck">Reach Truck</option>
                <option value="order_picker">Order Picker</option>
                <option value="scissor_lift">Scissor Lift</option>
                <option value="hand_truck">Hand Truck</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="manufacturer" class="form-label">Manufacturer</label>
              <input type="text" id="manufacturer" name="manufacturer" class="form-control" placeholder="e.g., Toyota">
            </div>
            <div class="col-md-6">
              <label for="model" class="form-label">Model</label>
              <input type="text" id="model" name="model" class="form-control" placeholder="e.g., 8FBE15U">
            </div>
            <div class="col-md-6">
              <label for="location" class="form-label">Location</label>
              <input type="text" id="location" name="location" class="form-control" placeholder="e.g., Warehouse A">
            </div>
            <div class="col-md-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="requires_license" name="requires_license" value="1">
                <label class="form-check-label" for="requires_license">
                  Requires License
                </label>
              </div>
            </div>
            <div class="col-md-6" id="license_type_container" style="display:none;">
              <label for="license_type" class="form-label">License Type</label>
              <input type="text" id="license_type" name="license_type" class="form-control" placeholder="e.g., Forklift LF">
            </div>
            <div class="col-md-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="generate_qr" name="generate_qr" value="1" checked>
                <label class="form-check-label" for="generate_qr">
                  Generate QR Code
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="icon-base ti ti-plus me-1"></i> Add Equipment
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Equipment Modal -->
<div class="modal fade" id="editEquipmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Equipment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editEquipmentForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label for="edit_equipment_name" class="form-label">Equipment Name *</label>
              <input type="text" id="edit_equipment_name" name="equipment_name" class="form-control" required>
            </div>
            <div class="col-12">
              <label for="edit_equipment_type" class="form-label">Equipment Type *</label>
              <select id="edit_equipment_type" name="equipment_type" class="form-select" required>
                <option value="forklift">Forklift</option>
                <option value="pallet_jack">Pallet Jack</option>
                <option value="reach_truck">Reach Truck</option>
                <option value="order_picker">Order Picker</option>
                <option value="scissor_lift">Scissor Lift</option>
                <option value="hand_truck">Hand Truck</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="col-12">
              <label for="edit_status" class="form-label">Status *</label>
              <select id="edit_status" name="status" class="form-select" required>
                <option value="available">Available</option>
                <option value="in_use">In Use</option>
                <option value="maintenance">Maintenance</option>
                <option value="out_of_service">Out of Service</option>
              </select>
            </div>
            <div class="col-12">
              <label for="edit_location" class="form-label">Location</label>
              <input type="text" id="edit_location" name="location" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="icon-base ti ti-device-floppy me-1"></i> Update Equipment
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Check Out Equipment Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Check Out Equipment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="checkoutForm" method="POST">
        @csrf
        <div class="modal-body">
          <p>Check out <strong id="checkoutEquipmentName"></strong>?</p>
          <div class="mb-3">
            <label for="checkout_notes" class="form-label">Notes (Optional)</label>
            <textarea id="checkout_notes" name="notes" class="form-control" rows="3" placeholder="Purpose of use..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="icon-base ti ti-logout me-1"></i> Check Out
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Return Equipment Modal -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Return Equipment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="returnForm" method="POST">
        @csrf
        <div class="modal-body">
          <p>Return <strong id="returnEquipmentName"></strong>?</p>
          <div class="mb-3">
            <label for="return_condition" class="form-label">Condition *</label>
            <select id="return_condition" name="condition" class="form-select" required>
              <option value="">Select Condition</option>
              <option value="good">Good - No Issues</option>
              <option value="minor_wear">Minor Wear - Normal Usage</option>
              <option value="damaged">Damaged - Requires Attention</option>
              <option value="needs_maintenance">Needs Maintenance</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="return_notes" class="form-label">Return Notes</label>
            <textarea id="return_notes" name="notes" class="form-control" rows="3" placeholder="Any issues or observations..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="icon-base ti ti-login me-1"></i> Return Equipment
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
        <h5 class="modal-title">Delete Equipment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="deleteForm" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body">
          <p>Are you sure you want to delete <strong id="deleteEquipmentName"></strong>?</p>
          <div class="alert alert-warning mb-0">
            <i class="icon-base ti ti-alert-circle me-2"></i>
            This will also delete all inspection and custody history. This action cannot be undone.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="icon-base ti ti-trash me-1"></i> Delete Equipment
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@section('page-script')
<script>
$(document).ready(function() {
  // Toggle license type field
  $('#requires_license').on('change', function() {
    if($(this).is(':checked')) {
      $('#license_type_container').show();
      $('#license_type').attr('required', true);
    } else {
      $('#license_type_container').hide();
      $('#license_type').attr('required', false);
    }
  });
});

// Open Edit Modal
function openEditModal(id, name, type, status, location) {
  document.getElementById('edit_equipment_name').value = name;
  document.getElementById('edit_equipment_type').value = type;
  document.getElementById('edit_status').value = status;
  document.getElementById('edit_location').value = location || '';
  document.getElementById('editEquipmentForm').action = '/warehouse-equipment/' + id;

  var editModal = new bootstrap.Modal(document.getElementById('editEquipmentModal'));
  editModal.show();
}

// Open Checkout Modal
function openCheckoutModal(id, name) {
  document.getElementById('checkoutEquipmentName').textContent = name;
  document.getElementById('checkoutForm').action = '/warehouse-equipment/' + id + '/checkout';

  var checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
  checkoutModal.show();
}

// Open Return Modal
function openReturnModal(id, name) {
  document.getElementById('returnEquipmentName').textContent = name;
  document.getElementById('returnForm').action = '/warehouse-equipment/' + id + '/return';

  var returnModal = new bootstrap.Modal(document.getElementById('returnModal'));
  returnModal.show();
}

// Delete confirmation
function confirmDelete(id, name) {
  document.getElementById('deleteEquipmentName').textContent = name;
  document.getElementById('deleteForm').action = '/warehouse-equipment/' + id;

  var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
  deleteModal.show();
}

// Generate QR Code
function generateQrCode(id) {
  if(confirm('Generate QR code for this equipment?')) {
    window.location.href = '/warehouse-equipment/' + id + '/generate-qr';
  }
}
</script>
@endsection

