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
        <span class="sensei-search__shortcut" aria-hidden="true">
          <kbd>Ctrl</kbd>
          <span class="sensei-search__shortcut-plus">+</span>
          <kbd>K</kbd>
        </span>
      </div>

      <button type="submit" class="sensei-search__submit">
        <span class="sensei-search__submit-text">Search</span>
        <i class="ti ti-arrow-right" aria-hidden="true"></i>
      </button>
    </form>
  </div>

  <div class="sensei-topbar__actions">
    <div class="sensei-user">
      <span class="sensei-user__avatar">
        {{ strtoupper(substr(Auth::user()->name ?? 'WHS', 0, 2)) }}
      </span>
      <div class="sensei-user__meta">
        <span class="sensei-user__name">{{ Auth::user()->name ?? 'WHS Operator' }}</span>
        <span class="sensei-user__role">{{ Auth::user()->role->name ?? 'Administrator' }}</span>
      </div>
      <a
        href="{{ Route::has('profile.show') ? route('profile.show') : '#' }}"
        class="sensei-user__link"
        aria-label="Profile"
      >
        <i class="ti ti-chevron-down"></i>
      </a>
    </div>
  </div>
</header>

