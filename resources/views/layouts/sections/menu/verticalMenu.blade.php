@php
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
@endphp

<aside class="sensei-sidebar" id="sensei-sidebar" data-sensei-sidebar>
  <div class="sensei-sidebar__brand">
    <a href="{{ url('/') }}" class="sensei-sidebar__brand-link">
      {{-- Dark theme logo (white text) - hidden in light theme --}}
      <img
        src="{{ asset('assets/img/branding/RR_RedWhite.png') }}"
        alt="Rotech Logo"
        class="sensei-sidebar__brand-logo sensei-sidebar__brand-logo--dark"
      >
      {{-- Light theme logo (black text) - hidden in dark theme --}}
      <img
        src="{{ asset('assets/img/branding/RR_RedBlack.png') }}"
        alt="Rotech Logo"
        class="sensei-sidebar__brand-logo sensei-sidebar__brand-logo--light"
      >
      <span class="sensei-sidebar__brand-name">ROTECH WHS</span>
    </a>
    {{-- Mobile close button --}}
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
      @foreach ($menuItems as $item)
        @if (isset($item->menuHeader))
          <li class="sensei-nav__section">{{ __($item->menuHeader) }}</li>
        @else
          @php
            $itemClasses = $activeClassFor($item);
            $linkClasses = 'sensei-nav__link' . (isset($item->submenu) ? ' has-children' : '');
            if (($item->slug ?? null) === 'dashboard-analytics') {
                $linkClasses .= ' sensei-nav__link--primary';
            }
          @endphp

          <li class="sensei-nav__item{{ $itemClasses }}">
            <a
              href="{{ isset($item->url) ? url($item->url) : 'javascript:void(0);' }}"
              class="{{ $linkClasses }}"
              @if (!empty($item->target)) target="{{ $item->target }}" @endif
            >
              @isset($item->icon)
                <i class="sensei-nav__icon {{ $item->icon }}"></i>
              @endisset
              <span class="sensei-nav__text">{{ isset($item->name) ? __($item->name) : '' }}</span>
              @isset($item->badge)
                <span class="sensei-nav__badge">{{ $item->badge[1] }}</span>
              @endisset
              @if (!empty($item->submenu))
                <i class="sensei-nav__chevron ti ti-chevron-right"></i>
              @endif
            </a>

            @isset($item->submenu)
              @include('layouts.sections.menu.submenu', [
                'menu' => $item->submenu,
                'matchesRoute' => $matchesRoute,
                'hasActiveChild' => $hasActiveChild,
                'activeClassFor' => $activeClassFor,
                'currentRouteName' => $currentRouteName,
              ])
            @endisset
          </li>
        @endif
      @endforeach
    </ul>
  </nav>
</aside>

