@props([
    'title' => '',
    'viewMode' => 'cards', // cards|table
    'itemsPerPage' => 50,
    'searchAction' => '',
    'searchPlaceholder' => 'Search...',
    'searchValue' => '',
    'showViewToggle' => true,
    'showDensity' => true,
    'showPerPage' => true,
    'showBulkActions' => false,
    'selectedCount' => 0,
])

<div class="whs-table-toolbar" data-table-toolbar>
  {{-- Title and Description --}}
  @if($title)
  <div class="whs-table-toolbar__header">
    <h2 class="whs-table-toolbar__title">{{ $title }}</h2>
    @if($slot->isNotEmpty())
      <p class="whs-table-toolbar__description">{{ $slot }}</p>
    @endif
  </div>
  @endif

  {{-- Main Toolbar Row --}}
  <div class="whs-table-toolbar__main">
    {{-- Left: Search --}}
    <div class="whs-table-toolbar__search">
      <form method="GET" action="{{ $searchAction }}" class="whs-search-form">
        <div class="input-group">
          <span class="input-group-text">
            <i class="ti ti-search"></i>
          </span>
          <input
            type="search"
            name="search"
            class="form-control whs-search-input"
            placeholder="{{ $searchPlaceholder }}"
            value="{{ $searchValue }}"
            aria-label="{{ $searchPlaceholder }}"
          >
          @if($searchValue)
          <button type="button" class="btn btn-icon btn-outline-secondary" onclick="this.closest('form').querySelector('input[name=search]').value='';this.closest('form').submit();" aria-label="Clear search">
            <i class="ti ti-x"></i>
          </button>
          @endif
        </div>
      </form>
    </div>

    {{-- Right: Controls --}}
    <div class="whs-table-toolbar__controls">
      {{-- View Mode Toggle --}}
      @if($showViewToggle)
      <div class="btn-group whs-view-toggle" role="group" aria-label="View mode">
        <input type="radio" class="btn-check" name="viewMode" id="viewModeCards" value="cards" {{ $viewMode === 'cards' ? 'checked' : '' }} autocomplete="off">
        <label class="btn btn-outline-secondary btn-sm" for="viewModeCards" title="Card view">
          <i class="ti ti-layout-grid"></i>
          <span class="d-none d-md-inline ms-1">Cards</span>
        </label>

        <input type="radio" class="btn-check" name="viewMode" id="viewModeTable" value="table" {{ $viewMode === 'table' ? 'checked' : '' }} autocomplete="off">
        <label class="btn btn-outline-secondary btn-sm" for="viewModeTable" title="Table view">
          <i class="ti ti-table"></i>
          <span class="d-none d-md-inline ms-1">Table</span>
        </label>
      </div>
      @endif

      {{-- Items Per Page --}}
      @if($showPerPage)
      <div class="whs-per-page-selector">
        <select class="form-select form-select-sm" name="per_page" aria-label="Items per page" onchange="this.closest('form')?.submit() || window.location.href = updateQueryParam('per_page', this.value)">
          <option value="25" {{ $itemsPerPage == 25 ? 'selected' : '' }}>25</option>
          <option value="50" {{ $itemsPerPage == 50 ? 'selected' : '' }}>50</option>
          <option value="100" {{ $itemsPerPage == 100 ? 'selected' : '' }}>100</option>
        </select>
      </div>
      @endif

      {{-- Density Toggle --}}
      @if($showDensity)
      <button type="button" class="btn btn-sm btn-icon btn-outline-secondary whs-density-toggle" title="Toggle density" aria-label="Toggle table density" data-density="normal">
        <i class="ti ti-layout-distribute-vertical"></i>
      </button>
      @endif

      {{-- Additional Actions Slot --}}
      @isset($actions)
        {{ $actions }}
      @endisset
    </div>
  </div>

  {{-- Active Filters Row --}}
  @isset($filters)
  <div class="whs-table-toolbar__filters">
    {{ $filters }}
  </div>
  @endisset

  {{-- Bulk Actions Bar (shown when items selected) --}}
  @if($showBulkActions)
  <div class="whs-bulk-actions" data-bulk-actions hidden>
    <div class="whs-bulk-actions__content">
      <span class="whs-bulk-actions__count" data-selected-count>0</span>
      <span class="whs-bulk-actions__label">items selected</span>

      @isset($bulkActions)
        <div class="whs-bulk-actions__buttons">
          {{ $bulkActions }}
        </div>
      @else
        <div class="whs-bulk-actions__buttons">
          <button type="button" class="btn btn-sm btn-primary" data-bulk-action="export">
            <i class="ti ti-download"></i> Export
          </button>
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bulk-action="assign">
            <i class="ti ti-user-plus"></i> Assign
          </button>
          <button type="button" class="btn btn-sm btn-outline-danger" data-bulk-action="delete">
            <i class="ti ti-trash"></i> Delete
          </button>
        </div>
      @endisset

      <button type="button" class="btn btn-sm btn-text" data-bulk-clear>Clear selection</button>
    </div>
  </div>
  @endif
</div>

@push('page-style')
<style>
.whs-table-toolbar {
  background: var(--sensei-surface);
  border: 1px solid var(--sensei-border);
  border-radius: var(--sensei-radius);
  padding: 1.25rem;
  margin-bottom: 1.5rem;
  box-shadow: var(--sensei-shadow-card);
  transition: all var(--sensei-transition);
}

.whs-table-toolbar__header {
  margin-bottom: 1rem;
}

.whs-table-toolbar__title {
  font-size: 1.125rem;
  font-weight: 600;
  margin: 0;
  color: var(--sensei-text-primary);
}

.whs-table-toolbar__description {
  font-size: 0.875rem;
  color: var(--sensei-text-secondary);
  margin: 0.25rem 0 0;
}

.whs-table-toolbar__main {
  display: flex;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
}

.whs-table-toolbar__search {
  flex: 1;
  min-width: 280px;
}

.whs-search-input {
  border-radius: var(--sensei-radius-sm);
}

.whs-table-toolbar__controls {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  flex-wrap: wrap;
}

.whs-view-toggle .btn {
  min-width: 44px;
  height: 38px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.whs-per-page-selector select {
  min-width: 80px;
  border-radius: 8px;
}

.whs-density-toggle {
  min-width: 38px;
  height: 38px;
}

.whs-table-toolbar__filters {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--sensei-border);
}

.whs-bulk-actions {
  margin-top: 1rem;
  padding: 0.875rem 1rem;
  background: var(--sensei-accent-soft);
  border: 1px solid color-mix(in srgb, var(--sensei-accent) 25%, transparent);
  border-radius: var(--sensei-radius-sm);
  transition: all var(--sensei-transition);
}

.whs-bulk-actions__content {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.whs-bulk-actions__count {
  font-weight: 700;
  font-size: 1.125rem;
  color: var(--sensei-accent);
}

.whs-bulk-actions__label {
  font-size: 0.875rem;
  color: var(--sensei-text-secondary);
}

.whs-bulk-actions__buttons {
  margin-left: auto;
  display: flex;
  gap: 0.5rem;
}

/* Light Theme - Already handled by Sensei tokens */
[data-bs-theme='light'] .whs-table-toolbar {
  background: var(--sensei-surface-strong);
  border-color: var(--sensei-border);
}

[data-bs-theme='light'] .whs-bulk-actions {
  background: var(--sensei-accent-soft);
  border-color: color-mix(in srgb, var(--sensei-accent) 25%, transparent);
}

@media (max-width: 768px) {
  .whs-table-toolbar__main {
    flex-direction: column;
    align-items: stretch;
  }

  .whs-table-toolbar__search,
  .whs-table-toolbar__controls {
    width: 100%;
  }

  .whs-table-toolbar__controls {
    justify-content: space-between;
  }
}
</style>
@endpush

@push('page-script')
<script>
function updateQueryParam(key, value) {
  const url = new URL(window.location);
  url.searchParams.set(key, value);
  return url.toString();
}
</script>
@endpush
