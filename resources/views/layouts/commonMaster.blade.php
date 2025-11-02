@php
  $layoutData = Helper::appClasses();
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="{{ $layoutData['theme'] ?? 'light' }}">
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

  {{-- Global Employee Quick View Modal (AJAX) --}}
  @auth
    <div class="modal fade" id="employeeQuickViewModal" tabindex="-1" aria-labelledby="employeeQuickViewModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: var(--sensei-surface); border: 1px solid var(--sensei-border);">
          <div class="modal-header" style="border-bottom: 1px solid var(--sensei-border); background: var(--sensei-surface-strong);">
            <h5 class="modal-title" id="employeeQuickViewModalLabel" style="color: var(--sensei-text-primary);">Employee Quick View</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="employeeQuickViewBody">
            {{-- Loading state --}}
            <div class="text-center py-5" id="employeeQuickViewLoading">
              <div class="spinner-border" style="color: var(--sensei-accent);" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-3" style="color: var(--sensei-text-secondary);">Loading employee details...</p>
            </div>

            {{-- Content loaded via AJAX --}}
            <div id="employeeQuickViewContent" style="display: none;"></div>
          </div>
          <div class="modal-footer" style="border-top: 1px solid var(--sensei-border); background: var(--sensei-surface-strong);">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <a href="#" class="btn btn-primary" id="employeeViewProfileBtn">
              <i class="bx bx-external-link me-2"></i>
              View Full Profile
            </a>
            <a href="#" class="btn btn-outline-primary" id="employeeEditBtn">
              <i class="bx bx-edit me-2"></i>
              Edit
            </a>
          </div>
        </div>
      </div>
    </div>
  @endauth

  @include('layouts/sections/scripts')
</body>
</html>
