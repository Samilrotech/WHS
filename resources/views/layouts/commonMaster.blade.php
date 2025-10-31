@php
  $layoutData = Helper::appClasses();
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="{{ $layoutData['theme'] ?? 'dark' }}">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />

  <title>
    @yield('title', 'WHS4') | {{ config('variables.templateName') }}
  </title>
  <meta name="description" content="{{ config('variables.templateDescription') }}" />
  <meta name="keywords" content="{{ config('variables.templateKeyword') }}" />

  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />
  <link rel="canonical" href="{{ url()->current() }}" />

  @include('layouts/sections/styles')
</head>
<body
  class="sensei-app {{ $layoutData['menuCollapsed'] ?? '' }}"
  dir="{{ ($layoutData['rtlMode'] ?? false) ? 'rtl' : 'ltr' }}"
>
  @yield('layoutContent')

  @include('layouts/sections/scripts')
</body>
</html>
