<!-- Page Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

@yield('page-script')
@stack('page-script')

<!-- Component Scripts -->
@stack('scripts')

<!-- app JS -->
@vite(['resources/js/app.js'])
