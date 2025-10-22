@props([
    'name',
    'label' => '',
    'id' => null,
    'type' => 'text',
    'required' => false,
    'help' => null,
    'placeholder' => null,
    'value' => null,
    'error' => null,
    'autocomplete' => null,
])

@php
    $inputId = $id ?? $name;
    $hasError = filled($error);
    $inputValue = $value ?? old($name);
@endphp

<div class="whs-field">
  @if($label)
    <label for="{{ $inputId }}" class="whs-field__label">
      {{ $label }}
      @if($required)<span class="whs-field__required">*</span>@endif
    </label>
  @endif

  <input
    id="{{ $inputId }}"
    name="{{ $name }}"
    type="{{ $type }}"
    value="{{ $inputValue }}"
    @if($placeholder) placeholder="{{ $placeholder }}" @endif
    @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
    @if($required) required @endif
    {{ $attributes->merge(['class' => 'whs-field__control' . ($hasError ? ' has-error' : '')]) }}
  />

  @if($help && !$hasError)
    <p class="whs-field__help">{{ $help }}</p>
  @endif

  @if($hasError)
    <p class="whs-field__error">{{ $error }}</p>
  @endif
</div>
