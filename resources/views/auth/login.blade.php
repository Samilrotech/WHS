@extends('layouts/blankLayout')

@section('title', 'Login - Rotech Rural')

@section('content')
<div class="rotech-login">
  <div class="rotech-login__card">
    <div class="rotech-login__logo">
      {{-- Dark theme logo (white text) - hidden in light theme --}}
      <img src="{{ asset('assets/img/branding/RR_RedWhite.png') }}" alt="Rotech Rural logo" class="rotech-login__logo-img rotech-login__logo-img--dark">
      {{-- Light theme logo (black text) - hidden in dark theme --}}
      <img src="{{ asset('assets/img/branding/RR_RedBlack.png') }}" alt="Rotech Rural logo" class="rotech-login__logo-img rotech-login__logo-img--light">
    </div>

    <h1 class="rotech-login__title">Rotech Rural</h1>
    <p class="rotech-login__subtitle">Fencing hardware partner portal</p>

    @if (session('status'))
      <div class="rotech-login__alert">
        {{ session('status') }}
      </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="rotech-login__form">
      @csrf

      <label class="rotech-field">
        <span class="rotech-field__label">Email</span>
        <input
          type="email"
          name="email"
          value="{{ old('email') }}"
          placeholder="you@rotech.com.au"
          autocomplete="username"
          required
          class="rotech-field__input @error('email') is-invalid @enderror"
        >
        @error('email')
          <span class="rotech-field__error">{{ $message }}</span>
        @enderror
      </label>

      <label class="rotech-field rotech-field--password">
        <span class="rotech-field__label">
          Password
          @php $hasPasswordRequest = \Illuminate\Support\Facades\Route::has('password.request'); @endphp
          @if ($hasPasswordRequest)
            <a href="{{ route('password.request') }}">Forgot?</a>
          @endif
        </span>
        <div class="rotech-field__password">
          <input
            type="password"
            name="password"
            placeholder="Enter your password"
            autocomplete="current-password"
            required
            class="rotech-field__input @error('password') is-invalid @enderror"
          >
          <button type="button" class="rotech-field__toggle" aria-label="Toggle password visibility">
            <i class="ti ti-eye"></i>
          </button>
        </div>
        @error('password')
          <span class="rotech-field__error">{{ $message }}</span>
        @enderror
      </label>

      <label class="rotech-checkbox">
        <input type="checkbox" name="remember">
        <span>Keep me signed in</span>
      </label>

      <button type="submit" class="rotech-login__submit">
        <span>Sign in</span>
        <i class="ti ti-arrow-narrow-right"></i>
      </button>
    </form>

    @php $hasRegister = \Illuminate\Support\Facades\Route::has('register'); @endphp
    @if ($hasRegister)
      <p class="rotech-login__footer">
        New to Rotech Rural? <a href="{{ route('register') }}">Request portal access</a>
      </p>
    @endif
  </div>
</div>
@endsection

@section('page-script')
<script>
document.querySelectorAll('.rotech-field__toggle').forEach(toggle => {
  toggle.addEventListener('click', () => {
    const input = toggle.previousElementSibling;
    const icon = toggle.querySelector('i');
    const isPassword = input.type === 'password';

    input.type = isPassword ? 'text' : 'password';
    icon.classList.toggle('ti-eye', !isPassword);
    icon.classList.toggle('ti-eye-off', isPassword);
  });
});
</script>
@endsection

@section('page-style')
<style>
  body {
    min-height: 100vh;
    background: radial-gradient(circle at 18% 22%, rgba(90, 139, 255, 0.24), transparent 55%),
      radial-gradient(circle at 78% 8%, rgba(235, 68, 68, 0.22), transparent 60%),
      linear-gradient(140deg, #05070b, #0c111a 45%, #121c2b 100%);
    color: #f2f4ff;
  }

  .rotech-login {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: clamp(2rem, 5vw, 5rem);
  }

  .rotech-login__card {
    width: min(420px, 100%);
    background: rgba(12, 16, 26, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 28px;
    padding: clamp(2.6rem, 5vw, 3.4rem);
    box-shadow: 0 45px 110px -70px rgba(5, 12, 30, 0.85);
    backdrop-filter: blur(24px);
    text-align: center;
    display: flex;
    flex-direction: column;
    gap: 1.8rem;
  }

  .rotech-login__logo {
    width: 76px;
    height: 76px;
    border-radius: 20px;
    background: rgba(235, 68, 68, 0.2);
    border: 1px solid rgba(235, 68, 68, 0.35);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    box-shadow: 0 30px 60px -38px rgba(235, 68, 68, 0.72);
  }

  .rotech-login__logo img {
    max-width: 50px;
    height: auto;
  }

  .rotech-login__title {
    margin: 0;
    font-size: 1.6rem;
    font-weight: 700;
    letter-spacing: 0.24em;
    text-transform: uppercase;
  }

  .rotech-login__subtitle {
    margin: 0;
    font-size: 0.9rem;
    color: rgba(187, 198, 224, 0.76);
  }

  .rotech-login__alert {
    padding: 0.85rem 1rem;
    border-radius: 16px;
    font-size: 0.85rem;
    background: rgba(94, 234, 212, 0.14);
    border: 1px solid rgba(94, 234, 212, 0.32);
    color: #5eead4;
  }

  .rotech-login__form {
    display: flex;
    flex-direction: column;
    gap: 1.1rem;
    text-align: left;
  }

  .rotech-field {
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
  }

  .rotech-field__label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(187, 198, 224, 0.78);
  }

  .rotech-field__label a {
    letter-spacing: normal;
    font-weight: 500;
    font-size: 0.76rem;
    color: #8fb3ff;
    text-decoration: none;
  }

  .rotech-field__input {
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(9, 12, 19, 0.84);
    padding: 0.85rem 1rem;
    color: #f2f4ff;
    font-size: 0.92rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
  }

  .rotech-field__input:focus {
    outline: none;
    border-color: rgba(90, 139, 255, 0.45);
    box-shadow: 0 0 0 4px rgba(90, 139, 255, 0.18);
    background: rgba(12, 16, 26, 0.92);
  }

  .rotech-field__password {
    position: relative;
  }

  .rotech-field__toggle {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: rgba(187, 198, 224, 0.65);
    font-size: 1.05rem;
    cursor: pointer;
  }

  .rotech-field__error {
    font-size: 0.75rem;
    color: #f87171;
  }

  .rotech-checkbox {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    font-size: 0.8rem;
    color: rgba(187, 198, 224, 0.7);
  }

  .rotech-checkbox input {
    width: 16px;
    height: 16px;
    accent-color: #5a8bff;
  }

  .rotech-login__submit {
    margin-top: 0.5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    padding: 0.95rem 1.1rem;
    border-radius: 18px;
    border: none;
    background: linear-gradient(135deg, rgba(235, 68, 68, 1), rgba(90, 139, 255, 0.95));
    color: #ffffff;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .rotech-login__submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 26px 56px -32px rgba(235, 68, 68, 0.75);
  }

  .rotech-login__footer {
    margin: 0;
    font-size: 0.8rem;
    color: rgba(187, 198, 224, 0.7);
  }

  .rotech-login__footer a {
    color: #8fb3ff;
    font-weight: 600;
    text-decoration: none;
  }

  .rotech-login__footer a:hover {
    color: #c1d1ff;
  }

  @media (max-width: 576px) {
    .rotech-login {
      padding: 1.5rem;
    }

    .rotech-login__card {
      padding: 2rem;
      gap: 1.6rem;
    }
  }

  /* ===================================================================
     LIGHT THEME OVERRIDES - Login Page
     Fix dark backgrounds for light theme
     =================================================================== */

  [data-bs-theme='light'] body {
    background: radial-gradient(circle at 18% 22%, rgba(59, 130, 246, 0.12), transparent 55%),
      radial-gradient(circle at 78% 8%, rgba(239, 68, 68, 0.10), transparent 60%),
      linear-gradient(140deg, #f8fafc, #f1f5f9 45%, #e2e8f0 100%);
    color: #0f172a;
  }

  [data-bs-theme='light'] .rotech-login__card {
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(15, 23, 42, 0.12);
    box-shadow: 0 45px 110px -70px rgba(15, 23, 42, 0.15);
  }

  [data-bs-theme='light'] .rotech-login__logo {
    background: rgba(239, 68, 68, 0.08);
    border: 1px solid rgba(239, 68, 68, 0.15);
    box-shadow: 0 30px 60px -38px rgba(239, 68, 68, 0.25);
  }

  [data-bs-theme='light'] .rotech-login__title {
    color: #0f172a;
  }

  [data-bs-theme='light'] .rotech-login__subtitle {
    color: rgba(71, 85, 105, 0.85);
  }

  [data-bs-theme='light'] .rotech-login__alert {
    background: rgba(20, 184, 166, 0.10);
    border: 1px solid rgba(20, 184, 166, 0.25);
    color: #0d9488;
  }

  [data-bs-theme='light'] .rotech-field__label {
    color: rgba(51, 65, 85, 0.85);
  }

  [data-bs-theme='light'] .rotech-field__label a {
    color: #3b82f6;
  }

  [data-bs-theme='light'] .rotech-field__label a:hover {
    color: #2563eb;
  }

  [data-bs-theme='light'] .rotech-field__input {
    border: 1px solid rgba(15, 23, 42, 0.15);
    background: rgba(255, 255, 255, 0.90);
    color: #0f172a;
  }

  [data-bs-theme='light'] .rotech-field__input:focus {
    border-color: rgba(59, 130, 246, 0.35);
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.10);
    background: rgba(255, 255, 255, 0.98);
  }

  [data-bs-theme='light'] .rotech-field__input::placeholder {
    color: rgba(71, 85, 105, 0.50);
  }

  [data-bs-theme='light'] .rotech-field__toggle {
    color: rgba(71, 85, 105, 0.70);
  }

  [data-bs-theme='light'] .rotech-field__error {
    color: #dc2626;
  }

  [data-bs-theme='light'] .rotech-checkbox {
    color: rgba(71, 85, 105, 0.80);
  }

  [data-bs-theme='light'] .rotech-checkbox input {
    accent-color: #3b82f6;
  }

  [data-bs-theme='light'] .rotech-login__submit {
    background: linear-gradient(135deg, rgba(239, 68, 68, 1), rgba(59, 130, 246, 0.95));
  }

  [data-bs-theme='light'] .rotech-login__submit:hover {
    box-shadow: 0 26px 56px -32px rgba(239, 68, 68, 0.50);
  }

  [data-bs-theme='light'] .rotech-login__footer {
    color: rgba(71, 85, 105, 0.80);
  }

  [data-bs-theme='light'] .rotech-login__footer a {
    color: #3b82f6;
  }

  [data-bs-theme='light'] .rotech-login__footer a:hover {
    color: #2563eb;
  }
</style>
@endsection
