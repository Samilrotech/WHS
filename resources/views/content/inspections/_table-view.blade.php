{{-- Dense Table View for Inspection Management --}}
{{-- Reference: Team Management dense table pattern --}}

{{-- Table Toolbar --}}
<x-whs.table-toolbar
  searchPlaceholder="Search inspections..."
  :showViewToggle="false"
  :showExport="true"
>
  {{-- Search Input --}}
  <x-slot name="search">
    <input
      type="text"
      class="whs-search-input"
      placeholder="Search by inspection number or vehicle registration..."
      value="{{ request('search') }}"
      name="search"
      aria-label="Search inspections"
    />
  </x-slot>

  {{-- Filter Pills --}}
  <x-slot name="filters">
    @if(request('status') && request('status') !== 'all')
      <span class="whs-filter-pill">
        Status: {{ ucfirst(str_replace('_', ' ', request('status'))) }}
        <button type="button" class="whs-filter-pill__remove" data-filter="status" aria-label="Remove status filter">×</button>
      </span>
    @endif
    @if(request('type') && request('type') !== 'all')
      <span class="whs-filter-pill">
        Type: {{ ucfirst(str_replace('_', ' ', request('type'))) }}
        <button type="button" class="whs-filter-pill__remove" data-filter="type" aria-label="Remove type filter">×</button>
      </span>
    @endif
    @if(request('result') && request('result') !== 'all')
      <span class="whs-filter-pill">
        Result: {{ ucfirst(str_replace('_', ' ', request('result'))) }}
        <button type="button" class="whs-filter-pill__remove" data-filter="result" aria-label="Remove result filter">×</button>
      </span>
    @endif
  </x-slot>

  {{-- Bulk Actions --}}
  <x-slot name="bulkActions">
    <button type="button" class="whs-action-btn whs-action-btn--icon" data-bulk-action="export" title="Export selected inspections">
      <i class="icon-base ti ti-download"></i>
      <span>Export</span>
    </button>
    <button type="button" class="whs-action-btn whs-action-btn--icon" data-bulk-action="print" title="Print selected reports">
      <i class="icon-base ti ti-printer"></i>
      <span>Print</span>
    </button>
  </x-slot>

  {{-- Primary Action --}}
  <x-slot name="actions">
    <a href="{{ route('inspections.create') }}" class="whs-action-btn whs-action-btn--primary">
      <i class="icon-base ti ti-plus"></i>
      <span>New Inspection</span>
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
        <input type="checkbox" class="whs-checkbox" id="selectAll" aria-label="Select all inspections">
      </th>
      <th class="whs-table__cell--sortable" data-sort="inspection_number">
        <span class="whs-table__sort-label">Number</span>
        <i class="icon-base ti ti-selector"></i>
      </th>
      <th class="whs-table__cell--sortable" data-sort="vehicle">
        <span class="whs-table__sort-label">Vehicle</span>
        <i class="icon-base ti ti-selector"></i>
      </th>
      <th>Inspector</th>
      <th>Type</th>
      <th class="whs-table__cell--sortable" data-sort="inspection_date">
        <span class="whs-table__sort-label">Date</span>
        <i class="icon-base ti ti-selector"></i>
      </th>
      <th>Result</th>
      <th>Defects</th>
      <th>Status</th>
      <th class="whs-table__cell--actions">Actions</th>
    </tr>
  </x-slot>

  {{-- Table Body --}}
  <x-slot name="body">
    @forelse($inspections as $inspection)
      @php
        $statusLabel = match($inspection->status) {
          'pending' => 'Pending',
          'in_progress' => 'In Progress',
          'completed' => 'Completed',
          'approved' => 'Approved',
          'rejected' => 'Rejected',
          'failed' => 'Failed',
          default => ucfirst($inspection->status)
        };
        $typeLabel = match($inspection->inspection_type) {
          'monthly_routine' => 'Monthly',
          'pre_trip' => 'Pre-Trip',
          'post_incident' => 'Post-Incident',
          'annual_compliance' => 'Annual',
          'maintenance_followup' => 'Maintenance',
          'random_spot_check' => 'Spot Check',
          default => ucfirst($inspection->inspection_type)
        };
        $resultLabel = match($inspection->overall_result) {
          'pass' => 'Pass',
          'pass_minor' => 'Pass (Minor)',
          'fail_major' => 'Fail (Major)',
          'fail_critical' => 'Fail (Critical)',
          default => '-'
        };
      @endphp

      <x-whs.table-row :id="$inspection->id">
        {{-- Checkbox --}}
        <x-whs.table-cell type="checkbox">
          <input type="checkbox" class="whs-checkbox whs-row-checkbox" value="{{ $inspection->id }}" aria-label="Select inspection {{ $inspection->inspection_number }}">
        </x-whs.table-cell>

        {{-- Inspection Number --}}
        <x-whs.table-cell>
          <span class="whs-chip whs-chip--id">{{ $inspection->inspection_number }}</span>
        </x-whs.table-cell>

        {{-- Vehicle --}}
        <x-whs.table-cell>
          @if($inspection->vehicle)
            <strong class="whs-table__primary-text">{{ $inspection->vehicle->registration_number }}</strong>
            <p class="whs-table__secondary-text">{{ $inspection->vehicle->make ?? '' }} {{ $inspection->vehicle->model ?? '' }}</p>
          @else
            <span class="whs-table__empty-text">Vehicle archived</span>
          @endif
        </x-whs.table-cell>

        {{-- Inspector --}}
        <x-whs.table-cell>
          @if($inspection->inspector)
            <div class="d-flex align-items-center gap-2">
              <span>{{ $inspection->inspector->name }}</span>
              <button type="button" class="whs-action-btn whs-action-btn--icon whs-action-btn--xs"
                      data-quick-view
                      data-member-id="{{ $inspection->inspector->id }}"
                      aria-label="Quick view {{ $inspection->inspector->name }}">
                <i class="icon-base ti ti-eye"></i>
              </button>
            </div>
          @else
            <span class="whs-table__empty-text">Not assigned</span>
          @endif
        </x-whs.table-cell>

        {{-- Type --}}
        <x-whs.table-cell>
          <span class="whs-chip whs-chip--type">{{ $typeLabel }}</span>
        </x-whs.table-cell>

        {{-- Date --}}
        <x-whs.table-cell>
          @if($inspection->inspection_date)
            <time datetime="{{ $inspection->inspection_date->toIso8601String() }}">
              {{ $inspection->inspection_date->format('d/m/Y') }}
            </time>
            <p class="whs-table__secondary-text">{{ $inspection->inspection_date->diffForHumans() }}</p>
          @else
            <span class="whs-table__empty-text">Not started</span>
          @endif
        </x-whs.table-cell>

        {{-- Result --}}
        <x-whs.table-cell>
          @if($inspection->overall_result === 'pass')
            <span class="whs-chip whs-chip--severity whs-chip--severity-low">{{ $resultLabel }}</span>
          @elseif($inspection->overall_result === 'pass_minor')
            <span class="whs-chip whs-chip--severity whs-chip--severity-medium">{{ $resultLabel }}</span>
          @elseif(in_array($inspection->overall_result, ['fail_major', 'fail_critical']))
            <span class="whs-chip whs-chip--severity whs-chip--severity-critical">{{ $resultLabel }}</span>
          @else
            <span class="whs-table__empty-text">-</span>
          @endif
        </x-whs.table-cell>

        {{-- Defects --}}
        <x-whs.table-cell>
          @if($inspection->critical_defects > 0)
            <span class="whs-chip whs-chip--severity whs-chip--severity-critical">{{ $inspection->critical_defects }} Critical</span>
          @elseif($inspection->major_defects > 0)
            <span class="whs-chip whs-chip--severity whs-chip--severity-high">{{ $inspection->major_defects }} Major</span>
          @elseif($inspection->minor_defects > 0)
            <span class="whs-chip whs-chip--severity whs-chip--severity-medium">{{ $inspection->minor_defects }} Minor</span>
          @else
            <span class="whs-chip whs-chip--severity whs-chip--severity-low">None</span>
          @endif
        </x-whs.table-cell>

        {{-- Status --}}
        <x-whs.table-cell>
          @php
            $statusClass = match($inspection->status) {
              'pending' => 'warning',
              'in_progress' => 'info',
              'completed' => 'success',
              'approved' => 'success',
              'rejected' => 'danger',
              'failed' => 'danger',
              default => 'secondary'
            };
          @endphp
          <span class="whs-chip whs-chip--status whs-chip--status-{{ $statusClass }}">
            {{ $statusLabel }}
          </span>
        </x-whs.table-cell>

        {{-- Actions --}}
        <x-whs.table-cell type="actions">
          <div class="whs-table__actions">
            <a href="{{ route('inspections.show', $inspection) }}"
               class="whs-action-btn whs-action-btn--icon"
               aria-label="View inspection {{ $inspection->inspection_number }}">
              <i class="icon-base ti ti-eye"></i>
              <span>View</span>
            </a>
            @if(in_array($inspection->status, ['pending', 'in_progress']))
              <a href="{{ route('inspections.edit', $inspection) }}"
                 class="whs-action-btn whs-action-btn--icon"
                 aria-label="Edit inspection {{ $inspection->inspection_number }}">
                <i class="icon-base ti ti-pencil"></i>
                <span>Edit</span>
              </a>
            @endif
            <button type="button" class="whs-action-btn whs-action-btn--icon" data-action-menu="{{ $inspection->id }}" aria-label="More actions">
              <i class="icon-base ti ti-dots-vertical"></i>
            </button>
          </div>
        </x-whs.table-cell>
      </x-whs.table-row>
    @empty
      <tr>
        <td colspan="10" class="whs-table__empty">
          <div class="whs-empty-state">
            <i class="icon-base ti ti-clipboard-off"></i>
            <p class="whs-empty-state__title">No inspections found</p>
            <p class="whs-empty-state__description">Try adjusting your search or filters, or create your first inspection.</p>
            <a href="{{ route('inspections.create') }}" class="whs-action-btn whs-action-btn--primary mt-3">
              <i class="icon-base ti ti-plus me-2"></i>
              Create first inspection
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
        Showing {{ $inspections->firstItem() ?? 0 }} - {{ $inspections->lastItem() ?? 0 }} of {{ $inspections->total() }}
      </div>
      <div class="whs-table__pagination-controls">
        {{ $inspections->links() }}
      </div>
    </div>
  </x-slot>
</x-whs.table>
