@props([
    'selectable' => false,
    'selected' => false,
    'rowId' => null,
    'severity' => 'normal', // normal|info|warning|danger|success
    'clickable' => false,
    'href' => null,
])

@php
$rowClass = [
    'whs-table__row',
    $selectable ? 'whs-table__row--selectable' : '',
    $selected ? 'whs-table__row--selected' : '',
    $clickable || $href ? 'whs-table__row--clickable' : '',
    $severity !== 'normal' ? "whs-table__row--{$severity}" : '',
];
@endphp

<tr
  {{ $attributes->merge(['class' => implode(' ', array_filter($rowClass))]) }}
  @if($rowId) data-row-id="{{ $rowId }}" @endif
  @if($clickable && $href) onclick="window.location.href='{{ $href }}'" style="cursor: pointer;" @endif
  role="row"
>
  @if($selectable)
  <td class="whs-table__cell whs-table__cell--checkbox" data-cell-type="checkbox">
    <div class="form-check">
      <input
        type="checkbox"
        class="form-check-input whs-row-select"
        @if($rowId) value="{{ $rowId }}" @endif
        {{ $selected ? 'checked' : '' }}
        aria-label="Select row"
      >
    </div>
  </td>
  @endif

  {{ $slot }}
</tr>

@once
@push('page-style')
<style>
.whs-table__row {
  transition: background-color 0.15s ease;
}

.whs-table__row--selectable:hover {
  background-color: rgba(90, 139, 255, 0.04);
}

.whs-table__row--selected {
  background-color: rgba(90, 139, 255, 0.08);
}

.whs-table__row--clickable {
  cursor: pointer;
}

.whs-table__row--clickable:hover {
  background-color: rgba(90, 139, 255, 0.06);
}

.whs-table__row--info {
  border-left: 3px solid var(--sensei-info);
}

.whs-table__row--warning {
  border-left: 3px solid var(--sensei-warning);
}

.whs-table__row--danger {
  border-left: 3px solid var(--sensei-danger);
}

.whs-table__row--success {
  border-left: 3px solid var(--sensei-success);
}

.whs-table__cell--checkbox {
  width: 48px;
  padding: 0.5rem 0.75rem;
  vertical-align: middle;
}

.whs-table__cell--checkbox .form-check {
  margin: 0;
  min-height: auto;
}

.whs-row-select {
  cursor: pointer;
}

/* Light Theme */
[data-bs-theme='light'] .whs-table__row--selectable:hover {
  background-color: rgba(59, 130, 246, 0.04);
}

[data-bs-theme='light'] .whs-table__row--selected {
  background-color: rgba(59, 130, 246, 0.08);
}

[data-bs-theme='light'] .whs-table__row--clickable:hover {
  background-color: rgba(59, 130, 246, 0.06);
}
</style>
@endpush
@endonce
