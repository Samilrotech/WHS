<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

$menuItems = $menuData[0]->menu ?? [];
$currentRouteName = Route::currentRouteName();

$matchesRoute = static function ($slug) use ($currentRouteName) {
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

$hasActiveChild = null;
$hasActiveChild = static function ($items) use (&$hasActiveChild, $matchesRoute) {
    foreach ($items ?? [] as $child) {
        if ($matchesRoute($child->slug ?? null) || $hasActiveChild($child->submenu ?? [])) {
            return true;
        }
    }

    return false;
};

$activeClassFor = static function ($item) use ($matchesRoute, $hasActiveChild, $currentRouteName) {
    if (($item->slug ?? null) && $currentRouteName === $item->slug) {
        return ' is-active';
    }

    if ($matchesRoute($item->slug ?? null) || $hasActiveChild($item->submenu ?? [])) {
        return ' is-active is-open';
    }

    return '';
};
?>

<aside class="sensei-sidebar" id="sensei-sidebar" data-sensei-sidebar>
  <div class="sensei-sidebar__brand">
    <a href="<?php echo e(url('/')); ?>" class="sensei-sidebar__brand-link">
      <img
        src="<?php echo e(asset('assets/img/branding/RR_RedWhite.png')); ?>"
        alt="Rotech Logo"
        class="sensei-sidebar__brand-logo"
      >
      <span class="sensei-sidebar__brand-name">ROTECH WHS</span>
    </a>
    
    <button
      type="button"
      class="sensei-sidebar__close d-md-none"
      data-sidebar-close
      aria-label="Close navigation menu"
    >
      <i class="ti ti-x" aria-hidden="true"></i>
    </button>
  </div>

  <nav class="sensei-nav">
    <ul class="sensei-nav__list">
      <?php $__currentLoopData = $menuItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(isset($item->menuHeader)): ?>
          <li class="sensei-nav__section"><?php echo e(__($item->menuHeader)); ?></li>
        <?php else: ?>
          <?php
            $itemClasses = $activeClassFor($item);
            $linkClasses = 'sensei-nav__link' . (isset($item->submenu) ? ' has-children' : '');
            if (($item->slug ?? null) === 'dashboard-analytics') {
                $linkClasses .= ' sensei-nav__link--primary';
            }
          ?>

          <li class="sensei-nav__item<?php echo e($itemClasses); ?>">
            <a
              href="<?php echo e(isset($item->url) ? url($item->url) : 'javascript:void(0);'); ?>"
              class="<?php echo e($linkClasses); ?>"
              <?php if(!empty($item->target)): ?> target="<?php echo e($item->target); ?>" <?php endif; ?>
            >
              <?php if(isset($item->icon)): ?>
                <i class="sensei-nav__icon <?php echo e($item->icon); ?>"></i>
              <?php endif; ?>
              <span class="sensei-nav__text"><?php echo e(isset($item->name) ? __($item->name) : ''); ?></span>
              <?php if(isset($item->badge)): ?>
                <span class="sensei-nav__badge"><?php echo e($item->badge[1]); ?></span>
              <?php endif; ?>
              <?php if(!empty($item->submenu)): ?>
                <i class="sensei-nav__chevron ti ti-chevron-right"></i>
              <?php endif; ?>
            </a>

            <?php if(isset($item->submenu)): ?>
              <?php echo $__env->make('layouts.sections.menu.submenu', [
                'menu' => $item->submenu,
                'matchesRoute' => $matchesRoute,
                'hasActiveChild' => $hasActiveChild,
                'activeClassFor' => $activeClassFor,
                'currentRouteName' => $currentRouteName,
              ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>
          </li>
        <?php endif; ?>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
  </nav>
</aside>

<?php /**PATH D:\WHS5\resources\views/layouts/sections/menu/verticalMenu.blade.php ENDPATH**/ ?>