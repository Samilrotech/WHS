<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['active' => 'dashboard']));

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

foreach (array_filter((['active' => 'dashboard']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<nav class="mobile-nav" aria-label="Mobile navigation" role="navigation">
  <a href="<?php echo e(route('dashboard')); ?>"
     class="mobile-nav__item <?php echo e($active === 'dashboard' ? 'active' : ''); ?>"
     aria-current="<?php echo e($active === 'dashboard' ? 'page' : 'false'); ?>">
    <i class="ti ti-home mobile-nav__icon" aria-hidden="true"></i>
    <span class="mobile-nav__label">Dashboard</span>
  </a>

  <a href="<?php echo e(route('incidents.index')); ?>"
     class="mobile-nav__item <?php echo e($active === 'incidents' ? 'active' : ''); ?>"
     aria-current="<?php echo e($active === 'incidents' ? 'page' : 'false'); ?>">
    <i class="ti ti-alert-triangle mobile-nav__icon" aria-hidden="true"></i>
    <span class="mobile-nav__label">Incidents</span>
  </a>

  <a href="<?php echo e(route('driver.vehicle-inspections.index')); ?>"
     class="mobile-nav__item <?php echo e($active === 'assigned-vehicles' ? 'active' : ''); ?>"
     aria-current="<?php echo e($active === 'assigned-vehicles' ? 'page' : 'false'); ?>">
    <i class="ti ti-car-garage mobile-nav__icon" aria-hidden="true"></i>
    <span class="mobile-nav__label">My Vehicle</span>
  </a>

  <button type="button"
          class="mobile-nav__item"
          data-sidebar-toggle
          aria-label="Open menu"
          aria-expanded="false"
          aria-controls="sensei-sidebar">
    <i class="ti ti-menu-2 mobile-nav__icon" aria-hidden="true"></i>
    <span class="mobile-nav__label">More</span>
  </button>
</nav>
<?php /**PATH D:\WHS5\resources\views/components/mobile-nav.blade.php ENDPATH**/ ?>