{{-- Dense Table View for Vehicle Management --}}
{{-- Reference: Team Management dense table pattern --}}

{{-- Table Toolbar --}}
<x-whs.table-toolbar
  searchPlaceholder="Search vehicles..."
  :showViewToggle="false"
  :showExport="true"
>
  {{-- Search Input --}}
  <x-slot name="search">
    <input
      type="text"
      class="whs-search-input"
      placeholder="Search by registration, make, model, or VIN..."
      value="{{ request('search') }}"
      name="search"
      aria-label="Search vehicles"
    />
  </x-slot>

  {{-- Filter Pills --}}
  <x-slot name="filters">
    @if(request('status'))
      <span class="whs-filter-pill">
        Status: {{ ucfirst(request('status')) }}
        <button type="button" class="whs-filter-pill__remove" data-filter="status" aria-label="Remove status filter">×</button>
      </span>
    @endif
    @if(request('make'))
      <span class="whs-filter-pill">
        Make: {{ request('make') }}
        <button type="button" class="whs-filter-pill__remove" data-filter="make" aria-label="Remove make filter">×</button>
      </span>
    @endif
    @if(request('branch'))
      <span class="whs-filter-pill">
        Branch: {{ $branches->firstWhere('id', request('branch'))?->name ?? 'Unknown' }}
        <button type="button" class="whs-filter-pill__remove" data-filter="branch" aria-label="Remove branch filter">×</button>
      </span>
    @endif
    @if(request('assigned') && request('assigned') !== 'all')
      <span class="whs-filter-pill">
        {{ request('assigned') === 'yes' ? 'Assigned Only' : 'Available Only' }}
        <button type="button" class="whs-filter-pill__remove" data-filter="assigned" aria-label="Remove assignment filter">×</button>
      </span>
    @endif
  </x-slot>

  {{-- Bulk Actions --}}
  <x-slot name="bulkActions">
    <button type="button" class="whs-action-btn whs-action-btn--icon" data-bulk-action="export" title="Export selected vehicles">
      <i class="icon-base ti ti-download"></i>
      <span>Export</span>
    </button>
    <button type="button" class="whs-action-btn whs-action-btn--icon" data-bulk-action="print-qr" title="Print QR codes for selected">
      <i class="icon-base ti ti-qrcode"></i>
      <span>Print QR</span>
    </button>
  </x-slot>

  {{-- Primary Action --}}
  <x-slot name="actions">
    <a href="{{ route('vehicles.create') }}" class="whs-action-btn whs-action-btn--primary">
      <i class="icon-base ti ti-plus"></i>
      <span>Add Vehicle</span>
    </a>
  </x-slot>
</x-whs.table-toolbar>

{{-- Data Table --}}
<x-whs.table
  :density="'comfortable'"
  :striped="true"
  :hover="true"
>
  {{-- Table Header --}}
  <x-slot name="header">
    <tr>
      <th class="whs-table__cell--checkbox">
        <input type="checkbox" class="whs-checkbox" id="selectAll" aria-label="Select all vehicles">
      </th>
      <th class="whs-table__cell--sortable" data-sort="registration_number">
        <span class="whs-table__sort-label">Registration</span>
        <i class="icon-base ti ti-selector"></i>
      </th>
      <th class="whs-table__cell--sortable" data-sort="make">
        <span class="whs-table__sort-label">Make / Model</span>
        <i class="icon-base ti ti-selector"></i>
      </th>
      <th>Branch</th>
      <th>Assigned To</th>
      <th>Status</th>
      <th>Next Service</th>
      <th class="whs-table__cell--actions">Actions</th>
    </tr>
  </x-slot>

  {{-- Table Body --}}
  <x-slot name="body">
    @forelse($vehicles['data'] as $vehicle)
      <x-whs.table-row :id="$vehicle->id">
        {{-- Checkbox --}}
        <x-whs.table-cell type="checkbox">
          <input type="checkbox" class="whs-checkbox whs-row-checkbox" value="{{ $vehicle->id }}" aria-label="Select {{ $vehicle->registration_number }}">
        </x-whs.table-cell>

        {{-- Registration Number --}}
        <x-whs.table-cell>
          <span class="whs-chip whs-chip--id">{{ $vehicle->registration_number }}</span>
          @if($vehicle->registration_state)
            <p class="whs-table__secondary-text">{{ $vehicle->registration_state }}</p>
          @endif
        </x-whs.table-cell>

        {{-- Make / Model --}}
        <x-whs.table-cell>
          <strong class="whs-table__primary-text">{{ $vehicle->make }} {{ $vehicle->model }}</strong>
          @if($vehicle->year)
            <p class="whs-table__secondary-text">{{ $vehicle->year }}</p>
          @endif
        </x-whs.table-cell>

        {{-- Branch --}}
        <x-whs.table-cell>
          {{ $vehicle->branch->name ?? 'N/A' }}
        </x-whs.table-cell>

        {{-- Assigned To --}}
        <x-whs.table-cell>
          @if($vehicle->currentAssignment && $vehicle->currentAssignment->user)
            <div class="d-flex align-items-center gap-2">
              <span>{{ $vehicle->currentAssignment->user->name }}</span>
              <button type="button" class="whs-action-btn whs-action-btn--icon whs-action-btn--xs"
                      data-quick-view
                      data-member-id="{{ $vehicle->currentAssignment->user->id }}"
                      aria-label="Quick view {{ $vehicle->currentAssignment->user->name }}">
                <i class="icon-base ti ti-eye"></i>
              </button>
            </div>
          @else
            <span class="whs-table__empty-text">Available</span>
          @endif
        </x-whs.table-cell>

        {{-- Status --}}
        <x-whs.table-cell>
          @php
            $statusClass = match($vehicle->status) {
              'active' => 'success',
              'maintenance' => 'warning',
              'inactive' => 'secondary',
              'sold' => 'info',
              default => 'secondary'
            };
          @endphp
          <span class="whs-chip whs-chip--status whs-chip--status-{{ $statusClass }}">
            {{ ucfirst($vehicle->status) }}
          </span>
          @if($vehicle->isInspectionDue())
            <p class="whs-table__secondary-text text-danger">
              <i class="icon-base ti ti-alert-triangle"></i>
              Inspection due
            </p>
          @endif
        </x-whs.table-cell>

        {{-- Next Service --}}
        <x-whs.table-cell>
          @if($vehicle->next_service_date)
            <time datetime="{{ $vehicle->next_service_date->toIso8601String() }}">
              {{ $vehicle->next_service_date->format('d/m/Y') }}
            </time>
            @php
              $isOverdue = $vehicle->next_service_date->isPast();
              $isDueSoon = !$isOverdue && $vehicle->next_service_date->diffInDays(now()) <= 7;
            @endphp
            @if($isOverdue)
              <p class="whs-table__secondary-text text-danger">
                <i class="icon-base ti ti-alert-circle"></i>
                Overdue
              </p>
            @elseif($isDueSoon)
              <p class="whs-table__secondary-text text-warning">
                <i class="icon-base ti ti-clock"></i>
                Due soon
              </p>
            @else
              <p class="whs-table__secondary-text">{{ $vehicle->next_service_date->diffForHumans() }}</p>
            @endif
          @else
            <span class="whs-table__empty-text">Not scheduled</span>
          @endif
        </x-whs.table-cell>

        {{-- Actions --}}
        <x-whs.table-cell type="actions">
          <div class="whs-table__actions">
            <a href="{{ route('vehicles.show', $vehicle) }}"
               class="whs-action-btn whs-action-btn--icon"
               aria-label="View vehicle {{ $vehicle->registration_number }}">
              <i class="icon-base ti ti-eye"></i>
              <span>View</span>
            </a>
            @can('vehicle.edit')
              <a href="{{ route('vehicles.edit', $vehicle) }}"
                 class="whs-action-btn whs-action-btn--icon"
                 aria-label="Edit vehicle {{ $vehicle->registration_number }}">
                <i class="icon-base ti ti-pencil"></i>
                <span>Edit</span>
              </a>
            @endcan
            <button type="button" class="whs-action-btn whs-action-btn--icon" data-action-menu="{{ $vehicle->id }}" aria-label="More actions">
              <i class="icon-base ti ti-dots-vertical"></i>
            </button>
          </div>
        </x-whs.table-cell>
      </x-whs.table-row>
    @empty
      <tr>
        <td colspan="8" class="whs-table__empty">
          <div class="whs-empty-state">
            <i class="icon-base ti ti-car-off"></i>
            <p class="whs-empty-state__title">No vehicles found</p>
            <p class="whs-empty-state__description">Try adjusting your search or filters, or add your first vehicle to the fleet.</p>
            <a href="{{ route('vehicles.create') }}" class="whs-action-btn whs-action-btn--primary mt-3">
              <i class="icon-base ti ti-plus me-2"></i>
              Add your first vehicle
            </a>
          </div>
        </td>
      </tr>
    @endforelse
  </x-slot>

  {{-- Pagination --}}
  <x-slot name="footer">
    @if(!empty($vehicles['data']))
      <div class="whs-table__pagination">
        <div class="whs-table__pagination-info">
          Showing {{ (($vehicles['current_page'] - 1) * $vehicles['per_page']) + 1 }} - {{ min($vehicles['current_page'] * $vehicles['per_page'], $vehicles['total']) }} of {{ $vehicles['total'] }}
        </div>
        <div class="whs-table__pagination-controls">
          @if($vehicles['current_page'] > 1)
            <a href="{{ route('vehicles.index', array_merge(request()->except('page'), ['page' => $vehicles['current_page'] - 1])) }}" class="whs-pagination-link">
              <i class="ti ti-chevron-left"></i>
              Previous
            </a>
          @endif

          <span class="whs-pagination-current">Page {{ $vehicles['current_page'] }} of {{ $vehicles['last_page'] }}</span>

          @if($vehicles['current_page'] < $vehicles['last_page'])
            <a href="{{ route('vehicles.index', array_merge(request()->except('page'), ['page' => $vehicles['current_page'] + 1])) }}" class="whs-pagination-link">
              Next
              <i class="ti ti-chevron-right"></i>
            </a>
          @endif
        </div>
      </div>
    @endif
  </x-slot>
</x-whs.table>
