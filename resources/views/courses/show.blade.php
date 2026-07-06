<x-layouts::app>
    <div class="k-page">
        <div class="k-container">
            <section class="k-section">
                <div class="k-section-head">
                    <h1 class="k-page-title">Recommended courses for {{ $skillName ?? $skill->name }}</h1>
                    <p class="k-page-subtitle">
                        These courses are selected to help you close this missing skill gap and improve your Digital Twin readiness.
                    </p>
                </div>

                <a href="{{ route('dashboard') }}" class="k-btn-secondary">
                    Back to Dashboard
                </a>
            </section>

            <div class="k-course-grid">
                @forelse ($courses as $course)
                    <article class="k-course-card">
                        <div class="k-course-platform">
                            {{ $course['platform'] ?? 'Unknown Platform' }}
                        </div>

                        <h2 class="k-course-title">
                            {{ $course['title'] ?? 'Untitled Course' }}
                        </h2>

                        <p class="k-course-text">
                            {{ $course['reason'] ?? 'This course is recommended because it is related to the selected missing skill.' }}
                        </p>

                        <div class="k-course-action">
                            <a href="{{ $course['link'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="k-btn-primary">
                                Open Course
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="k-card k-empty-state" style="grid-column: 1 / -1;">
                        <h2 class="k-empty-title">No course recommendations found</h2>
                        <p class="k-empty-text">
                        {{ $message ?? 'No course recommendations were found for this skill. Try again later or choose another missing skill.' }}
                       </p>
                        <div style="margin-top: 24px;">
                            <a href="{{ route('dashboard') }}" class="k-btn-secondary">
                                Back to Dashboard
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts::app>
