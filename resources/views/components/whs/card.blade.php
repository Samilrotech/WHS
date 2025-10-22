@props([
    'severity' => '', // critical, high, medium, low
])

<article {{ $attributes->merge(['class' => 'whs-card' . ($severity ? ' whs-card--' . $severity : '')]) }}>
  {{ $slot }}
</article>
