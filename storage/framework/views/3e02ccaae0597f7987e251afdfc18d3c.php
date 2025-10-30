<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

$items = $menu ?? [];
$currentRouteName = $currentRouteName ?? Route::currentRouteName();

$matchesRoute = $matchesRoute ?? static function ($slug) use ($currentRouteName) {
    if (empty($slug)) {
        return false;
    }

    $candidates = is_array($slug) ? $slug : [$slug];

    foreach ($candidates as $candidate) {
        if ($candidate && Str::startsWith($currentRouteName, $candidate)) {
            return true;
        }
    }

    return false;
};

if (!($hasActiveChild ?? null) instanceof \Closure) {
    $hasActiveChild = null;
}

$hasActiveChild = $hasActiveChild ?? static function ($items) use (&$hasActiveChild, $matchesRoute) {
    foreach ($items ?? [] as $child) {
        if ($matchesRoute($child->slug ?? null) || $hasActiveChild($child->submenu ?? [])) {
            return true;
        }
    }

    return false;
};

if (!($activeClassFor ?? null) instanceof \Closure) {
    $activeClassFor = null;
}

$activeClassFor = $activeClassFor ?? static function ($item) use ($matchesRoute, $hasActiveChild, $currentRouteName) {
    if (($item->slug ?? null) && $currentRouteName === $item->slug) {
        return ' is-active';
    }

    if ($matchesRoute($item->slug ?? null) || $hasActiveChild($item->submenu ?? [])) {
        return ' is-active is-open';
    }

    return '';
};
?>

<?php if(!empty($items)): ?>
  <ul class="sensei-nav__sub">
    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php
        $childClasses = $activeClassFor($child);
      ?>
      <li class="sensei-nav__item<?php echo e($childClasses); ?>">
        <a
          href="<?php echo e(isset($child->url) ? url($child->url) : 'javascript:void(0);'); ?>"
          class="sensei-nav__link<?php echo e(isset($child->submenu) ? ' has-children' : ''); ?>"
          <?php if(!empty($child->target)): ?> target="<?php echo e($child->target); ?>" <?php endif; ?>
        >
          <span class="sensei-nav__text"><?php echo e(isset($child->name) ? __($child->name) : ''); ?></span>
          <?php if(isset($child->badge)): ?>
            <span class="sensei-nav__badge"><?php echo e($child->badge[1]); ?></span>
          <?php endif; ?>
          <?php if(!empty($child->submenu)): ?>
            <i class="sensei-nav__chevron ti ti-chevron-right"></i>
          <?php endif; ?>
        </a>

        <?php if(isset($child->submenu)): ?>
          <?php echo $__env->make('layouts.sections.menu.submenu', [
            'menu' => $child->submenu,
            'matchesRoute' => $matchesRoute,
            'hasActiveChild' => $hasActiveChild,
            'activeClassFor' => $activeClassFor,
            'currentRouteName' => $currentRouteName,
          ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?>
      </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </ul>
<?php endif; ?>

<?php /**PATH D:\WHS5\resources\views/layouts/sections/menu/submenu.blade.php ENDPATH**/ ?>