<?php
  $layoutData = Helper::appClasses();
?>

<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>" data-bs-theme="<?php echo e($layoutData['theme'] ?? 'dark'); ?>">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>" />

  <title>
    <?php echo $__env->yieldContent('title', 'WHS4'); ?> | <?php echo e(config('variables.templateName')); ?>

  </title>
  <meta name="description" content="<?php echo e(config('variables.templateDescription')); ?>" />
  <meta name="keywords" content="<?php echo e(config('variables.templateKeyword')); ?>" />

  <link rel="icon" type="image/png" href="<?php echo e(asset('assets/img/favicon/favicon.ico')); ?>" />
  <link rel="canonical" href="<?php echo e(url()->current()); ?>" />

  <?php echo $__env->make('layouts/sections/styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body
  class="sensei-app <?php echo e($layoutData['menuCollapsed'] ?? ''); ?>"
  dir="<?php echo e(($layoutData['rtlMode'] ?? false) ? 'rtl' : 'ltr'); ?>"
>
  <?php echo $__env->yieldContent('layoutContent'); ?>

  <?php echo $__env->make('layouts/sections/scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH D:\WHS5\resources\views/layouts/commonMaster.blade.php ENDPATH**/ ?>