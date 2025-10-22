@props([
    'eyebrow' => '',
    'title' => '',
    'subtitle' => '',
    'metric' => null,
    'metricLabel' => '',
    'metricValue' => '',
    'metricCaption' => '',
    'searchRoute' => null,
    'searchPlaceholder' => 'Search...',
    'createRoute' => null,
    'createLabel' => 'Create New',
    'filters' => []
])

<header class="whs-hero">
  <div class="whs-hero__main">
    <div>
      @if($eyebrow)
        <span class="whs-eyebrow">{{ $eyebrow }}</span>
      @endif
      <h1 class="whs-title">{{ $title }}</h1>
      @if($subtitle)
        <p class="whs-subtitle">{{ $subtitle }}</p>
      @endif
    </div>

    @if($metric)
      <div class="whs-hero-metric">
        <span class="whs-hero-metric__label">{{ $metricLabel }}</span>
        <span class="whs-hero-metric__value">{{ $metricValue }}</span>
        @if($metricCaption)
          <span class="whs-hero-metric__caption">{{ $metricCaption }}</span>
        @endif
      </div>
    @endif
  </div>

  <div class="whs-hero__actions">
    @if($searchRoute)
      <form method="GET" action="{{ $searchRoute }}" class="whs-search">
        <i class="icon-base ti ti-search whs-search__icon"></i>
        <input
          type="search"
          name="q"
          value="{{ request('q') }}"
          class="whs-search__input"
          placeholder="{{ $searchPlaceholder }}"
          aria-label="Search"
        >
      </form>
    @endif

    @if($createRoute)
      <a href="{{ $createRoute }}" class="whs-btn-primary">
        <i class="icon-base ti ti-plus me-2"></i>
        {{ $createLabel }}
      </a>
    @endif
  </div>

  @if(count($filters) > 0)
    <div class="whs-filter-pills">
      @foreach ($filters as $filter)
        @php
          $isActive = $filter['active'] ?? false;
          $filterClass = 'whs-filter-pill' . ($isActive ? ' is-active' : '');
        @endphp

        @if(isset($filter['url']))
          <a href="{{ $filter['url'] }}" class="{{ $filterClass }}">
            {{ $filter['label'] }}
          </a>
        @else
          <button type="button" class="{{ $filterClass }}">
            {{ $filter['label'] }}
          </button>
        @endif
      @endforeach
    </div>
  @endif
</header>
