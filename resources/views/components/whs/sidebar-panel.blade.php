@props([
    'title' => ''
])

<div class="whs-sidebar__panel">
  @if($title)
    <h3>{{ $title }}</h3>
  @endif
  {{ $slot }}
</div>
