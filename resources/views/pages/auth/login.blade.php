<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
    <title>Login - Kifaa</title>
</head>

<body>
    <main class="auth-page">

        <section class="auth-visual">
            <a href="{{ route('home') }}" class="auth-brand">
                <img src="{{ asset('images/kifaa-logo.png') }}" alt="Kifaa">
            </a>

            <div class="auth-hero">
                <h1>
                    Build your
                    <em>future profile.</em>
                </h1>
            </div>

            <div class="auth-orbit">
                <div class="auth-core auth-twin-visual">
                    <img src="{{ asset('images/twin-kfiaa.png') }}" alt="Digital Twin">
                </div>

                <div class="auth-node one">CV</div>
                <div class="auth-node two">AI</div>
                <div class="auth-node three">%</div>
            </div>
        </section>

        <section class="auth-form-side">
            <div class="auth-form-card">

                <a href="{{ route('home') }}" class="auth-mobile-logo">
                    <img src="{{ asset('images/kifaa-logo.png') }}" alt="Kifaa">
                </a>

                <h2>{{ __('Welcome back') }}</h2>

                <p>{{ __('Log in to continue your Digital Twin journey.') }}</p>

                @if (session('status'))
                    <div class="auth-status-box">
                        <p>{{ session('status') }}</p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="auth-error-box">
                        <p>{{ __('Please fix the following:') }}</p>

                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="auth-form">
                    @csrf

                    <div class="auth-field">
                        <label for="email">{{ __('Email address') }}</label>
                        <input
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            type="email"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="email@example.com"
                        >
                    </div>

                    <div class="auth-field">
                        <div class="auth-password-top">
                            <label for="password">{{ __('Password') }}</label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}">
                                    {{ __('Forgot password?') }}
                                </a>
                            @endif
                        </div>

                        <div class="auth-password-wrap">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                autocomplete="current-password"
                                placeholder="{{ __('Password') }}"
                            >

                            <button
                                type="button"
                                class="auth-eye"
                                onclick="togglePassword('password', this)"
                                aria-label="Show password"
                            >
                                <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" />
                                    <circle cx="12" cy="12" r="3" stroke-width="2" />
                                </svg>

                                <svg class="eye-closed hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M10.6 10.6A2 2 0 0012 14a2 2 0 001.4-.6" />
                                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M7.1 7.1C4.2 8.8 2.25 12 2.25 12S6 18.75 12 18.75c1.7 0 3.2-.45 4.45-1.1" />
                                    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M17.7 14.7C20.2 13.1 21.75 12 21.75 12S18 5.25 12 5.25c-.9 0-1.75.12-2.55.35" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <label class="auth-remember">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        {{ __('Remember me') }}
                    </label>

                    <button type="submit" class="auth-submit" data-test="login-button">
                        {{ __('Log in') }}
                    </button>
                </form>

                @if (Route::has('register'))
                    <div class="auth-link-row">
                        <span>{{ __('Don\'t have an account?') }}</span>
                        <a href="{{ route('register') }}">{{ __('Sign up') }}</a>
                    </div>
                @endif

            </div>
        </section>

    </main>

    <script>
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const eyeOpen = button.querySelector('.eye-open');
            const eyeClosed = button.querySelector('.eye-closed');

            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }
    </script>
</body>
</html>