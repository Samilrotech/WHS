@extends('layouts/blankLayout')

@section('title', 'Login - Rotech WHS')

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/vendor/css/pages/page-auth.css')}}">
@endsection

@section('content')
<div class="container-xxl">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner">
      <!-- Login Card -->
      <div class="card">
        <div class="card-body">
          <!-- Logo -->
          <div class="app-brand justify-content-center mb-4">
            <a href="/" class="app-brand-link gap-2">
              <img src="{{ asset('assets/img/branding/RR_RedWhite.png') }}" alt="Rotech Logo" style="height: 60px;">
            </a>
          </div>
          <!-- /Logo -->

          <h4 class="mb-2 text-center">Rotech WHS</h4>
          <p class="mb-4 text-center">Workplace Health & Safety Management System</p>

          <!-- Session Status -->
          @if (session('status'))
            <div class="alert alert-success alert-dismissible" role="alert">
              {{ session('status') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          <form id="formAuthentication" class="mb-3" method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="Enter your email"
                required
                autofocus
                autocomplete="username"
              >
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Password -->
            <div class="mb-3 form-password-toggle">
              <div class="d-flex justify-content-between">
                <label class="form-label" for="password">Password</label>
                @php
                  $hasPasswordRequest = \Illuminate\Support\Facades\Route::has('password.request');
                @endphp
                @if ($hasPasswordRequest)
                  <a href="{{ route('password.request') }}">
                    <small>Forgot Password?</small>
                  </a>
                @endif
              </div>
              <div class="input-group input-group-merge">
                <input
                  type="password"
                  id="password"
                  class="form-control @error('password') is-invalid @enderror"
                  name="password"
                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                  required
                  autocomplete="current-password"
                  aria-describedby="password"
                >
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                @error('password')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- Remember Me -->
            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                <label class="form-check-label" for="remember_me">
                  Remember Me
                </label>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="mb-3">
              <button class="btn btn-primary d-grid w-100" type="submit">Sign in</button>
            </div>
          </form>

          @php
            $hasRegister = \Illuminate\Support\Facades\Route::has('register');
          @endphp
          @if ($hasRegister)
            <p class="text-center">
              <span>New on our platform?</span>
              <a href="{{ route('register') }}">
                <span>Create an account</span>
              </a>
            </p>
          @endif
        </div>
      </div>
      <!-- /Login Card -->
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
// Password toggle functionality
document.querySelectorAll('.form-password-toggle .input-group-text').forEach(function(el) {
  el.addEventListener('click', function() {
    const input = this.previousElementSibling;
    const icon = this.querySelector('i');

    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.remove('bx-hide');
      icon.classList.add('bx-show');
    } else {
      input.type = 'password';
      icon.classList.remove('bx-show');
      icon.classList.add('bx-hide');
    }
  });
});
</script>
@endsection
