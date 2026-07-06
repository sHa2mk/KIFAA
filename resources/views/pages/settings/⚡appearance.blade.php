<?php

use Livewire\Component;

new class extends Component {
    //
}; ?>

<div>
    @auth
        <section class="w-full">
            @include('partials.settings-heading')

            <flux:heading class="sr-only">{{ __('Appearance Settings') }}</flux:heading>

            <x-pages::settings.layout
                :heading="__('Appearance')"
                :subheading="__('Choose how Kifaa looks on your device.')"
            >
                <div class="my-6 w-full">
                    <div class="k-appearance-options">
                        <button type="button" class="k-appearance-card" onclick="setKifaaTheme('light')">
                            <strong>Light</strong>
                            <small>Clean bright interface</small>
                        </button>

                        <button type="button" class="k-appearance-card" onclick="setKifaaTheme('dark')">
                            <strong>Dark</strong>
                            <small>Soft dark dashboard mode</small>
                        </button>

                        <button type="button" class="k-appearance-card" onclick="setKifaaTheme('system')">
                            <strong>System</strong>
                            <small>Follow your device setting</small>
                        </button>
                    </div>
                </div>
            </x-pages::settings.layout>
        </section>
    @else
    <main class="auth-page">
        <section class="auth-form-side">
            <div class="auth-form-card">

                <a href="{{ route('home') }}" class="auth-mobile-logo">
                    <img src="{{ asset('images/kifaa-logo.png') }}" alt="Kifaa">
                </a>

                <h2>Appearance</h2>

                <p>Choose how Kifaa looks on your device.</p>

                <div class="k-appearance-options">
                    <button type="button" class="k-appearance-card" onclick="setKifaaTheme('light')">
                        <strong>Light</strong>
                        <small>Clean bright interface</small>
                    </button>

                    <button type="button" class="k-appearance-card" onclick="setKifaaTheme('dark')">
                        <strong>Dark</strong>
                        <small>Soft dark dashboard mode</small>
                    </button>

                    <button type="button" class="k-appearance-card" onclick="setKifaaTheme('system')">
                        <strong>System</strong>
                        <small>Follow your device setting</small>
                    </button>
                </div>

                <div class="auth-link-row">
                    <a href="{{ route('home') }}">Back to home</a>
                </div>

            </div>
        </section>

        <section class="auth-visual">
            <a href="{{ route('home') }}" class="auth-brand">
                <img src="{{ asset('images/kifaa-logo.png') }}" alt="Kifaa">

                <div>
                    <div class="auth-brand-title">Kifaa</div>
                    <div class="auth-brand-subtitle">Career Twin Platform</div>
                </div>
            </a>

            <div class="auth-hero">
                <h1>
                    Choose your
                    <em>experience.</em>
                </h1>

                <p>
                    Switch between light and dark mode while keeping the same Kifaa visual identity.
                </p>
            </div>
        </section>
        </main>
@endauth
</div>