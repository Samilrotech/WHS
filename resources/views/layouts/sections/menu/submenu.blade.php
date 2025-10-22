@php
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
@endphp

@if (!empty($items))
  <ul class="sensei-nav__sub">
    @foreach ($items as $child)
      @php
        $childClasses = $activeClassFor($child);
      @endphp
      <li class="sensei-nav__item{{ $childClasses }}">
        <a
          href="{{ isset($child->url) ? url($child->url) : 'javascript:void(0);' }}"
          class="sensei-nav__link{{ isset($child->submenu) ? ' has-children' : '' }}"
          @if (!empty($child->target)) target="{{ $child->target }}" @endif
        >
          <span class="sensei-nav__text">{{ isset($child->name) ? __($child->name) : '' }}</span>
          @isset($child->badge)
            <span class="sensei-nav__badge">{{ $child->badge[1] }}</span>
          @endisset
          @if (!empty($child->submenu))
            <i class="sensei-nav__chevron ti ti-chevron-right"></i>
          @endif
        </a>

        @isset($child->submenu)
          @include('layouts.sections.menu.submenu', [
            'menu' => $child->submenu,
            'matchesRoute' => $matchesRoute,
            'hasActiveChild' => $hasActiveChild,
            'activeClassFor' => $activeClassFor,
            'currentRouteName' => $currentRouteName,
          ])
        @endisset
      </li>
    @endforeach
  </ul>
@endif

