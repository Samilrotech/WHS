@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
@endphp

@php
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
@endphp

<header class="sensei-topbar" data-sensei-topbar>
  {{-- Logo & Brand Section --}}
  <div class="sensei-topbar__brand">
    <a href="{{ route('dashboard') }}" class="sensei-topbar__logo">
      {{-- Dark theme logo (white text) - hidden in light theme --}}
      <img src="{{ asset('assets/img/branding/RR_RedWhite.png') }}" alt="Rotech Rural Logo" class="sensei-topbar__logo-img sensei-topbar__logo-img--dark">
      {{-- Light theme logo (black text) - hidden in dark theme --}}
      <img src="{{ asset('assets/img/branding/RR_RedBlack.png') }}" alt="Rotech Rural Logo" class="sensei-topbar__logo-img sensei-topbar__logo-img--light">
      <div class="sensei-topbar__brand-text">
        <span class="sensei-topbar__brand-name">WHS5</span>
        <span class="sensei-topbar__brand-tagline">Workplace Safety</span>
      </div>
    </a>
  </div>

  {{-- Context & Breadcrumb --}}
  <div class="sensei-topbar__context">
    <div class="sensei-topbar__breadcrumb">
      <i class="ti ti-layout-dashboard sensei-topbar__breadcrumb-icon"></i>
      <span class="sensei-topbar__page-title">{{ $pageTitle }}</span>
    </div>
  </div>

  {{-- Search --}}
  <div class="sensei-topbar__search">
    <form
      method="GET"
      action="{{ route('teams.index') }}"
      class="sensei-search"
      role="search"
      aria-label="Global search"
    >
      <div class="sensei-search__field">
        <i class="ti ti-search sensei-search__icon" aria-hidden="true"></i>
        <input
          type="search"
          name="q"
          value="{{ request('q') }}"
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

  {{-- Actions: Notifications & User Menu --}}
  <div class="sensei-topbar__actions">
    {{-- Notifications Button --}}
    <button
      type="button"
      class="sensei-topbar__notification-btn"
      aria-label="View notifications"
      title="Notifications"
    >
      <i class="ti ti-bell" aria-hidden="true"></i>
      <span class="sensei-topbar__notification-badge">3</span>
    </button>

    {{-- Theme Toggle Button --}}
    <button
      type="button"
      class="sensei-topbar__theme-toggle"
      data-theme-toggle
      aria-label="Switch to dark theme"
      title="Toggle theme"
    >
      <i class="bx bx-moon" aria-hidden="true"></i>
    </button>

    {{-- User Menu --}}
    <div class="sensei-user" data-user-menu>
      <button
        type="button"
        class="sensei-user__trigger"
        data-user-menu-trigger
        aria-haspopup="true"
        aria-expanded="false"
      >
        <span class="sensei-user__avatar">
          {{ strtoupper(substr(Auth::user()->name ?? 'WHS', 0, 2)) }}
        </span>
        <span class="sensei-user__meta d-none d-lg-flex">
          <span class="sensei-user__name">{{ Auth::user()->name ?? 'WHS Operator' }}</span>
          <span class="sensei-user__role">{{ Auth::user()->role->name ?? 'Administrator' }}</span>
        </span>
        <i class="ti ti-chevron-down sensei-user__caret d-none d-lg-inline" aria-hidden="true"></i>
      </button>

      <div class="sensei-user__dropdown" data-user-menu-panel hidden>
        <div class="sensei-user__dropdown-header">
          <span class="sensei-user__initials">{{ strtoupper(substr(Auth::user()->name ?? 'WHS', 0, 2)) }}</span>
          <div class="sensei-user__dropdown-meta">
            <strong>{{ Auth::user()->name ?? 'WHS Operator' }}</strong>
            <span>{{ Auth::user()->email ?? 'operator@example.com' }}</span>
          </div>
        </div>

        <div class="sensei-user__dropdown-body">
          @if(Route::has('profile.show'))
            <a href="{{ route('profile.show') }}" class="sensei-user__dropdown-link">
              <i class="ti ti-user-circle"></i>
              <span>Profile</span>
            </a>
          @endif
          <a href="{{ route('dashboard') }}" class="sensei-user__dropdown-link">
            <i class="ti ti-layout-dashboard"></i>
            <span>Dashboard</span>
          </a>
        </div>

        <div class="sensei-user__dropdown-footer">
          <form method="POST" action="{{ route('logout', absolute: false) }}" id="logout-form" data-logout-form>
            @csrf
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

