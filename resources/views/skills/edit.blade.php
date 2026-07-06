<x-layouts::app>

@if(! $hasProfile)
    <div class="k-card k-empty-state" style="padding: 42px; margin-top: 40px; text-align: center;">
        <h2 class="k-empty-title">No career profile yet</h2>

        <p class="k-empty-text">
            Upload your CV first, or enter your information manually.
        </p>
    </div>
@else

<div class="k-page">
    <div class="k-container">
        <section class="k-section">
            <div class="k-section-head">
                <h1 class="k-page-title">Edit your career profile</h1>
                <p class="k-page-subtitle">
                    Update your target role and skills, then refresh your Digital Twin based on your latest information.
                </p>
            </div>

            @if(session('success'))
                <div id="success-message" class="k-alert-success">
                    {{ session('success') }}
                </div>

                <script>
                    setTimeout(() => {
                        const msg = document.getElementById('success-message');
                        if (msg) msg.style.display = 'none';
                    }, 2000);
                </script>
            @endif

            @if ($errors->any())
                <div class="k-alert-error">
                    <div>Please fix the following:</div>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="k-card" style="padding: 28px; margin-top: 28px;">
                <form method="POST" action="{{ route('skills.update') }}" class="k-form">
                    @csrf
                    @method('PUT')

                    <div class="k-field">
                        <label for="job_title">Target Role</label>
                        <input
                            type="text"
                            id="job_title"
                            name="job_title"
                            value="{{ old('job_title', $jobTitle ?? '') }}"
                            placeholder="Example: Backend Developer"
                            class="k-input"
                            required
                        >
                    </div>

                    <div class="k-field">
                        <label for="skills_text">Your Skills</label>
                        <textarea
                            id="skills_text"
                            name="skills_text"
                            rows="9"
                            required
                            placeholder="Laravel&#10;PHP&#10;MySQL"
                            class="k-textarea"
                        >{{ old('skills_text', isset($skills) ? implode("\n", $skills) : '') }}</textarea>

                        <p class="k-help-text">
                            Enter one skill per line. Add, remove, or edit your skills before refreshing your Digital Twin.
                        </p>
                    </div>

                    <div class="k-action-row">
                        <button type="submit" class="k-btn-primary">
                            Save & Re-analyze Profile
                        </button>

                        @if(count($skills ?? []) > 0)
                            <button
                                type="button"
                                class="k-btn-danger-soft"
                                onclick="openResetModal()"
                            >
                                Reset Skills
                            </button>
                        @endif
                    </div>
                </form>

                @if(count($skills ?? []) > 0)
                    <form id="reset-skills-form" method="POST" action="{{ route('skills.reset') }}" class="k-hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endif

                <a href="{{ route('dashboard') }}" class="k-btn-secondary" style="margin-top: 24px;">
                    Back to Dashboard
                </a>
            </div>
        </section>
    </div>
</div>

@if(count($skills ?? []) > 0)
    <div id="resetConfirmModal" class="k-modal-overlay k-hidden">
        <div class="k-modal-card">
            <h3>Reset your skills?</h3>

            <p>
                This will remove your current skills, skill gaps, and Digital Twin data.
                You will need to upload your CV again.
            </p>

            <div class="k-modal-actions">
                <button
                    type="button"
                    class="k-btn-secondary"
                    onclick="closeResetModal()"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="k-btn-danger-soft"
                    onclick="submitResetSkills()"
                >
                    Yes, Reset
                </button>
            </div>
        </div>
    </div>

    <script>
        function openResetModal() {
            document.getElementById('resetConfirmModal').classList.remove('k-hidden');
        }

        function closeResetModal() {
            document.getElementById('resetConfirmModal').classList.add('k-hidden');
        }

        function submitResetSkills() {
            document.getElementById('reset-skills-form').submit();
        }
    </script>
@endif

@endif

</x-layouts::app> 