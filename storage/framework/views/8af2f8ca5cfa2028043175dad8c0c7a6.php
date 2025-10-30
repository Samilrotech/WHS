<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'eyebrow' => '',
    'title' => '',
    'subtitle' => '',
    'metric' => null,
    'metricLabel' => '',
    'metricValue' => '',
    'metricCaption' => '',
    'searchRoute' => null,
    'searchPlaceholder' => 'Search...',
    'createRoute' => null,
    'createLabel' => 'Create New',
    'filters' => []
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
    'eyebrow' => '',
    'title' => '',
    'subtitle' => '',
    'metric' => null,
    'metricLabel' => '',
    'metricValue' => '',
    'metricCaption' => '',
    'searchRoute' => null,
    'searchPlaceholder' => 'Search...',
    'createRoute' => null,
    'createLabel' => 'Create New',
    'filters' => []
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<header class="whs-hero">
  <div class="whs-hero__main">
    <div>
      <?php if($eyebrow): ?>
        <span class="whs-eyebrow"><?php echo e($eyebrow); ?></span>
      <?php endif; ?>
      <h1 class="whs-title"><?php echo e($title); ?></h1>
      <?php if($subtitle): ?>
        <p class="whs-subtitle"><?php echo e($subtitle); ?></p>
      <?php endif; ?>
    </div>

    <?php if($metric): ?>
      <div class="whs-hero-metric">
        <span class="whs-hero-metric__label"><?php echo e($metricLabel); ?></span>
        <span class="whs-hero-metric__value"><?php echo e($metricValue); ?></span>
        <?php if($metricCaption): ?>
          <span class="whs-hero-metric__caption"><?php echo e($metricCaption); ?></span>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="whs-hero__actions">
    <?php if($searchRoute): ?>
      <form method="GET" action="<?php echo e($searchRoute); ?>" class="whs-search">
        <i class="icon-base ti ti-search whs-search__icon"></i>
        <input
          type="search"
          name="q"
          value="<?php echo e(request('q')); ?>"
          class="whs-search__input"
          placeholder="<?php echo e($searchPlaceholder); ?>"
          aria-label="Search"
        >
      </form>
    <?php endif; ?>

    <?php if($createRoute): ?>
      <a href="<?php echo e($createRoute); ?>" class="whs-btn-primary">
        <i class="icon-base ti ti-plus me-2"></i>
        <?php echo e($createLabel); ?>

      </a>
    <?php endif; ?>
  </div>

  <?php if(count($filters) > 0): ?>
    <div class="whs-filter-pills">
      <?php $__currentLoopData = $filters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
          $isActive = $filter['active'] ?? false;
          $filterClass = 'whs-filter-pill' . ($isActive ? ' is-active' : '');
        ?>

        <?php if(isset($filter['url'])): ?>
          <a href="<?php echo e($filter['url']); ?>" class="<?php echo e($filterClass); ?>">
            <?php echo e($filter['label']); ?>

          </a>
        <?php else: ?>
          <button type="button" class="<?php echo e($filterClass); ?>">
            <?php echo e($filter['label']); ?>

          </button>
        <?php endif; ?>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  <?php endif; ?>
</header>
<?php /**PATH D:\WHS5\resources\views/components/whs/hero.blade.php ENDPATH**/ ?>