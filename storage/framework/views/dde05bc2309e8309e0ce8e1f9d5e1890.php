<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
?>

<?php
  $user = Auth::user();
  $displayName = $user->name ?? 'WHS Operator';
  $firstName = strtok($displayName, ' ') ?: $displayName;

  // Get current page context for breadcrumb
  $currentRouteName = Route::currentRouteName();
  $pageTitle = match(true) {
    str_contains($currentRouteName, 'incidents') => 'Incidents',
    str_contains($currentRouteName, 'vehicles') => 'Fleet Management',
    str_contains($currentRouteName, 'risk-assessments') => 'Risk Assessments',
    str_contains($currentRouteName, 'inspections') => 'Inspections',
    str_contains($currentRouteName, 'teams') => 'Teams',
    str_contains($currentRouteName, 'dashboard') => 'Dashboard',
    default => 'WHS Management'
  };
?>

<header class="sensei-topbar" data-sensei-topbar>
  
  <div class="sensei-topbar__brand">
    <a href="<?php echo e(route('dashboard')); ?>" class="sensei-topbar__logo">
      <img src="<?php echo e(asset('assets/img/branding/RR_RedWhite.png')); ?>" alt="Rotech Rural Logo" class="sensei-topbar__logo-img">
      <div class="sensei-topbar__brand-text">
        <span class="sensei-topbar__brand-name">WHS5</span>
        <span class="sensei-topbar__brand-tagline">Workplace Safety</span>
      </div>
    </a>
  </div>

  
  <div class="sensei-topbar__context">
    <div class="sensei-topbar__breadcrumb">
      <i class="ti ti-layout-dashboard sensei-topbar__breadcrumb-icon"></i>
      <span class="sensei-topbar__page-title"><?php echo e($pageTitle); ?></span>
    </div>
  </div>

  
  <div class="sensei-topbar__search">
    <form
      method="GET"
      action="<?php echo e(route('teams.index')); ?>"
      class="sensei-search"
      role="search"
      aria-label="Global search"
    >
      <div class="sensei-search__field">
        <i class="ti ti-search sensei-search__icon" aria-hidden="true"></i>
        <input
          type="search"
          name="q"
          value="<?php echo e(request('q')); ?>"
          placeholder="Search teams, incidents, assets..."
          class="sensei-search__input"
          autocomplete="off"
          spellcheck="false"
        >
        <button type="submit" class="sensei-search__submit" aria-label="Run search">
          <i class="ti ti-arrow-up-right" aria-hidden="true"></i>
        </button>
      </div>
    </form>
  </div>

  
  <div class="sensei-topbar__actions">
    
    <button
      type="button"
      class="sensei-topbar__notification-btn"
      aria-label="View notifications"
      title="Notifications"
    >
      <i class="ti ti-bell" aria-hidden="true"></i>
      <span class="sensei-topbar__notification-badge">3</span>
    </button>

    
    <div class="sensei-user" data-user-menu>
      <button
        type="button"
        class="sensei-user__trigger"
        data-user-menu-trigger
        aria-haspopup="true"
        aria-expanded="false"
      >
        <span class="sensei-user__avatar">
          <?php echo e(strtoupper(substr(Auth::user()->name ?? 'WHS', 0, 2))); ?>

        </span>
        <span class="sensei-user__meta d-none d-lg-flex">
          <span class="sensei-user__name"><?php echo e(Auth::user()->name ?? 'WHS Operator'); ?></span>
          <span class="sensei-user__role"><?php echo e(Auth::user()->role->name ?? 'Administrator'); ?></span>
        </span>
        <i class="ti ti-chevron-down sensei-user__caret d-none d-lg-inline" aria-hidden="true"></i>
      </button>

      <div class="sensei-user__dropdown" data-user-menu-panel hidden>
        <div class="sensei-user__dropdown-header">
          <span class="sensei-user__initials"><?php echo e(strtoupper(substr(Auth::user()->name ?? 'WHS', 0, 2))); ?></span>
          <div class="sensei-user__dropdown-meta">
            <strong><?php echo e(Auth::user()->name ?? 'WHS Operator'); ?></strong>
            <span><?php echo e(Auth::user()->email ?? 'operator@example.com'); ?></span>
          </div>
        </div>

        <div class="sensei-user__dropdown-body">
          <?php if(Route::has('profile.show')): ?>
            <a href="<?php echo e(route('profile.show')); ?>" class="sensei-user__dropdown-link">
              <i class="ti ti-user-circle"></i>
              <span>Profile</span>
            </a>
          <?php endif; ?>
          <a href="<?php echo e(route('dashboard')); ?>" class="sensei-user__dropdown-link">
            <i class="ti ti-layout-dashboard"></i>
            <span>Dashboard</span>
          </a>
        </div>

        <div class="sensei-user__dropdown-footer">
          <form method="POST" action="<?php echo e(route('logout', absolute: false)); ?>" id="logout-form" data-logout-form>
            <?php echo csrf_field(); ?>
            <button
              type="submit"
              class="sensei-user__dropdown-link sensei-user__dropdown-link--danger"
              data-logout-trigger
            >
              <i class="ti ti-logout-2"></i>
              <span>Log Out</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</header>

<?php /**PATH D:\WHS5\resources\views/layouts/sections/navbar/navbar-partial.blade.php ENDPATH**/ ?>