<x-layouts::app>
    <div class="k-page">
        <div class="k-container">
            <section class="k-section">
                <div class="k-grid-two">
                    <div>
                        <div class="k-section-head">
                            <h1 class="k-page-title">Build your Digital Twin from your CV</h1>
                            <p class="k-page-subtitle">
                                Upload your resume and Kifaa will extract your skills, detect gaps,
                                recommend courses, and calculate your twin readiness.
                            </p>
                        </div>

                        {{--Displays the main CV processing steps shown to the user. --}}

                        <div class="k-grid-cards">
                            <div class="k-feature-card">
                                <div class="k-step-number">01</div>
                                <div>
                                    <h3 class="k-card-title">Extract Skills</h3>
                                    <p class="k-card-text">Analyze your CV and detect technical and soft skills.</p>
                                </div>
                            </div>

                            <div class="k-feature-card">
                                <div class="k-step-number">02</div>
                                <div>
                                    <h3 class="k-card-title">Find Skill Gaps</h3>
                                    <p class="k-card-text">Compare your skills with real market requirements.</p>
                                </div>
                            </div>

                            <div class="k-feature-card">
                                <div class="k-step-number">03</div>
                                <div>
                                    <h3 class="k-card-title">Generate Twin Readiness</h3>
                                    <p class="k-card-text">Create your dynamic career twin readiness score.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="k-card k-upload-panel">
                        <form method="POST" action="{{ route('cv.upload') }}" enctype="multipart/form-data">
                            @csrf
                            @if(session('notification_success'))
                                <div class="k-notification k-notification-success">
                                    {{ session('notification_success') }}
                                </div>
                            @endif
                            <label id="drop-zone" for="resume" class="k-drop-zone">
                                <div class="k-drop-icon">
                                    <flux:icon.document-text />
                                </div>

                                <h2 class="k-drop-title">Upload your CV</h2>
                                <p class="k-drop-text">PDF or DOCX files are supported</p>

                                <div class="k-btn-primary">Choose File</div>

                                <input
                                    id="resume"
                                    name="resume"
                                    type="file"
                                    class="k-hidden"
                                    accept=".pdf,.docx"
                                    required
                                >
                            </label>

                            @error('resume')
                                <p id="resume-error" class="k-alert-error">{{ $message }}</p>
                            @enderror

                            <div id="file-preview" class="k-file-preview">
                                <div class="k-file-row">
                                    <div>
                                        <p class="k-file-label">Selected file</p>
                                        <p id="file-name" class="k-file-name"></p>
                                    </div>

                                    <button id="remove-file" type="button" class="k-btn-secondary">
                                        Remove
                                    </button>
                                </div>
                            </div>

                        
                            <div class="k-grid-cards" style="margin-top: 20px;">
                                <button id="submit-btn" type="submit" class="k-btn-primary" disabled>
                                    Generate My Twin
                                </button>

                                @if(auth()->user()->skills()->count() === 0)
                                    <a href="{{ route('cv.manual') }}" class="k-btn-secondary">
                                        I don’t have a CV
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{--
        Handles the client-side upload state:
        - shows the selected file name,
        - enables the submit button,
        - clears old validation errors when a new file is selected,
        - resets the form state when the file is removed or when returning from browser cache.
    --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('resume');
            const filePreview = document.getElementById('file-preview');
            const fileName = document.getElementById('file-name');
            const removeBtn = document.getElementById('remove-file');
            const dropZone = document.getElementById('drop-zone');
            const submitBtn = document.getElementById('submit-btn');

            function clearResumeError() {
                const resumeError = document.getElementById('resume-error');

                if (resumeError) {
                    resumeError.remove();
                }
            }

            function resetUploadState() {
                input.value = '';
                fileName.textContent = '';
                filePreview.classList.remove('is-visible');
                submitBtn.disabled = true;
                dropZone.classList.remove('is-active');
            }

            function updateUploadState() {
                clearResumeError();

                if (input.files.length > 0) {
                    fileName.textContent = input.files[0].name;
                    filePreview.classList.add('is-visible');
                    submitBtn.disabled = false;
                    dropZone.classList.add('is-active');
                }
            }

            input.addEventListener('change', updateUploadState);

            removeBtn.addEventListener('click', function () {
                resetUploadState();
                clearResumeError();
            });

            window.addEventListener('pageshow', function () {
                resetUploadState();
            });
        });
    </script>
</x-layouts::app>