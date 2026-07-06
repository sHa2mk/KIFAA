<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
    <title>Register - Kifaa</title>
</head>

<body>
    <main class="auth-page auth-page-register">

        <section class="auth-form-side">
            <div class="auth-form-card">

                <a href="{{ route('home') }}" class="auth-mobile-logo">
                    <img src="{{ asset('images/kifaa-logo.png') }}" alt="Kifaa">
                </a>

                <h1>{{ __('Create account') }}</h1>

                <p>
                    {{ __('Start building your Digital Twin from your CV, skills, and career direction.') }}
                </p>

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

                @if (session('status'))
                    <div class="auth-status-box">
                        <p>{{ session('status') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.store') }}" class="auth-form">
                    @csrf

                    <div class="auth-field">
                        <label for="name">{{ __('Name') }}</label>
                        <input
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            type="text"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="{{ __('Full name') }}"
                        >
                    </div>

                    <div class="auth-field">
                        <label for="email">{{ __('Email address') }}</label>
                        <input
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            type="email"
                            required
                            autocomplete="email"
                            placeholder="email@example.com"
                        >
                    </div>

                    <div class="auth-field">
                        <label for="password">{{ __('Password') }}</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="new-password"
                            placeholder="{{ __('Password') }}"
                        >
                    </div>

                    <div class="auth-field">
                        <label for="password_confirmation">{{ __('Confirm password') }}</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            placeholder="{{ __('Confirm password') }}"
                        >
                    </div>

                    <button type="submit" class="auth-submit">
                        {{ __('Create account') }}
                    </button>
                </form>

                <div class="auth-link-row">
                    <span>{{ __('Already have an account?') }}</span>
                    <a href="{{ route('login') }}">{{ __('Log in') }}</a>
                </div>

            </div>
        </section>

        <section class="auth-visual">
            <a href="{{ route('home') }}" class="auth-brand">
                <img src="{{ asset('images/kifaa-logo.png') }}" alt="Kifaa">
            </a>

            <div class="auth-hero">
                <h2>
                    Create your
                    <span>career twin.</span>
                </h2>
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

    </main>
</body>
</html>