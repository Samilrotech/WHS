{{-- Dense Table View for Incident Management --}}
{{-- Reference: Team Management dense table pattern --}}

{{-- Table Toolbar --}}
<x-whs.table-toolbar
  searchPlaceholder="Search incidents..."
  :showViewToggle="false"
  :showExport="true"
>
  {{-- Search Input --}}
  <x-slot name="search">
    <input
      type="text"
      class="whs-search-input"
      placeholder="Search by incident ID, type, location, or description..."
      value="{{ request('q') }}"
      name="q"
      aria-label="Search incidents"
    />
  </x-slot>

  {{-- Filter Pills --}}
  <x-slot name="filters">
    @if(request('severity'))
      <span class="whs-filter-pill">
        Severity: {{ ucfirst(request('severity')) }}
        <button type="button" class="whs-filter-pill__remove" data-filter="severity" aria-label="Remove severity filter">×</button>
      </span>
    @endif
    @if(request('status'))
      <span class="whs-filter-pill">
        Status: {{ ucfirst(request('status')) }}
        <button type="button" class="whs-filter-pill__remove" data-filter="status" aria-label="Remove status filter">×</button>
      </span>
    @endif
    @if(request('type'))
      <span class="whs-filter-pill">
        Type: {{ ucfirst(request('type')) }}
        <button type="button" class="whs-filter-pill__remove" data-filter="type" aria-label="Remove type filter">×</button>
      </span>
    @endif
  </x-slot>

  {{-- Bulk Actions --}}
  <x-slot name="bulkActions">
    <button type="button" class="whs-action-btn whs-action-btn--icon" data-bulk-action="export" title="Export selected incidents">
      <i class="icon-base ti ti-download"></i>
      <span>Export</span>
    </button>
    @can('delete incidents')
      <button type="button" class="whs-action-btn whs-action-btn--icon whs-action-btn--danger" data-bulk-action="archive" title="Archive selected">
        <i class="icon-base ti ti-archive"></i>
        <span>Archive</span>
      </button>
    @endcan
  </x-slot>

  {{-- Primary Action --}}
  <x-slot name="actions">
    <a href="{{ route('incidents.create') }}" class="whs-action-btn whs-action-btn--primary">
      <i class="icon-base ti ti-plus"></i>
      <span>Report Incident</span>
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
        <input type="checkbox" class="whs-checkbox" id="selectAll" aria-label="Select all incidents">
      </th>
      <th class="whs-table__cell--sortable" data-sort="id">
        <span class="whs-table__sort-label">ID</span>
        <i class="icon-base ti ti-selector"></i>
      </th>
      <th class="whs-table__cell--sortable" data-sort="type">
        <span class="whs-table__sort-label">Type</span>
        <i class="icon-base ti ti-selector"></i>
      </th>
      <th>Severity</th>
      <th>Location</th>
      <th>Reported By</th>
      <th>Branch</th>
      <th class="whs-table__cell--sortable" data-sort="incident_datetime">
        <span class="whs-table__sort-label">Date</span>
        <i class="icon-base ti ti-selector"></i>
      </th>
      <th>Status</th>
      <th class="whs-table__cell--actions">Actions</th>
    </tr>
  </x-slot>

  {{-- Table Body --}}
  <x-slot name="body">
    @forelse($incidents as $incident)
      <x-whs.table-row :id="$incident->id">
        {{-- Checkbox --}}
        <x-whs.table-cell type="checkbox">
          <input type="checkbox" class="whs-checkbox whs-row-checkbox" value="{{ $incident->id }}" aria-label="Select incident #{{ $incident->id }}">
        </x-whs.table-cell>

        {{-- Incident ID --}}
        <x-whs.table-cell>
          <span class="whs-chip whs-chip--id">#{{ $incident->id }}</span>
        </x-whs.table-cell>

        {{-- Type --}}
        <x-whs.table-cell>
          <strong class="whs-table__primary-text">{{ ucfirst($incident->type) }}</strong>
          @if($incident->description)
            <p class="whs-table__secondary-text">{{ Str::limit($incident->description, 60) }}</p>
          @endif
        </x-whs.table-cell>

        {{-- Severity --}}
        <x-whs.table-cell>
          <span class="whs-chip whs-chip--severity whs-chip--severity-{{ strtolower($incident->severity) }}">
            {{ ucfirst($incident->severity) }}
          </span>
        </x-whs.table-cell>

        {{-- Location --}}
        <x-whs.table-cell>
          @if($incident->location_specific)
            <span class="whs-table__primary-text">{{ $incident->location_specific }}</span>
          @else
            <span class="whs-table__empty-text">Not specified</span>
          @endif
        </x-whs.table-cell>

        {{-- Reported By --}}
        <x-whs.table-cell>
          @if($incident->user)
            <div class="d-flex align-items-center gap-2">
              <span>{{ $incident->user->name }}</span>
              <button type="button" class="whs-action-btn whs-action-btn--icon whs-action-btn--xs"
                      data-quick-view
                      data-member-id="{{ $incident->user->id }}"
                      aria-label="Quick view {{ $incident->user->name }}">
                <i class="icon-base ti ti-eye"></i>
              </button>
            </div>
          @else
            <span class="whs-table__empty-text">Unknown</span>
          @endif
        </x-whs.table-cell>

        {{-- Branch --}}
        <x-whs.table-cell>
          {{ $incident->branch->name ?? 'N/A' }}
        </x-whs.table-cell>

        {{-- Date --}}
        <x-whs.table-cell>
          <time datetime="{{ $incident->incident_datetime->toIso8601String() }}">
            {{ $incident->incident_datetime->format('d/m/Y') }}
          </time>
          <p class="whs-table__secondary-text">{{ $incident->incident_datetime->format('H:i') }}</p>
        </x-whs.table-cell>

        {{-- Status --}}
        <x-whs.table-cell>
          <span class="whs-chip whs-chip--status whs-chip--status-{{ strtolower(str_replace(' ', '-', $incident->status)) }}">
            {{ ucfirst($incident->status) }}
          </span>
        </x-whs.table-cell>

        {{-- Actions --}}
        <x-whs.table-cell type="actions">
          <div class="whs-table__actions">
            <a href="{{ route('incidents.show', $incident) }}"
               class="whs-action-btn whs-action-btn--icon"
               aria-label="View incident #{{ $incident->id }}">
              <i class="icon-base ti ti-eye"></i>
              <span>View</span>
            </a>
            @can('edit incidents')
              <a href="{{ route('incidents.edit', $incident) }}"
                 class="whs-action-btn whs-action-btn--icon"
                 aria-label="Edit incident #{{ $incident->id }}">
                <i class="icon-base ti ti-pencil"></i>
                <span>Edit</span>
              </a>
            @endcan
            <button type="button" class="whs-action-btn whs-action-btn--icon" data-action-menu="{{ $incident->id }}" aria-label="More actions">
              <i class="icon-base ti ti-dots-vertical"></i>
            </button>
          </div>
        </x-whs.table-cell>
      </x-whs.table-row>
    @empty
      <tr>
        <td colspan="10" class="whs-table__empty">
          <div class="whs-empty-state">
            <i class="icon-base ti ti-confetti"></i>
            <p class="whs-empty-state__title">All clear!</p>
            <p class="whs-empty-state__description">No incidents have been recorded for this time window. Keep monitoring to stay ahead of risk.</p>
            <a href="{{ route('incidents.create') }}" class="whs-action-btn whs-action-btn--primary mt-3">
              <i class="icon-base ti ti-plus me-2"></i>
              Log your first incident
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
        Showing {{ $incidents->firstItem() ?? 0 }} - {{ $incidents->lastItem() ?? 0 }} of {{ $incidents->total() }}
      </div>
      <div class="whs-table__pagination-controls">
        {{ $incidents->links() }}
      </div>
    </div>
  </x-slot>
</x-whs.table>
