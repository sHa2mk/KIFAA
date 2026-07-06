<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
    <title>Kifaa</title>
</head>

<body>

{{-- =========================================================
    WELCOME HERO
    Scroll-driven hero section with twin visuals.
========================================================= --}}
<div class="kifaa-experience" id="welcome">
    <section class="kifaa-stage">

        {{-- Header logo --}}
        <div class="kifaa-logo">
            <img src="{{ asset('images/kifaa-logo.png') }}" alt="Kifaa">
        </div>

        {{-- Header navigation --}}
    <div class="kifaa-auth">
    <button type="button" class="welcome-theme-toggle" id="welcomeThemeSwitch" aria-label="Toggle theme">
    <span class="theme-icon theme-sun">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="12" cy="12" r="4"></circle>
            <path d="M12 2v2"></path>
            <path d="M12 20v2"></path>
            <path d="M4.93 4.93l1.41 1.41"></path>
            <path d="M17.66 17.66l1.41 1.41"></path>
            <path d="M2 12h2"></path>
            <path d="M20 12h2"></path>
            <path d="M4.93 19.07l1.41-1.41"></path>
            <path d="M17.66 6.34l1.41-1.41"></path>
        </svg>
    </span>

    <span class="theme-icon theme-moon">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M21 14.5A8.5 8.5 0 0 1 9.5 3 7 7 0 1 0 21 14.5Z"></path>
        </svg>
    </span>

    <span class="theme-toggle-thumb"></span>
</button>

    <a href="#about">About</a>

    @auth
        <a href="{{ route('dashboard') }}" class="primary">Dashboard</a>
    @else
        <a href="{{ route('login') }}">Login</a>

        @if (Route::has('register'))
            <a href="{{ route('register') }}" class="primary">Register</a>
        @endif
    @endauth
</div>

        {{-- Twin figures + neural connection lines --}}
        <div class="twins-layer">
            <div class="twin-energy" id="twinEnergy">
                <svg viewBox="0 0 900 420" aria-hidden="true">
                    <path class="energy-line line-1" d="M120 220 C 260 90, 640 90, 780 220" />
                    <path class="energy-line line-2" d="M130 245 C 310 180, 590 180, 770 245" />
                    <path class="energy-line line-3" d="M180 295 C 330 360, 570 360, 720 295" />

                    <circle class="energy-node n1" cx="250" cy="150" r="7" />
                    <circle class="energy-node n2" cx="450" cy="132" r="8" />
                    <circle class="energy-node n3" cx="650" cy="150" r="7" />
                    <circle class="energy-node n4" cx="338" cy="300" r="6" />
                    <circle class="energy-node n5" cx="560" cy="300" r="6" />
                </svg>
            </div>

            <div class="ai-figure ai-left" id="leftFigure">
                <div class="ai-inner">
                    <img src="{{ asset('images/ai-figure-1.png') }}" alt="Yellow AI figure">
                </div>
            </div>

            <div class="ai-figure ai-right" id="rightFigure">
                <div class="ai-inner">
                    <img src="{{ asset('images/ai-figure-2.png') }}" alt="Purple AI figure">
                </div>
            </div>
        </div>

        {{-- Main hero title --}}
        <div class="kifaa-center" id="heroText">
            <h1 class="kifaa-title">
                Meet your
                <span>career twin.</span>
            </h1>

            <p class="kifaa-subtitle">
                Kifaa turns your CV into a living model of your skills, gaps, and learning future.
            </p>

            <div class="kifaa-scroll">SCROLL TO OPEN</div>
        </div>

        {{-- Scroll reveal upload card --}}
        <div class="upload-card" id="uploadCard">
            <h2>Build your Digital Twin</h2>

            <p>
                Upload your CV and Kifaa will extract your skills, detect your target role,
                compare it with market needs, and prepare your learning path.
            </p>

            <div class="upload-actions">
                @auth
                    <a href="{{ route('cv.upload.form') }}" class="start-btn">Start Building →</a>
                @else
                    <a href="{{ route('login') }}" class="start-btn">Login to Start →</a>
                @endauth
            </div>
        </div>

    </section>
</div>

{{-- =========================================================
    ABOUT SECTION
    Premium cards with moving glow and 3D tilt.
========================================================= --}}
<section id="about" class="about-section">
    <div class="about-container">

        {{-- About intro --}}
        <div class="about-heading reveal">
            <div>
                <div class="about-kicker">ABOUT KIFAA</div>
                <h2>Your smart companion on the journey to professional success</h2>
            </div>

            <p>
                Kifaa is more than just a platform; it is a dedicated partner in your career.
                We designed it to take you from uncertainty to a clear, confident path.
            </p>
        </div>

        {{-- About cards --}}
        <div class="about-showcase">
            <div class="about-main-card reveal js-tilt-card">
                <div class="about-icon">✦</div>

                <h3>Why we built Kifaa</h3>

                <p>
                    We built Kifaa to be the digital mirror of your skills, showing the gap between
                    where you are and where you want to be in the simplest and smartest way possible.
                </p>
            </div>

            <div class="about-mini-grid">
                <div class="about-mini-card reveal js-tilt-card" style="--card-hue: 255;">
                    <div class="about-mini-icon">◇</div>
                    <h3>Understand</h3>
                    <p>Extract skills and career signals from the user’s CV.</p>
                </div>

                <div class="about-mini-card reveal js-tilt-card" style="--card-hue: 275;">
                    <div class="about-mini-icon">⇄</div>
                    <h3>Compare</h3>
                    <p>Compare current skills with market-required skills.</p>
                </div>

                <div class="about-mini-card reveal js-tilt-card" style="--card-hue: 292;">
                    <div class="about-mini-icon">↗</div>
                    <h3>Improve</h3>
                    <p>Guide users to the best courses and resources that fit their skill gaps.</p>
                </div>

                <div class="about-mini-card reveal js-tilt-card" style="--card-hue: 45;">
                    <div class="about-mini-icon">◌</div>
                    <h3>Grow</h3>
                    <p>Your Digital Twin evolves as you learn and gain new skills.</p>
                </div>
            </div>
        </div>

        {{-- =================================================
            TEAM SECTION
            Clean avatar orbit. Names stay hidden until hover.
        ================================================= --}}
        <div class="team-section">
            <div class="team-heading reveal">
                <div>
                    <div class="about-kicker">THE TEAM</div>
                    <h2>A focused team building the Kifaa experience.</h2>
                </div>

            </div>

            <div class="team-orbit reveal">
                <div class="team-core">
                    <small>KIFAA TEAM</small>
                    <h3>Four minds One Vision</h3>
                    <p> Each member powers a different layer of Kifaa, from interface and data
                    to AI analysis and course discovery </p>
                </div>

                <article class="team-avatar js-team-avatar t1" data-hue="268">
                    <img src="{{ asset('images/team/wajen.png') }}" alt="Wajen Naif AL-matrafi">
                    <div class="team-tooltip">
                        <strong>Wajen Naif Almatrafi</strong>
                    </div>
                </article>

                <article class="team-avatar js-team-avatar t2" data-hue="248">
                    <img src="{{ asset('images/team/leen.png') }}" alt="Leen Hani Alhazmi">
                    <div class="team-tooltip">
                        <strong>Leen Hani Alhazmi</strong>
                    </div>
                </article>

                <article class="team-avatar js-team-avatar t3" data-hue="285">
                    <img src="{{ asset('images/team/alya.png') }}" alt="Alya Eissa Alharthi">
                    <div class="team-tooltip">
                        <strong>Alya Eissa Alharthi</strong>
                    </div>
                </article>

                <article class="team-avatar js-team-avatar t4" data-hue="45">
                    <img src="{{ asset('images/team/shahad.png') }}" alt="Shahad Mosalam Alqurashi">
                    <div class="team-tooltip">
                        <strong>Shahad Mosalam Alqurashi</strong>
                    </div>
                </article>
            </div>
        </div>

    </div>
</section>

<script>
   
    const welcomeThemeSwitch = document.getElementById('welcomeThemeSwitch');

    function getSavedTheme() {
        return localStorage.getItem('kifaa-theme') === 'dark' ? 'dark' : 'light';
    }

    function applyTheme(theme) {
        // Save the selected theme so the welcome page keeps it after refresh.
        localStorage.setItem('kifaa-theme', theme);

        // Apply the theme on html because the CSS uses html.dark.
        document.documentElement.classList.toggle('dark', theme === 'dark');

        // Keep browser UI elements matching the selected theme.
        document.documentElement.style.colorScheme = theme;
    }

    applyTheme(getSavedTheme());

    if (welcomeThemeSwitch) {
        welcomeThemeSwitch.addEventListener('click', () => {
            const currentTheme = getSavedTheme();
            const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';

            applyTheme(nextTheme);
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Welcome hero scroll animation
    |--------------------------------------------------------------------------
    */
    const welcome = document.getElementById('welcome');
    const leftFigure = document.getElementById('leftFigure');
    const rightFigure = document.getElementById('rightFigure');
    const heroText = document.getElementById('heroText');
    const uploadCard = document.getElementById('uploadCard');
    const twinEnergy = document.getElementById('twinEnergy');

    let ticking = false;

    function clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }

    function updateWelcome() {
        const rect = welcome.getBoundingClientRect();
        const maxScroll = welcome.offsetHeight - window.innerHeight;
        const progress = clamp(-rect.top / maxScroll, 0, 1);
        const openProgress = clamp(progress / 0.42, 0, 1);

        const startDistance = 72;
        const endDistance = 190;
        const distance = startDistance + openProgress * (endDistance - startDistance);

        leftFigure.style.transform =
            `translate(calc(-${distance}% - ${openProgress * 34}vw), -50%) rotate(${-3 - openProgress * 12}deg) scale(${1.12 + openProgress * .08})`;

        rightFigure.style.transform =
            `translate(calc(${distance}% + ${openProgress * 34}vw), -50%) rotate(${3 + openProgress * 12}deg) scale(${1.12 + openProgress * .08})`;

        leftFigure.style.opacity = .76 - openProgress * .24;
        rightFigure.style.opacity = .76 - openProgress * .24;

        if (twinEnergy) {
            twinEnergy.style.opacity = 1 - openProgress * .22;
            twinEnergy.style.transform = `translate(-50%, -50%) scale(${1 + openProgress * .08})`;
        }

        const textOut = clamp((progress - 0.16) / 0.2, 0, 1);
        heroText.style.opacity = 1 - textOut;
        heroText.style.transform = `translateY(${-60 * textOut}px)`;

        const cardIn = clamp((progress - 0.34) / 0.16, 0, 1);
        const cardOut = clamp((progress - 0.82) / 0.14, 0, 1);
        const cardOpacity = cardIn * (1 - cardOut);

        uploadCard.style.opacity = cardOpacity;
        uploadCard.style.transform =
            `translateY(${50 - cardIn * 50 - cardOut * 45}px) scale(${0.94 + cardIn * 0.06 - cardOut * 0.02})`;

        ticking = false;
    }

    window.addEventListener('scroll', () => {
        if (!ticking) {
            requestAnimationFrame(updateWelcome);
            ticking = true;
        }
    }, { passive: true });

    updateWelcome();

    /*
    |--------------------------------------------------------------------------
    | Reveal on scroll
    |--------------------------------------------------------------------------
    */
    const revealItems = document.querySelectorAll('.reveal');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.14 });

    revealItems.forEach(item => observer.observe(item));

    /*
    |--------------------------------------------------------------------------
    | About cards: mouse glow + 3D tilt
    |--------------------------------------------------------------------------
    */
    document.querySelectorAll('.js-tilt-card').forEach((card, index) => {
        card.addEventListener('mousemove', e => {
            const r = card.getBoundingClientRect();
            const x = e.clientX - r.left;
            const y = e.clientY - r.top;

            const rotateY = ((x / r.width) - .5) * 7;
            const rotateX = ((y / r.height) - .5) * -7;

            card.style.setProperty('--mx', `${(x / r.width) * 100}%`);
            card.style.setProperty('--my', `${(y / r.height) * 100}%`);
            card.style.transform =
                `translateY(-10px) scale(1.012) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
            card.style.setProperty('--mx', '50%');
            card.style.setProperty('--my', '50%');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Team avatars: hover glow follows mouse
    |--------------------------------------------------------------------------
    */
    document.querySelectorAll('.js-team-avatar').forEach((avatar) => {
        const hue = avatar.dataset.hue || 265;
        avatar.style.setProperty('--team-hue', hue);

        avatar.addEventListener('mousemove', e => {
            const r = avatar.getBoundingClientRect();
            const x = e.clientX - r.left;
            const y = e.clientY - r.top;

            avatar.style.setProperty('--mx', `${(x / r.width) * 100}%`);
            avatar.style.setProperty('--my', `${(y / r.height) * 100}%`);
        });

        avatar.addEventListener('mouseleave', () => {
            avatar.style.setProperty('--mx', '50%');
            avatar.style.setProperty('--my', '30%');
        });
    });
</script>

</body>
</html>
