@props([
    'name',
    'label' => '',
    'description' => null,
    'id' => null,
    'checked' => false,
    'help' => null,
    'error' => null,
])

@php
    $toggleId = $id ?? $name;
    $hasError = filled($error);
    $oldValue = old($name, null);
    $isChecked = !is_null($oldValue) ? filter_var($oldValue, FILTER_VALIDATE_BOOLEAN) : (bool) $checked;
@endphp

<div class="whs-toggle-field">
  <label class="whs-switch">
    <input type="hidden" name="{{ $name }}" value="0">
    <input
      type="checkbox"
      id="{{ $toggleId }}"
      name="{{ $name }}"
      value="1"
      {{ $isChecked ? 'checked' : '' }}
      {{ $attributes }}
    >
    <span class="whs-switch__track"></span>
  </label>

  <div class="whs-switch__meta">
    @if($label)
      <label for="{{ $toggleId }}" class="whs-switch__label">{{ $label }}</label>
    @endif
    @if($description)
      <p class="whs-switch__description">{{ $description }}</p>
    @elseif($help)
      <p class="whs-switch__description">{{ $help }}</p>
    @endif
    @if($hasError)
      <p class="whs-field__error">{{ $error }}</p>
    @endif
  </div>
</div>
