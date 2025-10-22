@props([
    'items' => [],
    'class' => '',
])

@php
    $compiledItems = collect($items)->map(function ($item) {
        if (is_string($item)) {
            return ['label' => $item, 'url' => null];
        }

        return [
            'label' => $item['label'] ?? $item['title'] ?? '',
            'url' => $item['url'] ?? null,
        ];
    })->filter(fn ($item) => filled($item['label']));
@endphp

<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => trim('whs-breadcrumb ' . $class)]) }}>
  <ol>
    @foreach ($compiledItems as $item)
      <li>
        @if ($item['url'])
          <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
        @else
          <span aria-current="page">{{ $item['label'] }}</span>
        @endif
      </li>
    @endforeach
  </ol>
</nav>
