@props([
    'title' => '',
    'description' => null,
    'columns' => 2,
])

@php
    $gridClass = match((int) $columns) {
        1 => 'whs-form__grid--1',
        3 => 'whs-form__grid--3',
        default => 'whs-form__grid--2',
    };
@endphp

<section {{ $attributes->merge(['class' => 'whs-form__section']) }}>
  @if($title || $description)
    <header class="whs-form__section-header">
      @if($title)
        <h3>{{ $title }}</h3>
      @endif
      @if($description)
        <p>{{ $description }}</p>
      @endif
    </header>
  @endif

  <div class="whs-form__grid {{ $gridClass }}">
    {{ $slot }}
  </div>
</section>
