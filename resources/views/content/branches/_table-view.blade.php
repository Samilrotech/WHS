{{-- Dense Table View for Branch Management --}}
{{-- Reference: Team Management dense table pattern --}}

{{-- Table Toolbar --}}
<x-whs.table-toolbar
  searchPlaceholder="Search branches..."
  :showViewToggle="false"
  :showExport="true"
>
  {{-- Search Input --}}
  <x-slot name="search">
    <input
      type="text"
      class="whs-search-input"
      placeholder="Search by name, code, city, or location..."
      value="{{ request('q') }}"
      name="q"
      aria-label="Search branches"
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
  </x-slot>

  {{-- Bulk Actions --}}
  <x-slot name="bulkActions">
    <button type="button" class="whs-action-btn whs-action-btn--icon" data-bulk-action="export" title="Export selected branches">
      <i class="icon-base ti ti-download"></i>
      <span>Export</span>
    </button>
    <button type="button" class="whs-action-btn whs-action-btn--icon" data-bulk-action="print" title="Print branch directory">
      <i class="icon-base ti ti-printer"></i>
      <span>Print</span>
    </button>
  </x-slot>

  {{-- Primary Action --}}
  <x-slot name="actions">
    <a href="{{ route('branches.create') }}" class="whs-action-btn whs-action-btn--primary">
      <i class="icon-base ti ti-plus"></i>
      <span>Add Branch</span>
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
        <input type="checkbox" class="whs-checkbox" id="selectAll" aria-label="Select all branches">
      </th>
      <th class="whs-table__cell--sortable" data-sort="code">
        <span class="whs-table__sort-label">Code</span>
        <i class="icon-base ti ti-selector"></i>
      </th>
      <th class="whs-table__cell--sortable" data-sort="name">
        <span class="whs-table__sort-label">Branch Name</span>
        <i class="icon-base ti ti-selector"></i>
      </th>
      <th>Location</th>
      <th>Manager</th>
      <th>Employees</th>
      <th>Vehicles</th>
      <th>Status</th>
      <th class="whs-table__cell--actions">Actions</th>
    </tr>
  </x-slot>

  {{-- Table Body --}}
  <x-slot name="body">
    @forelse($branches as $branch)
      @php
        $compliance = $branch->vehicle_compliance ?? ['vehicles' => [], 'inspections' => []];
        $vehicleStats = $compliance['vehicles'] ?? [];
        $inspectionStats = $compliance['inspections'] ?? [];
      @endphp

      <x-whs.table-row :id="$branch->id">
        {{-- Checkbox --}}
        <x-whs.table-cell type="checkbox">
          <input type="checkbox" class="whs-checkbox whs-row-checkbox" value="{{ $branch->id }}" aria-label="Select branch {{ $branch->code }}">
        </x-whs.table-cell>

        {{-- Branch Code --}}
        <x-whs.table-cell>
          <span class="whs-chip whs-chip--id">{{ $branch->code }}</span>
        </x-whs.table-cell>

        {{-- Branch Name --}}
        <x-whs.table-cell>
          <strong class="whs-table__primary-text">{{ $branch->name }}</strong>
          @if($branch->phone)
            <p class="whs-table__secondary-text">
              <i class="icon-base ti ti-phone"></i>
              {{ $branch->phone }}
            </p>
          @endif
        </x-whs.table-cell>

        {{-- Location --}}
        <x-whs.table-cell>
          @if($branch->city && $branch->state)
            <div class="d-flex align-items-start gap-1">
              <i class="icon-base ti ti-map-pin" style="margin-top: 2px;"></i>
              <div>
                <span class="whs-table__primary-text">{{ $branch->city }}, {{ $branch->state }}</span>
                @if($branch->postcode)
                  <p class="whs-table__secondary-text mb-0">{{ $branch->postcode }}</p>
                @endif
              </div>
            </div>
          @else
            <span class="whs-table__empty-text">Not specified</span>
          @endif
        </x-whs.table-cell>

        {{-- Manager --}}
        <x-whs.table-cell>
          @if($branch->manager_name)
            <span>{{ $branch->manager_name }}</span>
          @else
            <span class="whs-table__empty-text">Not assigned</span>
          @endif
        </x-whs.table-cell>

        {{-- Employees --}}
        <x-whs.table-cell>
          <div class="text-center">
            <strong style="font-size: 1.25rem; color: var(--sensei-text-primary);">{{ $branch->users_count }}</strong>
            <p class="whs-table__secondary-text mb-0">employee{{ $branch->users_count !== 1 ? 's' : '' }}</p>
          </div>
        </x-whs.table-cell>

        {{-- Vehicles --}}
        <x-whs.table-cell>
          @if(isset($vehicleStats['total']) && $vehicleStats['total'] > 0)
            <div class="text-center">
              <strong style="font-size: 1.25rem; color: var(--sensei-text-primary);">{{ $vehicleStats['total'] }}</strong>
              <p class="whs-table__secondary-text mb-0">
                @if(isset($vehicleStats['active']))
                  <span class="text-success">{{ $vehicleStats['active'] }} active</span>
                @endif
              </p>
            </div>
          @else
            <span class="whs-table__empty-text">No vehicles</span>
          @endif
        </x-whs.table-cell>

        {{-- Status --}}
        <x-whs.table-cell>
          @if($branch->is_active)
            <span class="whs-chip whs-chip--status whs-chip--status-success">
              <i class="icon-base ti ti-circle-check"></i>
              Active
            </span>
          @else
            <span class="whs-chip whs-chip--status whs-chip--status-secondary">
              <i class="icon-base ti ti-circle-x"></i>
              Inactive
            </span>
          @endif
        </x-whs.table-cell>

        {{-- Actions --}}
        <x-whs.table-cell type="actions">
          <div class="whs-table__actions">
            <a href="{{ route('branches.show', $branch) }}"
               class="whs-action-btn whs-action-btn--icon"
               aria-label="View branch {{ $branch->name }}">
              <i class="icon-base ti ti-eye"></i>
              <span>View</span>
            </a>
            @can('branch.edit')
              <a href="{{ route('branches.edit', $branch) }}"
                 class="whs-action-btn whs-action-btn--icon"
                 aria-label="Edit branch {{ $branch->name }}">
                <i class="icon-base ti ti-pencil"></i>
                <span>Edit</span>
              </a>
            @endcan
            <button type="button" class="whs-action-btn whs-action-btn--icon" data-action-menu="{{ $branch->id }}" aria-label="More actions">
              <i class="icon-base ti ti-dots-vertical"></i>
            </button>
          </div>
        </x-whs.table-cell>
      </x-whs.table-row>
    @empty
      <tr>
        <td colspan="9" class="whs-table__empty">
          <div class="whs-empty-state">
            <i class="icon-base ti ti-building"></i>
            <p class="whs-empty-state__title">No branches found</p>
            <p class="whs-empty-state__description">Get started by creating your first organizational branch location.</p>
            <a href="{{ route('branches.create') }}" class="whs-action-btn whs-action-btn--primary mt-3">
              <i class="icon-base ti ti-plus me-2"></i>
              Create First Branch
            </a>
          </div>
        </td>
      </tr>
    @endforelse
  </x-slot>

  {{-- Pagination --}}
  <x-slot name="footer">
    <div class="whs-table__pagination">
      <div class="whs-table__pagination-info">
        Showing {{ $branches->firstItem() ?? 0 }} - {{ $branches->lastItem() ?? 0 }} of {{ $branches->total() }}
      </div>
      <div class="whs-table__pagination-controls">
        {{ $branches->links() }}
      </div>
    </div>
  </x-slot>
</x-whs.table>
