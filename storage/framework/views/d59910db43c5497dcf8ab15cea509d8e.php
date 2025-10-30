<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'icon' => 'ti-activity',
    'iconVariant' => 'brand', // brand, critical, warning, success
    'label' => '',
    'value' => '0',
    'meta' => '',
    'metaClass' => ''
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'icon' => 'ti-activity',
    'iconVariant' => 'brand', // brand, critical, warning, success
    'label' => '',
    'value' => '0',
    'meta' => '',
    'metaClass' => ''
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<article class="whs-metric-card">
  <div class="whs-metric-card__icon whs-metric-card__icon--<?php echo e($iconVariant); ?>">
    <i class="icon-base ti <?php echo e($icon); ?>"></i>
  </div>
  <span class="whs-metric-card__label"><?php echo e($label); ?></span>
  <span class="whs-metric-card__value"><?php echo e($value); ?></span>
  <?php if($meta): ?>
    <span class="whs-metric-card__meta <?php echo e($metaClass); ?>"><?php echo e($meta); ?></span>
  <?php endif; ?>
</article>

<?php /**PATH D:\WHS5\resources\views/components/whs/metric-card.blade.php ENDPATH**/ ?>