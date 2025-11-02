{{-- Dense Table View for Risk Assessment --}}
{{-- Reference: Team Management dense table pattern --}}

{{-- Table Toolbar --}}
<x-whs.table-toolbar
  searchPlaceholder="Search risk assessments..."
  :showViewToggle="false"
  :showExport="true"
>
  {{-- Search Input --}}
  <x-slot name="search">
    <input
      type="text"
      class="whs-search-input"
      placeholder="Search by task description, category, or location..."
      value="{{ request('search') }}"
      name="search"
      aria-label="Search risk assessments"
    />
  </x-slot>

  {{-- Filter Pills --}}
  <x-slot name="filters">
    @if(request('category'))
      <span class="whs-filter-pill">
        Category: {{ ucwords(str_replace('-', ' ', request('category'))) }}
        <button type="button" class="whs-filter-pill__remove" data-filter="category" aria-label="Remove category filter">×</button>
      </span>
    @endif
    @if(request('status'))
      <span class="whs-filter-pill">
        Status: {{ ucfirst(request('status')) }}
        <button type="button" class="whs-filter-pill__remove" data-filter="status" aria-label="Remove status filter">×</button>
      </span>
    @endif
    @if(request('risk_level'))
      <span class="whs-filter-pill">
        Risk Level: {{ ucfirst(request('risk_level')) }}
        <button type="button" class="whs-filter-pill__remove" data-filter="risk_level" aria-label="Remove risk level filter">×</button>
      </span>
    @endif
  </x-slot>

  {{-- Bulk Actions --}}
  <x-slot name="bulkActions">
    <button type="button" class="whs-action-btn whs-action-btn--icon" data-bulk-action="export" title="Export selected assessments">
      <i class="icon-base ti ti-download"></i>
      <span>Export</span>
    </button>
    <button type="button" class="whs-action-btn whs-action-btn--icon" data-bulk-action="print-matrix" title="Print risk matrix">
      <i class="icon-base ti ti-printer"></i>
      <span>Print Matrix</span>
    </button>
  </x-slot>

  {{-- Primary Action --}}
  <x-slot name="actions">
    <a href="{{ route('risk.create') }}" class="whs-action-btn whs-action-btn--primary">
      <i class="icon-base ti ti-plus"></i>
      <span>Create Assessment</span>
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
        <input type="checkbox" class="whs-checkbox" id="selectAll" aria-label="Select all risk assessments">
      </th>
      <th class="whs-table__cell--sortable" data-sort="id">
        <span class="whs-table__sort-label">ID</span>
        <i class="icon-base ti ti-selector"></i>
      </th>
      <th class="whs-table__cell--sortable" data-sort="task_description">
        <span class="whs-table__sort-label">Description</span>
        <i class="icon-base ti ti-selector"></i>
      </th>
      <th>Risk Level</th>
      <th>Likelihood</th>
      <th>Impact</th>
      <th>Risk Owner</th>
      <th>Branch</th>
      <th>Status</th>
      <th class="whs-table__cell--actions">Actions</th>
    </tr>
  </x-slot>

  {{-- Table Body --}}
  <x-slot name="body">
    @forelse($risks as $risk)
      @php
        $riskScore = $risk->initial_risk_score;
        $severity = $riskScore >= 20 ? 'critical' : ($riskScore >= 12 ? 'high' : ($riskScore >= 6 ? 'medium' : 'low'));
        $severityLabel = $riskScore >= 20 ? 'Critical' : ($riskScore >= 12 ? 'High' : ($riskScore >= 6 ? 'Medium' : 'Low'));
      @endphp

      <x-whs.table-row :id="$risk->id">
        {{-- Checkbox --}}
        <x-whs.table-cell type="checkbox">
          <input type="checkbox" class="whs-checkbox whs-row-checkbox" value="{{ $risk->id }}" aria-label="Select risk #{{ $risk->id }}">
        </x-whs.table-cell>

        {{-- Risk ID --}}
        <x-whs.table-cell>
          <span class="whs-chip whs-chip--id">#{{ $risk->id }}</span>
        </x-whs.table-cell>

        {{-- Description --}}
        <x-whs.table-cell>
          <strong class="whs-table__primary-text">{{ $risk->task_description }}</strong>
          <p class="whs-table__secondary-text">
            {{ ucwords(str_replace('-', ' ', $risk->category)) }}
            @if($risk->location)
              • {{ $risk->location }}
            @endif
          </p>
        </x-whs.table-cell>

        {{-- Risk Level --}}
        <x-whs.table-cell>
          <div class="d-flex align-items-center gap-2">
            <span class="whs-chip whs-chip--severity whs-chip--severity-{{ $severity }}">
              {{ $severityLabel }}
            </span>
            <span class="fw-bold" style="color: var(--sensei-text-primary);">{{ $riskScore }}</span>
          </div>
        </x-whs.table-cell>

        {{-- Likelihood --}}
        <x-whs.table-cell>
          <div class="text-center">
            <strong style="font-size: 1.25rem; color: var(--sensei-text-primary);">{{ $risk->initial_likelihood }}</strong>
            <p class="whs-table__secondary-text mb-0">/ 5</p>
          </div>
        </x-whs.table-cell>

        {{-- Impact (Consequence) --}}
        <x-whs.table-cell>
          <div class="text-center">
            <strong style="font-size: 1.25rem; color: var(--sensei-text-primary);">{{ $risk->initial_consequence }}</strong>
            <p class="whs-table__secondary-text mb-0">/ 5</p>
          </div>
        </x-whs.table-cell>

        {{-- Risk Owner --}}
        <x-whs.table-cell>
          @if($risk->user)
            <div class="d-flex align-items-center gap-2">
              <span>{{ $risk->user->name }}</span>
              <button type="button" class="whs-action-btn whs-action-btn--icon whs-action-btn--xs"
                      data-quick-view
                      data-member-id="{{ $risk->user->id }}"
                      aria-label="Quick view {{ $risk->user->name }}">
                <i class="icon-base ti ti-eye"></i>
              </button>
            </div>
          @else
            <span class="whs-table__empty-text">Not assigned</span>
          @endif
        </x-whs.table-cell>

        {{-- Branch --}}
        <x-whs.table-cell>
          {{ $risk->branch->name ?? 'N/A' }}
        </x-whs.table-cell>

        {{-- Status --}}
        <x-whs.table-cell>
          @php
            $statusClass = match($risk->status ?? 'pending') {
              'pending' => 'warning',
              'approved' => 'success',
              'rejected' => 'danger',
              'under_review' => 'info',
              'active' => 'success',
              default => 'secondary'
            };
          @endphp
          <span class="whs-chip whs-chip--status whs-chip--status-{{ $statusClass }}">
            {{ ucfirst(str_replace('_', ' ', $risk->status ?? 'Pending')) }}
          </span>
        </x-whs.table-cell>

        {{-- Actions --}}
        <x-whs.table-cell type="actions">
          <div class="whs-table__actions">
            <a href="{{ route('risk.show', $risk) }}"
               class="whs-action-btn whs-action-btn--icon"
               aria-label="View risk assessment #{{ $risk->id }}">
              <i class="icon-base ti ti-eye"></i>
              <span>View</span>
            </a>
            <a href="{{ route('risk.edit', $risk) }}"
               class="whs-action-btn whs-action-btn--icon"
               aria-label="Edit risk assessment #{{ $risk->id }}">
              <i class="icon-base ti ti-pencil"></i>
              <span>Edit</span>
            </a>
            <button type="button" class="whs-action-btn whs-action-btn--icon" data-action-menu="{{ $risk->id }}" aria-label="More actions">
              <i class="icon-base ti ti-dots-vertical"></i>
            </button>
          </div>
        </x-whs.table-cell>
      </x-whs.table-row>
    @empty
      <tr>
        <td colspan="10" class="whs-table__empty">
          <div class="whs-empty-state">
            <i class="icon-base ti ti-shield-check"></i>
            <p class="whs-empty-state__title">No risk assessments yet</p>
            <p class="whs-empty-state__description">Start building your risk register by creating your first 5×5 matrix assessment.</p>
            <a href="{{ route('risk.create') }}" class="whs-action-btn whs-action-btn--primary mt-3">
              <i class="icon-base ti ti-plus me-2"></i>
              Create first assessment
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
        Showing {{ $risks->firstItem() ?? 0 }} - {{ $risks->lastItem() ?? 0 }} of {{ $risks->total() }}
      </div>
      <div class="whs-table__pagination-controls">
        {{ $risks->links() }}
      </div>
    </div>
  </x-slot>
</x-whs.table>
