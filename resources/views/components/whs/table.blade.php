@props([
    'density' => 'normal', // compact|normal|comfortable
    'striped' => true,
    'hover' => true,
    'bordered' => false,
    'responsive' => true,
    'stickyHeader' => false,
    'sortable' => false,
    'currentSort' => null,
    'currentDirection' => 'asc',
])

@php
$tableClass = [
    'table',
    'whs-dense-table',
    "whs-dense-table--{$density}",
    $striped ? 'table-striped' : '',
    $hover ? 'table-hover' : '',
    $bordered ? 'table-bordered' : '',
    $sortable ? 'whs-dense-table--sortable' : '',
];

$wrapperClass = [
    $responsive ? 'table-responsive' : '',
    $stickyHeader ? 'whs-table-sticky-header' : '',
];
@endphp

<div {{ $attributes->merge(['class' => implode(' ', array_filter($wrapperClass))]) }} data-table-wrapper>
  <table class="{{ implode(' ', array_filter($tableClass)) }}" data-table data-density="{{ $density }}">
    @isset($header)
      <thead class="whs-dense-table__head">
        {{ $header }}
      </thead>
    @endisset

    @isset($body)
      <tbody class="whs-dense-table__body">
        {{ $body }}
      </tbody>
    @else
      <tbody class="whs-dense-table__body">
        {{ $slot }}
      </tbody>
    @endisset

    @isset($footer)
      <tfoot class="whs-dense-table__foot">
        {{ $footer }}
      </tfoot>
    @endisset
  </table>
</div>

@once
@push('page-style')
<style>
/* Dense Table Core Styles */
.whs-dense-table {
  margin-bottom: 0;
  font-size: 0.875rem;
  color: var(--sensei-text-primary);
}

.whs-dense-table__head {
  background: var(--sensei-surface);
  border-bottom: 2px solid var(--sensei-border);
}

.whs-dense-table__head th {
  font-weight: 600;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--sensei-text-secondary);
  white-space: nowrap;
  vertical-align: middle;
}

.whs-dense-table__body td {
  vertical-align: middle;
  border-color: var(--sensei-border);
}

/* Density Variants */
.whs-dense-table--compact th,
.whs-dense-table--compact td {
  padding: 0.375rem 0.75rem;
  font-size: 0.8125rem;
  line-height: 1.4;
}

.whs-dense-table--normal th,
.whs-dense-table--normal td {
  padding: 0.625rem 1rem;
  font-size: 0.875rem;
  line-height: 1.5;
}

.whs-dense-table--comfortable th,
.whs-dense-table--comfortable td {
  padding: 0.875rem 1.25rem;
  font-size: 0.9375rem;
  line-height: 1.6;
}

/* Sticky Header */
.whs-table-sticky-header {
  max-height: 600px;
  overflow-y: auto;
  position: relative;
}

.whs-table-sticky-header .whs-dense-table__head th {
  position: sticky;
  top: 0;
  z-index: 10;
  background: var(--sensei-surface);
  box-shadow: var(--sensei-shadow-card);
}

/* Sortable Headers */
.whs-dense-table--sortable th[data-sortable] {
  cursor: pointer;
  user-select: none;
  position: relative;
  padding-right: 1.75rem;
  transition: background var(--sensei-transition);
}

.whs-dense-table--sortable th[data-sortable]:hover {
  background: color-mix(in srgb, var(--sensei-accent) 6%, transparent);
}

.whs-dense-table--sortable th[data-sortable]::after {
  content: '\e92d'; /* Tabler icon: selector */
  font-family: 'tabler-icons';
  position: absolute;
  right: 0.5rem;
  top: 50%;
  transform: translateY(-50%);
  opacity: 0.3;
  font-size: 0.875rem;
}

.whs-dense-table--sortable th[data-sortable][data-sort='asc']::after {
  content: '\e930'; /* Tabler icon: sort-ascending */
  opacity: 1;
  color: var(--sensei-accent);
}

.whs-dense-table--sortable th[data-sortable][data-sort='desc']::after {
  content: '\e931'; /* Tabler icon: sort-descending */
  opacity: 1;
  color: var(--sensei-accent);
}

/* Responsive Wrapper */
.table-responsive {
  border-radius: var(--sensei-radius-sm);
  border: 1px solid var(--sensei-border);
  overflow: hidden;
}

/* Striped Rows */
.whs-dense-table.table-striped tbody tr:nth-of-type(odd) {
  background-color: color-mix(in srgb, var(--sensei-accent) 2%, transparent);
}

/* Hover Effect */
.whs-dense-table.table-hover tbody tr:hover {
  background-color: color-mix(in srgb, var(--sensei-accent) 4%, transparent);
  transition: background var(--sensei-transition);
}

/* Empty State */
.whs-dense-table__empty {
  text-align: center;
  padding: 3rem 1.5rem;
}

.whs-dense-table__empty-icon {
  font-size: 3rem;
  color: var(--sensei-text-muted);
  margin-bottom: 1rem;
}

.whs-dense-table__empty-text {
  color: var(--sensei-text-secondary);
  font-size: 0.9375rem;
}

/* Light Theme - Sensei tokens automatically handle theme differences */
[data-bs-theme='light'] .whs-dense-table__head {
  background: var(--sensei-surface-strong);
  border-bottom-color: var(--sensei-border);
}

[data-bs-theme='light'] .whs-dense-table__body td {
  border-color: var(--sensei-border);
}

[data-bs-theme='light'] .whs-dense-table.table-striped tbody tr:nth-of-type(odd) {
  background-color: color-mix(in srgb, var(--sensei-accent) 2%, transparent);
}

[data-bs-theme='light'] .whs-dense-table.table-hover tbody tr:hover {
  background-color: color-mix(in srgb, var(--sensei-accent) 4%, transparent);
}

[data-bs-theme='light'] .whs-table-sticky-header .whs-dense-table__head th {
  background: var(--sensei-surface-strong);
  box-shadow: var(--sensei-shadow-card);
}

[data-bs-theme='light'] .whs-dense-table--sortable th[data-sortable]:hover {
  background: color-mix(in srgb, var(--sensei-accent) 6%, transparent);
}

/* Scrollbar Styling - Theme aware */
.whs-table-sticky-header::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.whs-table-sticky-header::-webkit-scrollbar-track {
  background: color-mix(in srgb, var(--sensei-accent) 5%, transparent);
  border-radius: 4px;
}

.whs-table-sticky-header::-webkit-scrollbar-thumb {
  background: color-mix(in srgb, var(--sensei-accent) 30%, transparent);
  border-radius: 4px;
  transition: background var(--sensei-transition);
}

.whs-table-sticky-header::-webkit-scrollbar-thumb:hover {
  background: color-mix(in srgb, var(--sensei-accent) 50%, transparent);
}

[data-bs-theme='light'] .whs-table-sticky-header::-webkit-scrollbar-track {
  background: color-mix(in srgb, var(--sensei-accent) 5%, transparent);
}

[data-bs-theme='light'] .whs-table-sticky-header::-webkit-scrollbar-thumb {
  background: color-mix(in srgb, var(--sensei-accent) 30%, transparent);
}

[data-bs-theme='light'] .whs-table-sticky-header::-webkit-scrollbar-thumb:hover {
  background: color-mix(in srgb, var(--sensei-accent) 50%, transparent);
}

/* Action Menu Dropdown */
.whs-action-menu {
  position: absolute;
  right: 0;
  top: 100%;
  margin-top: 0.25rem;
  min-width: 160px;
  background: var(--sensei-surface);
  border: 1px solid var(--sensei-border);
  border-radius: var(--sensei-radius-sm);
  box-shadow: var(--sensei-shadow-card);
  z-index: 1000;
  display: flex;
  flex-direction: column;
  padding: 0.5rem;
  gap: 0.25rem;
}

.whs-action-menu__item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 0.75rem;
  color: var(--sensei-text-primary);
  text-decoration: none;
  border-radius: var(--sensei-radius-xs);
  transition: background var(--sensei-transition);
  font-size: 0.875rem;
}

.whs-action-menu__item:hover {
  background: color-mix(in srgb, var(--sensei-accent) 8%, transparent);
  color: var(--sensei-accent);
}

.whs-action-menu__item i {
  font-size: 1rem;
  opacity: 0.7;
}
</style>
@endpush

@push('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Handle sortable columns
  document.querySelectorAll('[data-sortable]').forEach(header => {
    header.addEventListener('click', function() {
      const column = this.dataset.sortable;
      const currentSort = this.dataset.sort;
      const newSort = currentSort === 'asc' ? 'desc' : 'asc';

      // Update URL with sort parameters
      const url = new URL(window.location);
      url.searchParams.set('sort', column);
      url.searchParams.set('direction', newSort);
      window.location.href = url.toString();
    });
  });

  // Handle density toggle
  document.querySelectorAll('[data-density-toggle]').forEach(btn => {
    btn.addEventListener('click', function() {
      const table = document.querySelector('[data-table]');
      if (!table) return;

      const densities = ['compact', 'normal', 'comfortable'];
      const currentDensity = table.dataset.density || 'normal';
      const currentIndex = densities.indexOf(currentDensity);
      const nextDensity = densities[(currentIndex + 1) % densities.length];

      // Remove all density classes
      table.classList.remove(...densities.map(d => `whs-dense-table--${d}`));

      // Add new density class
      table.classList.add(`whs-dense-table--${nextDensity}`);
      table.dataset.density = nextDensity;

      // Store preference in localStorage
      localStorage.setItem('whsDensity', nextDensity);
    });
  });

  // Restore density preference
  const savedDensity = localStorage.getItem('whsDensity');
  if (savedDensity) {
    const table = document.querySelector('[data-table]');
    if (table) {
      table.classList.remove('whs-dense-table--normal', 'whs-dense-table--compact', 'whs-dense-table--comfortable');
      table.classList.add(`whs-dense-table--${savedDensity}`);
      table.dataset.density = savedDensity;
    }
  }

  // Handle action menu (3-dots)
  document.querySelectorAll('[data-action-menu]').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.stopPropagation();
      const currentMenu = this.nextElementSibling;

      // Close all other menus
      document.querySelectorAll('.whs-action-menu').forEach(menu => {
        if (menu !== currentMenu) {
          menu.remove();
        }
      });

      // Toggle current menu
      if (currentMenu && currentMenu.classList.contains('whs-action-menu')) {
        currentMenu.remove();
      } else {
        // Create and show menu
        const menu = document.createElement('div');
        menu.className = 'whs-action-menu';
        menu.innerHTML = `
          <a href="#" class="whs-action-menu__item" onclick="alert('Edit action'); return false;">
            <i class="icon-base ti ti-pencil"></i>
            <span>Edit</span>
          </a>
          <a href="#" class="whs-action-menu__item" onclick="alert('Delete action'); return false;">
            <i class="icon-base ti ti-trash"></i>
            <span>Delete</span>
          </a>
        `;
        this.parentElement.style.position = 'relative';
        this.parentElement.appendChild(menu);
      }
    });
  });

  // Close menus when clicking outside
  document.addEventListener('click', function() {
    document.querySelectorAll('.whs-action-menu').forEach(menu => menu.remove());
  });
});
</script>
@endpush
@endonce
