<x-layouts::app>
@if(! $hasProfile)
    <div class="k-card k-empty-state" style="padding: 42px; margin-top: 40px; text-align: center;">
        <h2 class="k-empty-title">No career profile yet</h2>

        <p class="k-empty-text">
            Upload your CV first, or enter your information manually.
        </p>
    </div>
@else

@if(count($userSkills) > 0)

<div class="k-page">
    <div class="k-container">

        <section class="dash-hero-card">
            <div class="dash-hero-content">

            <div class="dash-pill dash-user-pill">
            <span class="dash-hello-line">
                Hello, {{ explode(' ', auth()->user()->name)[0] }} 👋
            </span>

            <span class="dash-active-line">
                <span class="dash-active-dot"></span>
                Twin Active
            </span>
        </div>
                <h1>
                    Your Digital Twin is
                    <span>{{ $readiness }}% Ready.</span>
                </h1>

                <p>
                    Kifaa tracks your skills, detects your gaps, and turns your next learning step
                    into a clear action.
                </p>

                <div class="dash-actions">
                    <a href="#skills-gap" class="dash-primary">Continue Learning →</a>
                    <a href="{{ route('cv.upload.form', ['reanalyze' => 1]) }}" class="dash-secondary">
                        Re-analyze CV
                    </a>
                </div>
            </div>

            <div class="dash-twin-visual">
                <svg viewBox="0 0 320 320" aria-hidden="true">
                    <line x1="160" y1="160" x2="70" y2="75"></line>
                    <line x1="160" y1="160" x2="250" y2="75"></line>
                    <line x1="160" y1="160" x2="75" y2="250"></line>
                    <line x1="160" y1="160" x2="250" y2="245"></line>
                    <line x1="70" y1="75" x2="250" y2="75"></line>
                    <line x1="75" y1="250" x2="250" y2="245"></line>
                </svg>

                <div class="dash-twin-center">
                    <div class="dash-twin-glow"></div>

                    <svg class="dash-twin-lines" viewBox="0 0 160 160" aria-hidden="true">
                        <path d="M80 80 L35 35" />
                        <path d="M80 80 L125 35" />
                        <path d="M80 80 L35 125" />
                        <path d="M80 80 L125 125" />
                        <circle cx="35" cy="35" r="4" />
                        <circle cx="125" cy="35" r="4" />
                        <circle cx="35" cy="125" r="4" />
                        <circle cx="125" cy="125" r="4" />
                    </svg>

                    <img src="{{ asset('images/ai-figure-1.png') }}" alt="Digital Twin">

                    <div class="dash-twin-overlay">
                        <strong class="k-number">{{ $readiness }}%</strong>
                        <span>Ready</span>
                    </div>
                </div>

                <div class="dash-node n1">Skills</div>
                <div class="dash-node n2">Market</div>
                <div class="dash-node n3">Gaps</div>
                <div class="dash-node n4">Courses</div>
            </div>
        </section>

        <section class="k-section" style="margin-top: 24px;">
            <div class="k-section-head">
                <h1 class="k-page-title">Your career twin overview</h1>
                <p class="k-page-subtitle">
                    Track your readiness, missing skills, learning impact, and progress.
                </p>
            </div>

            <div class="k-stat-grid">
                <div class="k-stat-card">
                    <p class="k-stat-label">Twin Readiness</p>
                    <h2 class="k-stat-value">{{ $readiness }}%</h2>
                    <p class="k-stat-note">Current career readiness</p>
                    <span class="k-level-pill">{{ $twinLevel }}</span>
                </div>

                <div class="k-stat-card">
                    <p class="k-stat-label">Market Skills</p>
                    <h2 class="k-stat-value">{{ $marketSkillsCount }}</h2>
                    <p class="k-stat-note">Required by market</p>
                </div>

                <div class="k-stat-card">
                    <p class="k-stat-label">Your Skills</p>
                    <h2 class="k-stat-value">{{ count($userSkills) }}</h2>
                    <p class="k-stat-note">Extracted from your CV</p>
                </div>

                <div class="k-stat-card">
                    <p class="k-stat-label">Skills To Learn</p>
                    <h2 class="k-stat-value">{{ count($missingSkills) }}</h2>
                    <p class="k-stat-note">Recommended improvements</p>
                </div>
            </div>
        </section>

        <div class="k-dashboard-grid">
            <div class="k-card" style="padding: 24px;">
                <h3 class="k-chart-title">Skill Match Score</h3>
                <div id="matchScoreChart"></div>
                <p class="k-chart-note">{{ $matchScore }}% match with market requirements</p>
            </div>

            <div class="k-card" style="padding: 24px;">
                <h3 class="k-chart-title">Skills Distribution</h3>
                <div id="skillsDonutChart"></div>
            </div>
        </div>

        <div class="k-card k-full-card" style="padding: 24px;">
            <h3 class="k-chart-title">Overall Career Comparison</h3>
            <div id="overallComparisonChart"></div>
        </div>

        <div id="simulationBox" class="k-card k-full-card k-hidden" style="padding: 24px;">
            <h3 class="k-panel-title">Digital Twin Simulation</h3>
            <p id="simulationSkill" class="k-card-text"></p>

            <div class="k-simulation-grid">
                <div class="k-simulation-metric">
                    <p class="k-simulation-label">Current Readiness</p>
                    <p class="k-simulation-value"><span id="currentScore"></span>%</p>
                </div>

                <div class="k-simulation-metric">
                    <p class="k-simulation-label">After Learning</p>
                    <p class="k-simulation-value"><span id="predictedScore"></span>%</p>
                </div>

                <div class="k-simulation-metric">
                    <p class="k-simulation-label">Expected Impact</p>
                    <p class="k-simulation-value"><span id="increaseScore"></span></p>
                </div>
            </div>

            <div class="k-progress-track">
                <div id="simulationBar" class="k-progress-bar" style="width:0%"></div>
            </div>
        </div>

        <div id="completionBox" class="k-alert-success k-hidden">
            <h3 class="k-card-title">Digital Twin Updated</h3>
            <p id="completionMessage" class="k-card-text"></p>
        </div>

        <div class="k-skills-layout">
            <div class="k-card" style="padding: 24px;">
                <h3 class="k-panel-title">Current Skills</h3>

                <div class="k-list">
                    @forelse($userSkills as $skill)
                        <div class="k-list-item">{{ $skill }}</div>
                    @empty
                        <p class="k-card-text">No skills found</p>
                    @endforelse
                </div>
            </div>

            <div id="skills-gap" class="k-card" style="padding: 24px;">
                <div class="k-panel-head">
                    <h3 class="k-panel-title">Skills To Develop</h3>
                    <p class="k-panel-subtitle">
                        Focus on high-priority skills first to improve your readiness faster.
                    </p>
                </div>

                <div class="k-list">
                   @forelse($missingSkills as $index => $skill)
    <div class="k-skill-card">
        <div class="k-skill-row">
            <div>
                <div class="market-skill-title-wrap">
                    <p class="k-skill-name">{{ $skill['name'] }}</p>

                    @if(($skill['source'] ?? null) === 'weekly_job_market_sync')
                        <span class="market-skill-inline-badge">
                             Newly added
                        </span>
                    @endif
                </div>

                <div style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                    <span class="priority-pill {{ $skill['priority'] ?? 'medium' }}">
                        {{ strtoupper($skill['priority'] ?? 'medium') }} PRIORITY
                    </span>

                    @if(!empty($skill['priority_reason']))
                        <span class="skill-gap-reason">{{ $skill['priority_reason'] }}</span>
                    @endif
                </div>
            </div>

            <div class="k-skill-actions">
                <a href="{{ route('courses.show', ['skill' => $skill['id']]) }}" class="k-btn-primary">
                    Learn
                </a>

                <button type="button" onclick="simulateSkill('{{ addslashes($skill['name']) }}')" class="k-btn-secondary">
                    Simulate
                </button>

               <button
    type="button"
    onclick="completeCourse(this, '{{ $skill['id'] }}')"
    class="k-btn-complete"
>
    Complete
</button>
            </div>
        </div>

    </div>
@empty
    <p class="k-card-text">No missing skills</p>
@endforelse
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
const chartData = @json($chartData);

const purple = '#7C3AED';
const purpleLight = '#A78BFA';
const yellow = '#FACC15';
const softPurple = '#EDE9FE';

new ApexCharts(document.querySelector('#matchScoreChart'), {
    chart: { type: 'radialBar', height: 280, toolbar: { show: false } },
    series: [{{ $matchScore }}],
    colors: [purple],
    labels: ['Match Score'],
    plotOptions: {
        radialBar: {
            hollow: { size: '65%' },
            track: { background: softPurple },
            dataLabels: {
                name: { color: '#71717A', fontSize: '13px', fontWeight: 800 },
                value: {
                    color: purple,
                    fontSize: '30px',
                    fontWeight: 900,
                    formatter: val => parseInt(val) + '%'
                }
            }
        }
    }
}).render();

new ApexCharts(document.querySelector('#skillsDonutChart'), {
    chart: { type: 'donut', height: 280, toolbar: { show: false } },
    labels: ['Current Skills', 'Missing Skills'],
    series: [
        chartData.skillsDistribution.current,
        chartData.skillsDistribution.missing
    ],
    colors: [purpleLight, yellow],
    legend: { position: 'bottom', fontWeight: 800 },
    stroke: { width: 0 }
}).render();

new ApexCharts(document.querySelector('#overallComparisonChart'), {
    chart: { type: 'bar', height: 320, toolbar: { show: false } },
    series: [{
        name: 'Value',
        data: [
            {{ $marketSkillsCount }},
            {{ count($userSkills) }},
            {{ count($missingSkills) }},
            {{ $readiness }}
        ]
    }],
    xaxis: {
        categories: ['Market Skills', 'Your Skills', 'Missing Skills', 'Readiness %']
    },
    colors: [purple, purpleLight, yellow, softPurple],
    plotOptions: {
        bar: { borderRadius: 10, columnWidth: '45%', distributed: true }
    },
    dataLabels: { enabled: true },
    legend: { show: false }
}).render();

function simulateSkill(skillName) {
    fetch('/simulate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ skill_name: skillName })
    })
    .then(response => response.json())
    .then(data => {
        const box = document.getElementById('simulationBox');
        box.classList.remove('k-hidden');

        const reason = data.priority_reason ? ' ' + data.priority_reason : '';

        document.getElementById('simulationSkill').innerText =
            'Learning ' + data.skill + ' (' + data.priority + ' priority) may improve your Digital Twin readiness.' + reason;

        document.getElementById('currentScore').innerText = data.current;
        document.getElementById('predictedScore').innerText = data.predicted;
        document.getElementById('increaseScore').innerText = '+' + data.increase + '%';

        document.getElementById('simulationBar').style.width = '0%';

        setTimeout(() => {
            document.getElementById('simulationBar').style.width = data.predicted + '%';
        }, 100);

        box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
}function completeCourse(button, skillId) {
    button.disabled = true;
    button.innerText = 'Updating';

    fetch('/complete-course', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            skill_id: skillId,
            course_title: 'Completed Learning Activity',
            course_link: '#',
            platform: 'Custom'
        })
    })
    .then(response => response.json())
    .then(data => {
        button.innerText = 'Completed';
        button.classList.add('is-completed');

        const box = document.getElementById('completionBox');
        const message = document.getElementById('completionMessage');

        message.innerText =
            'The selected skill has been added to your current skills. Your Digital Twin has been updated.';

        box.classList.remove('k-hidden');
        box.scrollIntoView({ behavior: 'smooth', block: 'center' });

        setTimeout(() => {
            location.reload();
        }, 1200);
    })
    .catch(() => {
        button.disabled = false;
        button.innerText = 'Complete';
    });
}
</script>

@else
    <div class="k-page">
        <div class="k-container">
            <div class="k-card k-empty-state">
                <h2 class="k-empty-title">No Skill Analysis Yet</h2>
                <p class="k-empty-text">
                    Upload your CV or enter your skills manually to generate your Digital Twin analysis.
                </p>
            </div>
        </div>
    </div>
@endif
@endif
</x-layouts::app>