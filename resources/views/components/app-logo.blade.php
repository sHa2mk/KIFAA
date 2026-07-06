<a href="{{ url('/') }}" class="auth-logo-glow inline-flex items-center justify-center" wire:navigate>
    <img
        src="{{ asset('images/kifaa-logo.png') }}"
        alt="Kifaa"
        class="auth-app-logo-img"
    >

    <span class="sr-only">{{ config('app.name', 'Kifaa') }}</span>
</a>