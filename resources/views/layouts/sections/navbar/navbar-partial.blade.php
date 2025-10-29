@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
@endphp

<header class="sensei-topbar" data-sensei-topbar>
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

  <div class="sensei-topbar__actions">
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
        <span class="sensei-user__meta">
          <span class="sensei-user__name">{{ Auth::user()->name ?? 'WHS Operator' }}</span>
          <span class="sensei-user__role">{{ Auth::user()->role->name ?? 'Administrator' }}</span>
        </span>
        <i class="ti ti-chevron-down sensei-user__caret" aria-hidden="true"></i>
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

