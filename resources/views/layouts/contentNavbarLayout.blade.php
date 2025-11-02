@extends('layouts/commonMaster')

@section('layoutContent')
  {{-- Skip Navigation Link for Keyboard Users (WCAG 2.1 A) --}}
  <a href="#main-content" class="sensei-skip-link">
    Skip to main content
  </a>

  <div class="sensei-layout">
    @include('layouts/sections/menu/verticalMenu')

    <div class="sensei-main">
      @include('layouts/sections/navbar/navbar-partial')

      <main id="main-content" class="sensei-content" tabindex="-1">
        @yield('content')
      </main>

      @include('layouts/sections/footer/footer')
    </div>
  </div>

  {{-- Mobile Bottom Navigation --}}
  @hasSection('mobile-nav')
    @yield('mobile-nav')
  @else
    <x-mobile-nav :active="$mobileNavActive ?? 'dashboard'" />
  @endif
@endsection
