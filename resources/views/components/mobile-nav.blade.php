@props(['active' => 'dashboard'])

<nav class="mobile-nav" aria-label="Mobile navigation" role="navigation">
  <a href="{{ route('dashboard') }}"
     class="mobile-nav__item {{ $active === 'dashboard' ? 'active' : '' }}"
     aria-current="{{ $active === 'dashboard' ? 'page' : 'false' }}">
    <i class="ti ti-home mobile-nav__icon" aria-hidden="true"></i>
    <span class="mobile-nav__label">Dashboard</span>
  </a>

  <a href="{{ route('incidents.index') }}"
     class="mobile-nav__item {{ $active === 'incidents' ? 'active' : '' }}"
     aria-current="{{ $active === 'incidents' ? 'page' : 'false' }}">
    <i class="ti ti-alert-triangle mobile-nav__icon" aria-hidden="true"></i>
    <span class="mobile-nav__label">Incidents</span>
  </a>

  <a href="{{ route('driver.vehicle-inspections.index') }}"
     class="mobile-nav__item {{ $active === 'assigned-vehicles' ? 'active' : '' }}"
     aria-current="{{ $active === 'assigned-vehicles' ? 'page' : 'false' }}">
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
