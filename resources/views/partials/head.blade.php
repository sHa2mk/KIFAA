<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>
<script>
    (function () {
        const key = 'kifaa-scroll:' + window.location.pathname;
        const saved = sessionStorage.getItem(key);

        if (saved !== null) {
            document.documentElement.style.visibility = 'hidden';
        }

        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }
    })();
</script>
<script>
    (function () {
        const savedTheme =
            localStorage.getItem('kifaa-theme') ||
            localStorage.getItem('kefaa-theme') ||
            'light';

        const theme = savedTheme === 'dark' ? 'dark' : 'light';

        localStorage.setItem('kifaa-theme', theme);
        localStorage.setItem('kefaa-theme', theme);
        localStorage.setItem('flux.appearance', theme);

        document.documentElement.classList.remove('dark', 'light');
        document.documentElement.classList.add(theme);
        document.documentElement.classList.toggle('dark', theme === 'dark');
        document.documentElement.dataset.theme = theme;
        document.documentElement.style.colorScheme = theme;
    })();
</script>

<style>
    html,
    body {
        background: #ffffff;
    }

    html.dark,
    html.dark body {
        background: #0f172a;
    }
</style>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<link rel="icon" type="image/x-icon" href="{{ asset('kifaa-favicon.ico') }}">
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('kifaa-favicon.ico') }}">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

<link rel="preconnect" href="https://fonts.bunny.net">

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

<script>
    function getStoredKifaaTheme() {
        const kifaaTheme = localStorage.getItem('kifaa-theme');
        const oldKefaaTheme = localStorage.getItem('kefaa-theme');

        if (kifaaTheme) {
            return kifaaTheme;
        }

        if (oldKefaaTheme) {
            localStorage.setItem('kifaa-theme', oldKefaaTheme);
            return oldKefaaTheme;
        }

        return 'light';
    }

    function applyKifaaTheme(theme) {
        const selectedTheme = theme === 'dark' ? 'dark' : 'light';

        localStorage.setItem('kifaa-theme', selectedTheme);
        localStorage.setItem('kefaa-theme', selectedTheme);
        localStorage.setItem('flux.appearance', selectedTheme);

        document.documentElement.classList.remove('dark', 'light');
        document.documentElement.classList.add(selectedTheme);
        document.documentElement.classList.toggle('dark', selectedTheme === 'dark');
        document.documentElement.dataset.theme = selectedTheme;
        document.documentElement.style.colorScheme = selectedTheme;

        document.querySelectorAll('[data-theme-option]').forEach((button) => {
            button.classList.toggle('active', button.dataset.themeOption === selectedTheme);
        });
    }

    window.setKifaaTheme = function (theme) {
        applyKifaaTheme(theme);
    };

    window.setKefaaTheme = function (theme) {
        applyKifaaTheme(theme);
    };

    document.addEventListener('DOMContentLoaded', function () {
        applyKifaaTheme(getStoredKifaaTheme());
    });

    document.addEventListener('livewire:navigated', function () {
        applyKifaaTheme(getStoredKifaaTheme());
    });
</script>