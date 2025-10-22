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
@endsection
