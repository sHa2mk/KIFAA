<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>

<body class="kifaa-body">

@auth
<aside class="kifaa-sidebar">

    <div class="kifaa-brand">
    <a
    href="{{ auth()->user()->skills()->count() === 0 || ! auth()->user()->interest_id
        ? route('cv.upload.form')
        : route('dashboard') }}"
    class="kifaa-sidebar-logo" >
    <img
        src="{{ asset('images/kifaa-logo.png') }}"
        alt="Kifaa"
        onerror="this.style.display='none'; this.parentElement.querySelector('.kifaa-sidebar-logo-fallback').style.display='grid';" >

    <span class="kifaa-sidebar-logo-fallback">K</span>
      </a>

        <div class="kifaa-fade">
            <div class="kifaa-brand-title">Kifaa</div>
            <div class="kifaa-brand-subtitle">Career Twin</div>
        </div>
    </div>


   
    <nav class="kifaa-nav">

        @if(auth()->user()->skills()->count() === 0)
            <a
    href="{{ route('cv.upload.form') }}"
    class="kifaa-link {{ request()->routeIs('cv.upload.form') ? 'active' : '' }}"
>
                <span class="kifaa-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 16V4"></path>
                        <path d="M6 10l6-6 6 6"></path>
                        <path d="M4 20h16"></path>
                    </svg>
                </span>

                <span class="kifaa-text">Build Twin</span>
            </a>
        @endif

        <a
    href="{{ route('dashboard') }}"
    class="kifaa-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
>
            <span class="kifaa-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
            </span>

            <span class="kifaa-text">Digital Twin</span>
        </a>

      <a
    href="{{ route('skills.edit') }}"
    class="kifaa-link {{ request()->routeIs('skills.edit') ? 'active' : '' }}"
>
            <span class="kifaa-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 20h9"></path>
                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                </svg>
            </span>

            <span class="kifaa-text">Edit Twin</span>
        </a>

    </nav>
    
    <div class="kifaa-bottom">
    <a href="{{ route('profile.edit') }}" class="kifaa-user">
        <span class="kifaa-avatar">
            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
        </span>

        <span class="kifaa-fade">
            <span class="kifaa-user-name">{{ auth()->user()->name ?? 'User' }}</span>
            <span class="kifaa-user-subtitle">Account settings</span>
        </span>
    </a>

    <form method="POST" action="{{ route('logout') }}" class="kifaa-logout-form">
        @csrf

        <button type="submit" class="kifaa-logout">
            <span class="kifaa-icon">↪</span>
            <span class="kifaa-text">Log Out</span>
        </button>
    </form>
</div>
</aside>
@endauth
<main class="{{ auth()->check() ? 'kifaa-main' : 'k-page' }}">
    {{ $slot }}
</main>
<script>
    (function () {
        const key = 'kifaa-scroll:' + window.location.pathname;

        function saveScrollPosition() {
            sessionStorage.setItem(key, String(window.scrollY));
        }

        function restoreScrollPosition() {
            const saved = sessionStorage.getItem(key);

            if (saved === null) {
                document.documentElement.style.visibility = 'visible';
                return;
            }

            const y = Number(saved);

            window.scrollTo(0, y);

            requestAnimationFrame(function () {
                window.scrollTo(0, y);

                setTimeout(function () {
                    window.scrollTo(0, y);
                    document.documentElement.style.visibility = 'visible';
                    sessionStorage.removeItem(key);
                }, 50);
            });
        }

        window.addEventListener('scroll', saveScrollPosition, { passive: true });
        window.addEventListener('DOMContentLoaded', restoreScrollPosition);
        window.addEventListener('load', restoreScrollPosition);
    })();
</script>
@fluxScripts

</body>
</html>