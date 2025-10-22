@props([
    'name',
    'label' => '',
    'id' => null,
    'options' => [],
    'placeholder' => null,
    'required' => false,
    'help' => null,
    'value' => null,
    'error' => null,
])

@php
    $selectId = $id ?? $name;
    $hasError = filled($error);
    $selectedValue = $value ?? old($name);
@endphp

<div class="whs-field">
  @if($label)
    <label for="{{ $selectId }}" class="whs-field__label">
      {{ $label }}
      @if($required)<span class="whs-field__required">*</span>@endif
    </label>
  @endif

  <div class="whs-select-wrapper{{ $hasError ? ' has-error' : '' }}">
    <select
      id="{{ $selectId }}"
      name="{{ $name }}"
      @if($required) required @endif
      {{ $attributes->merge(['class' => 'whs-field__control whs-field__control--select']) }}
    >
      @if($placeholder)
        <option value="">{{ $placeholder }}</option>
      @endif
      @foreach($options as $optionValue => $optionLabel)
        <option value="{{ $optionValue }}" {{ (string) $selectedValue === (string) $optionValue ? 'selected' : '' }}>
          {{ $optionLabel }}
        </option>
      @endforeach
    </select>
  </div>

  @if($help && !$hasError)
    <p class="whs-field__help">{{ $help }}</p>
  @endif

  @if($hasError)
    <p class="whs-field__error">{{ $error }}</p>
  @endif
</div>
