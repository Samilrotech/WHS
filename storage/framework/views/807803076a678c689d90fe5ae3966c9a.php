<?php $__env->startSection('layoutContent'); ?>
  <div class="sensei-layout">
    <?php echo $__env->make('layouts/sections/menu/verticalMenu', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="sensei-main">
      <?php echo $__env->make('layouts/sections/navbar/navbar-partial', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

      <main class="sensei-content">
        <?php echo $__env->yieldContent('content'); ?>
      </main>

      <?php echo $__env->make('layouts/sections/footer/footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
  </div>

  
  <?php if (! empty(trim($__env->yieldContent('mobile-nav')))): ?>
    <?php echo $__env->yieldContent('mobile-nav'); ?>
  <?php else: ?>
    <?php if (isset($component)) { $__componentOriginald46331a8752b5fd6e6035ba2d20e7c19 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald46331a8752b5fd6e6035ba2d20e7c19 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.mobile-nav','data' => ['active' => $mobileNavActive ?? 'dashboard']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('mobile-nav'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($mobileNavActive ?? 'dashboard')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald46331a8752b5fd6e6035ba2d20e7c19)): ?>
<?php $attributes = $__attributesOriginald46331a8752b5fd6e6035ba2d20e7c19; ?>
<?php unset($__attributesOriginald46331a8752b5fd6e6035ba2d20e7c19); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald46331a8752b5fd6e6035ba2d20e7c19)): ?>
<?php $component = $__componentOriginald46331a8752b5fd6e6035ba2d20e7c19; ?>
<?php unset($__componentOriginald46331a8752b5fd6e6035ba2d20e7c19); ?>
<?php endif; ?>
  <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts/commonMaster', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\WHS5\resources\views/layouts/contentNavbarLayout.blade.php ENDPATH**/ ?>