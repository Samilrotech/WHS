@props([
    'as' => null,
    'href' => null,
    'type' => 'button',
    'variant' => 'default',
    'icon' => null,
    'disabled' => false,
])

@php
    $component = $as;
    if (is_null($component)) {
        $component = $href ? 'a' : 'button';
    }

    $variantClass = match ($variant) {
        'ghost' => 'whs-action-btn--ghost',
        'danger' => 'whs-action-btn--danger',
        'success' => 'whs-action-btn--success',
        default => '',
    };

    $baseClasses = trim("whs-action-btn {$variantClass}" . ($disabled ? ' is-disabled' : ''));

    $iconMarkup = $icon ? "<i class=\"icon-base ti {$icon}\"></i>" : null;
@endphp

<{{ $component }}
  @if($component === 'a')
    href="{{ $disabled ? '#' : $href }}"
  @else
    type="{{ $type }}"
  @endif
  @if($disabled) aria-disabled="true" tabindex="-1" @endif
  {{ $attributes->merge(['class' => $baseClasses]) }}
>
  @if($iconMarkup){!! $iconMarkup !!}@endif
  <span>{{ $slot }}</span>
</{{ $component }}>
