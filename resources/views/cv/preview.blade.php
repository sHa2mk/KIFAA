<x-layouts::app>
  
    <div class="k-page">
        <div class="k-container">
            <section class="k-section">
                <div class="k-section-head">
                    <h1 class="k-page-title">
                        {{ $isManual ? 'Create your career profile' : 'Review your extracted career profile' }}
                    </h1>

                    <p class="k-page-subtitle">
                        {{ $isManual
                            ? 'Enter your target role and skills manually to build your Digital Twin.'
                            : 'Check your extracted target role and skills before saving them into your Digital Twin.'
                        }}
                    </p>
                </div>

                @if(session('success'))
                    <div id="success-message" class="k-alert-success">
                        Information saved successfully.
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
                    <form method="POST" action="{{ route('cv.confirm') }}" class="k-form">
                        @csrf

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
                            <label for="skills_text">
                                {{ $isManual ? 'Your Skills' : 'Extracted Skills' }}
                            </label>

                            <textarea
                                id="skills_text"
                                name="skills_text"
                                rows="9"
                                required
                                placeholder="Laravel&#10;PHP&#10;MySQL"
                                class="k-textarea"
                            >{{ old('skills_text', isset($skills) ? implode("\n", $skills) : '') }}</textarea>

                            <p class="k-help-text">
                                {{ $isManual
                                    ? 'Enter one skill per line. You can add, remove, or edit your skills before building your Digital Twin.'
                                    : 'These skills were extracted by AI. Review, add, remove, or edit them before saving.'
                                }}
                            </p>
                        </div>

                        <div class="k-action-row">
                            <button type="submit" class="k-btn-primary">
                                {{ $isManual ? 'Save & Build My Twin' : 'Confirm & Build My Twin' }}
                            </button>

                        </div>
                    </form>

                   

                    @if($isManual)
                        <a href="{{ route('cv.upload.form') }}" class="k-btn-secondary" style="margin-top: 24px;">
                            Go Back
                        </a>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-layouts::app>