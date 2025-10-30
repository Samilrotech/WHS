@extends('layouts/commonMaster')

@section('layoutContent')
  <div class="sensei-layout">
    @include('layouts/sections/menu/verticalMenu')

    <div class="sensei-main">
      @include('layouts/sections/navbar/navbar-partial')

      <main class="sensei-content">
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
