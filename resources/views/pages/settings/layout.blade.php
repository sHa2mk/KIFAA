<div class="k-settings-shell">
    <aside class="k-settings-nav">
        <a href="{{ route('profile.edit') }}" wire:navigate class="k-settings-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
            Profile
        </a>

        <a href="{{ route('user-password.edit') }}" wire:navigate class="k-settings-link {{ request()->routeIs('user-password.edit') ? 'active' : '' }}">
            Password
        </a>

        @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
            <a href="{{ route('two-factor.show') }}" wire:navigate class="k-settings-link {{ request()->routeIs('two-factor.show') ? 'active' : '' }}">
                Two-Factor Auth
            </a>
        @endif

        <a href="{{ route('appearance.edit') }}" wire:navigate class="k-settings-link {{ request()->routeIs('appearance.edit') ? 'active' : '' }}">
            Appearance
        </a>
    </aside>

    <section class="k-settings-panel">
        <div class="k-settings-head">
            <h1>{{ $heading ?? '' }}</h1>
            <p>{{ $subheading ?? '' }}</p>
        </div>

        <div class="k-settings-content">
            {{ $slot }}
        </div>
    </section>
</div>