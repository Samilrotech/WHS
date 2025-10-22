@props([
    'name',
    'label' => '',
    'id' => null,
    'rows' => 3,
    'required' => false,
    'help' => null,
    'placeholder' => null,
    'value' => null,
    'error' => null,
])

@php
    $textareaId = $id ?? $name;
    $hasError = filled($error);
    $textareaValue = $value ?? old($name);
@endphp

<div class="whs-field">
  @if($label)
    <label for="{{ $textareaId }}" class="whs-field__label">
      {{ $label }}
      @if($required)<span class="whs-field__required">*</span>@endif
    </label>
  @endif

  <textarea
    id="{{ $textareaId }}"
    name="{{ $name }}"
    rows="{{ $rows }}"
    @if($placeholder) placeholder="{{ $placeholder }}" @endif
    @if($required) required @endif
    {{ $attributes->merge(['class' => 'whs-field__control whs-field__control--textarea' . ($hasError ? ' has-error' : '')]) }}
  >{{ trim($textareaValue) }}</textarea>

  @if($help && !$hasError)
    <p class="whs-field__help">{{ $help }}</p>
  @endif

  @if($hasError)
    <p class="whs-field__error">{{ $error }}</p>
  @endif
</div>
