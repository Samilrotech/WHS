@extends('layouts/layoutMaster')

@section('title', 'Parts Inventory')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
@endsection

@section('content')
<h4 class="mb-1">Parts Inventory</h4>
<p class="mb-4">Track spare parts stock levels and reorder management</p>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
  <!-- Total Parts Card -->
  <div class="col-sm-6 col-lg-3">
    <div class="card statistics-card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Total Parts</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $statistics['total_parts'] ?? 0 }}</h4>
            </div>
            <small class="mb-0">All inventory items</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="bx bx-box bx-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Low Stock Card -->
  <div class="col-sm-6 col-lg-3">
    <div class="card statistics-card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Low Stock</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $statistics['low_stock'] ?? 0 }}</h4>
              @if(($statistics['low_stock'] ?? 0) > 0)
              <span class="text-warning ms-1">
                <i class="bx bx-error-circle"></i>
              </span>
              @endif
            </div>
            <small class="mb-0">Below reorder point</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="bx bx-error bx-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Out of Stock Card -->
  <div class="col-sm-6 col-lg-3">
    <div class="card statistics-card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Out of Stock</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $statistics['out_of_stock'] ?? 0 }}</h4>
              @if(($statistics['out_of_stock'] ?? 0) > 0)
              <span class="text-danger ms-1">
                <i class="bx bx-trending-up"></i>
              </span>
              @endif
            </div>
            <small class="mb-0">Urgent restock needed</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-danger">
              <i class="bx bx-x-circle bx-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Total Value Card -->
  <div class="col-sm-6 col-lg-3">
    <div class="card statistics-card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Total Value</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">${{ number_format($statistics['total_value'] ?? 0, 2) }}</h4>
            </div>
            <small class="mb-0">Inventory worth</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-success">
              <i class="bx bx-dollar-circle bx-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Needs Reorder Card -->
  <div class="col-sm-6 col-lg-3">
    <div class="card statistics-card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Needs Reorder</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $statistics['needs_reorder'] ?? 0 }}</h4>
            </div>
            <small class="mb-0">Action required</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-info">
              <i class="bx bx-cart bx-md"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Parts Table Card -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Parts Inventory</h5>
    <div class="card-action d-flex gap-2">
      <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addPartModal">
        <i class="bx bx-plus me-1"></i> Add Part
      </button>
      <button type="button" class="btn btn-primary" onclick="window.print()">
        <i class="bx bx-printer me-1"></i> Print Inventory
      </button>
    </div>
  </div>
  <div class="card-body">
    <!-- Filters -->
    <div class="row mb-3">
      <div class="col-md-3">
        <label for="filterCategory" class="form-label">Category</label>
        <select id="filterCategory" class="form-select form-select-sm">
          <option value="">All Categories</option>
          <option value="filters">Filters</option>
          <option value="fluids">Fluids</option>
          <option value="brakes">Brakes</option>
          <option value="electrical">Electrical</option>
          <option value="tires">Tires</option>
          <option value="belts_hoses">Belts & Hoses</option>
          <option value="wipers">Wipers</option>
          <option value="other">Other</option>
        </select>
      </div>
      <div class="col-md-3">
        <label for="filterStatus" class="form-label">Stock Status</label>
        <select id="filterStatus" class="form-select form-select-sm">
          <option value="">All Status</option>
          <option value="normal">Normal</option>
          <option value="low_stock">Low Stock</option>
          <option value="out_of_stock">Out of Stock</option>
          <option value="overstocked">Overstocked</option>
        </select>
      </div>
      <div class="col-md-3">
        <label for="filterCritical" class="form-label">Critical Parts</label>
        <select id="filterCritical" class="form-select form-select-sm">
          <option value="">All Parts</option>
          <option value="yes">Critical Only</option>
          <option value="no">Non-Critical</option>
        </select>
      </div>
      <div class="col-md-3">
        <label for="filterMoving" class="form-label">Movement</label>
        <select id="filterMoving" class="form-select form-select-sm">
          <option value="">All</option>
          <option value="fast">Fast Moving</option>
          <option value="slow">Slow Moving</option>
        </select>
      </div>
    </div>

    <!-- DataTable -->
    <table id="partsTable" class="table table-hover">
      <thead>
        <tr>
          <th>Part Number</th>
          <th>Part Name</th>
          <th>Category</th>
          <th>Qty on Hand</th>
          <th>Reorder Point</th>
          <th>Unit Cost</th>
          <th>Total Value</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($parts as $part)
        <tr class="{{ $part->isOutOfStock() ? 'table-danger' : ($part->isLowStock() ? 'table-warning' : '') }}">
          <td>
            <span class="fw-semibold">{{ $part->part_number }}</span>
            @if($part->critical_part)
            <span class="badge bg-danger badge-sm ms-1" title="Critical Part">
              <i class="bx bx-error-circle"></i>
            </span>
            @endif
          </td>
          <td>{{ $part->part_name }}</td>
          <td>
            <span class="badge badge-light-info">{{ ucfirst(str_replace('_', ' ', $part->part_category)) }}</span>
          </td>
          <td>
            @if($part->isOutOfStock())
            <span class="text-danger fw-bold">{{ $part->quantity_on_hand }}</span>
            <i class="bx bx-x-circle text-danger ms-1"></i>
            @elseif($part->isLowStock())
            <span class="text-warning fw-bold">{{ $part->quantity_on_hand }}</span>
            <i class="bx bx-error-circle text-warning ms-1"></i>
            @else
            {{ $part->quantity_on_hand }}
            @endif
          </td>
          <td>{{ $part->reorder_point }}</td>
          <td>${{ number_format($part->unit_cost, 2) }}</td>
          <td><span class="fw-semibold">${{ number_format($part->inventory_value, 2) }}</span></td>
          <td>
            @php
            $statusClass = match($part->stock_status) {
              'out_of_stock' => 'danger',
              'low_stock' => 'warning',
              'overstocked' => 'info',
              'normal' => 'success',
              default => 'secondary'
            };
            @endphp
            <span class="badge bg-{{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $part->stock_status)) }}</span>
            @if($part->needsReorder())
            <span class="badge bg-danger badge-sm">Reorder</span>
            @endif
          </td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                <i class="bx bx-dots-vertical-rounded"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end">
                <a class="dropdown-item" href="javascript:void(0);"
                   onclick="viewPartDetails('{{ $part->id }}', '{{ $part->part_number }}', '{{ $part->part_name }}', {{ $part->quantity_on_hand }}, {{ $part->reorder_point }}, {{ $part->unit_cost }})">
                  <i class="bx bx-show me-1"></i> View Details
                </a>
                <a class="dropdown-item" href="javascript:void(0);"
                   onclick="openUpdateModal('{{ $part->id }}', '{{ $part->part_name }}', {{ $part->quantity_on_hand }}, {{ $part->reorder_point }}, {{ $part->unit_cost }})">
                  <i class="bx bx-edit me-1"></i> Update Stock
                </a>

                @if($part->needsReorder())
                <a class="dropdown-item" href="javascript:void(0);"
                   onclick="openReorderModal('{{ $part->id }}', '{{ $part->part_name }}', {{ $part->recommended_order_quantity }}, '{{ $part->supplier_name ?? 'N/A' }}')">
                  <i class="bx bx-cart me-1"></i> Reorder ({{ $part->recommended_order_quantity }} units)
                </a>
                @endif

                <a class="dropdown-item" href="javascript:void(0);"
                   onclick="openRestockModal('{{ $part->id }}', '{{ $part->part_name }}', {{ $part->recommended_order_quantity }})">
                  <i class="bx bx-package me-1"></i> Restock
                </a>

                <a class="dropdown-item" href="javascript:void(0);"
                   onclick="openConsumeModal('{{ $part->id }}', '{{ $part->part_name }}', {{ $part->quantity_on_hand }})">
                  <i class="bx bx-minus-circle me-1"></i> Consume Stock
                </a>
              </div>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- Add Part Modal -->
<div class="modal fade" id="addPartModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add New Part</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('parts.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="part_number" class="form-label">Part Number *</label>
              <input type="text" id="part_number" name="part_number" class="form-control" required
                     placeholder="e.g., FLT-001">
            </div>
            <div class="col-md-6">
              <label for="part_name" class="form-label">Part Name *</label>
              <input type="text" id="part_name" name="part_name" class="form-control" required
                     placeholder="e.g., Engine Oil Filter">
            </div>
            <div class="col-md-12">
              <label for="description" class="form-label">Description</label>
              <textarea id="description" name="description" class="form-control" rows="2"
                        placeholder="Enter part description..."></textarea>
            </div>
            <div class="col-md-6">
              <label for="part_category" class="form-label">Category *</label>
              <select id="part_category" name="part_category" class="form-select" required>
                <option value="">Select category</option>
                <option value="filters">Filters</option>
                <option value="fluids">Fluids</option>
                <option value="brakes">Brakes</option>
                <option value="electrical">Electrical</option>
                <option value="tires">Tires</option>
                <option value="belts_hoses">Belts & Hoses</option>
                <option value="wipers">Wipers</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="quantity_on_hand" class="form-label">Initial Quantity *</label>
              <input type="number" id="quantity_on_hand" name="quantity_on_hand" class="form-control"
                     min="0" required value="0">
            </div>
            <div class="col-md-6">
              <label for="reorder_point" class="form-label">Reorder Point *</label>
              <input type="number" id="reorder_point" name="reorder_point" class="form-control"
                     min="0" required placeholder="e.g., 5">
            </div>
            <div class="col-md-6">
              <label for="reorder_quantity" class="form-label">Reorder Quantity *</label>
              <input type="number" id="reorder_quantity" name="reorder_quantity" class="form-control"
                     min="1" required placeholder="e.g., 10">
            </div>
            <div class="col-md-6">
              <label for="unit_cost" class="form-label">Unit Cost</label>
              <input type="number" id="unit_cost" name="unit_cost" class="form-control"
                     step="0.01" min="0" placeholder="0.00">
            </div>
            <div class="col-md-6">
              <label for="supplier_name" class="form-label">Supplier Name</label>
              <input type="text" id="supplier_name" name="supplier_name" class="form-control"
                     placeholder="e.g., Auto Parts Supplier">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Add Part
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Update Stock Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Update Stock</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="updateForm" method="POST">
        @csrf
        @method('PATCH')
        <div class="modal-body">
          <p>Update stock information for <strong id="updatePartName"></strong></p>
          <div class="mb-3">
            <label for="update_quantity" class="form-label">Quantity on Hand *</label>
            <input type="number" id="update_quantity" name="quantity_on_hand" class="form-control" min="0" required>
          </div>
          <div class="mb-3">
            <label for="update_reorder" class="form-label">Reorder Point *</label>
            <input type="number" id="update_reorder" name="reorder_point" class="form-control" min="0" required>
          </div>
          <div class="mb-3">
            <label for="update_cost" class="form-label">Unit Cost</label>
            <input type="number" id="update_cost" name="unit_cost" class="form-control" step="0.01" min="0">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-check me-1"></i> Update
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Restock Modal -->
<div class="modal fade" id="restockModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Restock Part</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="restockForm" method="POST">
        @csrf
        <div class="modal-body">
          <p>Add stock for <strong id="restockPartName"></strong></p>
          <div class="mb-3">
            <label for="restock_quantity" class="form-label">Quantity to Add *</label>
            <input type="number" id="restock_quantity" name="quantity" class="form-control" min="1" required>
            <div class="form-text">Recommended: <span id="recommendedQty"></span> units</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class="bx bx-package me-1"></i> Restock
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Consume Stock Modal -->
<div class="modal fade" id="consumeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Consume Stock</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="consumeForm" method="POST">
        @csrf
        <div class="modal-body">
          <p>Use stock for <strong id="consumePartName"></strong></p>
          <p class="text-muted">Available: <span id="availableQty" class="fw-semibold"></span> units</p>
          <div class="mb-3">
            <label for="consume_quantity" class="form-label">Quantity to Use *</label>
            <input type="number" id="consume_quantity" name="quantity" class="form-control" min="1" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">
            <i class="bx bx-minus-circle me-1"></i> Consume
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
  // Initialize DataTable
  var table = $('#partsTable').DataTable({
    order: [[0, 'asc']], // Sort by Part Number
    pageLength: 25,
    responsive: true,
    dom: '<"card-header"<"head-label"><"dt-action-buttons text-end"B>><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
  });

  // Filter by Category
  $('#filterCategory').on('change', function() {
    table.column(2).search(this.value).draw();
  });

  // Filter by Status
  $('#filterStatus').on('change', function() {
    table.column(7).search(this.value).draw();
  });

  // Filter by Critical
  $('#filterCritical').on('change', function() {
    const value = this.value;
    if (value === 'yes') {
      table.column(0).search('Critical', false, false).draw();
    } else if (value === 'no') {
      table.column(0).search('^((?!Critical).)*$', true, false).draw();
    } else {
      table.column(0).search('').draw();
    }
  });
});

// Update stock modal
function openUpdateModal(partId, partName, quantity, reorder, cost) {
  document.getElementById('updatePartName').textContent = partName;
  document.getElementById('update_quantity').value = quantity;
  document.getElementById('update_reorder').value = reorder;
  document.getElementById('update_cost').value = cost;
  document.getElementById('updateForm').action = '/parts/' + partId;

  var updateModal = new bootstrap.Modal(document.getElementById('updateModal'));
  updateModal.show();
}

// Restock modal
function openRestockModal(partId, partName, recommendedQty) {
  document.getElementById('restockPartName').textContent = partName;
  document.getElementById('recommendedQty').textContent = recommendedQty;
  document.getElementById('restock_quantity').value = recommendedQty;
  document.getElementById('restockForm').action = '/parts/' + partId + '/restock';

  var restockModal = new bootstrap.Modal(document.getElementById('restockModal'));
  restockModal.show();
}

// Consume stock modal
function openConsumeModal(partId, partName, availableQty) {
  document.getElementById('consumePartName').textContent = partName;
  document.getElementById('availableQty').textContent = availableQty;
  document.getElementById('consume_quantity').max = availableQty;
  document.getElementById('consumeForm').action = '/parts/' + partId + '/consume';

  var consumeModal = new bootstrap.Modal(document.getElementById('consumeModal'));
  consumeModal.show();
}

// View part details (simple alert for now)
function viewPartDetails(id, number, name, qty, reorder, cost) {
  alert(`Part Details:\n\nPart Number: ${number}\nName: ${name}\nQuantity: ${qty}\nReorder Point: ${reorder}\nUnit Cost: $${cost}`);
}
</script>
@endsection
