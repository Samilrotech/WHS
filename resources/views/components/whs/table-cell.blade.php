{{--
  WHS Table Cell Component

  Purpose: Reusable, accessible table cell for team management and other data-heavy interfaces
  Accessibility: WCAG 2.1 AA compliant with semantic HTML and ARIA attributes

  Props:
  - type: string (text|badge|actions|date|numeric|avatar) - Cell display type
  - value: mixed - Cell content value
  - sortable: bool - Whether column is sortable
  - align: string (left|center|right) - Text alignment
  - label: string - Accessible label for screen readers
  - meta: string - Secondary metadata (optional)

  Usage:
  <x-whs.table-cell type="text" value="John Doe" :sortable="true" label="Employee Name" />
  <x-whs.table-cell type="badge" value="active" label="Status" />
  <x-whs.table-cell type="numeric" value="42" align="right" label="Count" />
--}}

@props([
    'type' => 'text',
    'value' => null,
    'sortable' => false,
    'align' => 'left',
    'label' => null,
    'meta' => null,
])

@php
$alignClass = match($align) {
    'center' => 'text-center',
    'right' => 'text-end',
    default => 'text-start',
};

$typeClass = match($type) {
    'numeric' => 'font-monospace',
    'badge' => 'badge-cell',
    'actions' => 'actions-cell',
    default => '',
};

$ariaLabel = $label ?? ($value ? strip_tags($value) : '');
@endphp

<td
    {{ $attributes->merge([
        'class' => "whs-table-cell {$alignClass} {$typeClass}",
        'aria-label' => $ariaLabel,
    ]) }}
    @if($sortable) data-sortable="true" @endif
>
    @if($type === 'text')
        {{-- Text Cell --}}
        <div class="cell-content">
            <span class="cell-value">{{ $value }}</span>
            @if($meta)
                <span class="cell-meta text-muted small">{{ $meta }}</span>
            @endif
        </div>

    @elseif($type === 'badge')
        {{-- Badge Cell (Status, Role, etc.) --}}
        @php
        $badgeVariant = match(strtolower($value ?? '')) {
            'active' => 'success',
            'inactive' => 'secondary',
            'on_leave', 'on leave' => 'warning',
            'admin' => 'danger',
            'manager' => 'primary',
            'employee' => 'info',
            default => 'secondary',
        };
        @endphp
        <span class="badge bg-{{ $badgeVariant }}" role="status" aria-label="{{ $label ?? $value }}">
            {{ ucfirst(str_replace('_', ' ', $value ?? '')) }}
        </span>

    @elseif($type === 'date')
        {{-- Date Cell with ISO 8601 format for accessibility --}}
        @php
        $dateObj = $value instanceof \Carbon\Carbon ? $value : \Carbon\Carbon::parse($value);
        @endphp
        <time datetime="{{ $dateObj->toIso8601String() }}" aria-label="{{ $label ?? 'Date' }}">
            {{ $dateObj->format('M j, Y') }}
            @if($meta)
                <span class="text-muted small d-block">{{ $dateObj->format('g:i A') }}</span>
            @endif
        </time>

    @elseif($type === 'numeric')
        {{-- Numeric Cell with proper formatting --}}
        <span class="numeric-value" aria-label="{{ $label ?? 'Number' }}">
            {{ is_numeric($value) ? number_format($value) : $value }}
        </span>

    @elseif($type === 'avatar')
        {{-- Avatar Cell with initials fallback --}}
        @php
        $initials = $meta ?? strtoupper(substr($value ?? '', 0, 2));
        @endphp
        <div class="d-flex align-items-center gap-2">
            <div class="avatar avatar-sm" aria-hidden="true">
                <span class="avatar-initial rounded bg-label-primary">{{ $initials }}</span>
            </div>
            <span>{{ $value }}</span>
        </div>

    @elseif($type === 'actions')
        {{-- Actions Cell with accessible buttons --}}
        <div class="d-flex gap-2 justify-content-end" role="group" aria-label="Row actions">
            {{ $slot }}
        </div>

    @else
        {{-- Default: Render slot or value --}}
        {{ $slot->isEmpty() ? $value : $slot }}
    @endif
</td>

{{-- CSS for table-cell component --}}
@once
@push('styles')
<style>
.whs-table-cell {
    padding: 0.75rem 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #eee;
}

.whs-table-cell[data-sortable="true"] {
    cursor: pointer;
}

.whs-table-cell .cell-content {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.whs-table-cell .cell-meta {
    font-size: 0.875rem;
    color: #6c757d;
}

.whs-table-cell.badge-cell {
    white-space: nowrap;
}

.whs-table-cell.actions-cell {
    white-space: nowrap;
}

.whs-table-cell time {
    display: block;
}

.whs-table-cell .avatar {
    width: 32px;
    height: 32px;
}

.whs-table-cell .avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    font-weight: 600;
}

/* Accessibility: Focus styles */
.whs-table-cell:focus-within {
    outline: 2px solid #0d6efd;
    outline-offset: -2px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .whs-table-cell {
        padding: 0.5rem 0.75rem;
    }
}
</style>
@endpush
@endonce
