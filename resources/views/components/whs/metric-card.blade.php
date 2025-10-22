@props([
    'icon' => 'ti-activity',
    'iconVariant' => 'brand', // brand, critical, warning, success
    'label' => '',
    'value' => '0',
    'meta' => '',
    'metaClass' => ''
])

<article class="whs-metric-card">
  <div class="whs-metric-card__icon whs-metric-card__icon--{{ $iconVariant }}">
    <i class="icon-base ti {{ $icon }}"></i>
  </div>
  <span class="whs-metric-card__label">{{ $label }}</span>
  <span class="whs-metric-card__value">{{ $value }}</span>
  @if($meta)
    <span class="whs-metric-card__meta {{ $metaClass }}">{{ $meta }}</span>
  @endif
</article>

