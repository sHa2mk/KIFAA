<?php

namespace App\Http\Controllers;

use App\Models\Interest;
use App\Models\Skill;
use App\Services\MarketSkillAiService;
use App\Services\OpenAiCvService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CvController extends Controller
{
    /**
     * Creates a new CV controller instance.
     *
     * @param  \App\Services\OpenAiCvService  $openAiCvService
     * @param  \App\Services\MarketSkillAiService  $marketSkillAiService
     * @return void
     */
    public function __construct(
        private OpenAiCvService $openAiCvService,
        private MarketSkillAiService $marketSkillAiService
    ) {}

    /**
     * Sends the user to the correct first step in the CV flow.
     *
     * Users who already have a selected target role are redirected to the
     * preview page. New users are redirected to the CV upload page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->interest_id) {
            return redirect()->route('cv.preview');
        }

        return redirect()->route('cv.upload.form');
    }

    /**
     * Shows the CV upload page.
     *
     * Users who already have extracted skills are redirected to the dashboard
     * unless they intentionally choose to re-analyze their CV.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function showUpload(Request $request)
    {
        $user = $request->user();

        if ($user->skills()->count() > 0 && ! $request->boolean('reanalyze')) {
            return redirect()->route('dashboard');
        }

        if ($user->interest_id) {
            session(['cv.from_edit' => true]);
        }

        return view('cv.upload_cv');
    }

    /**
     * Starts the manual profile flow without uploading a CV.
     *
     * Empty values are stored in the session so the same preview page can be
     * reused for both manual and CV-based profile creation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function manual(Request $request)
    {
        session([
            'cv.preview.job_title' => '',
            'cv.preview.skills' => [],
            'cv.preview.file_path' => null,
            'cv.preview.source' => 'manual',
        ]);

        return redirect()->route('cv.preview');
    }

    /**
     * Uploads the CV, extracts text, and stores the AI result temporarily.
     *
     * The uploaded file is validated before processing. Then, readable text is
     * extracted from the file, checked using a resume-likelihood heuristic, and
     * sent to the AI service for skill and target-role extraction. The extracted
     * data is saved in the session so the user can review it before confirming.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function upload(Request $request)
    {
        try {
            $request->validate(
                [
                    'resume' => 'required|file|mimes:pdf,docx|max:5120',
                ],
                [
                    'resume.required' => 'Please upload your CV file.',
                    'resume.uploaded' => 'Please make sure it is not larger than 5 MB.',
                    'resume.file' => 'The uploaded file is invalid.',
                    'resume.mimes' => 'Only PDF and DOCX files are supported.',
                    'resume.max' => 'Please make sure it is not larger than 5 MB.',
                ]
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        }

        $file = $request->file('resume');
        $path = $file->store('cvs');
        $extension = strtolower($file->getClientOriginalExtension());

        try {
            $text = $this->extractCvText(Storage::path($path), $extension);

            $resumeCheck = $this->evaluateResumeLikelihood($text);

            if ($resumeCheck['decision'] === 'reject') {
                Storage::delete($path);

                return back()
                    ->withErrors(['resume' => $resumeCheck['reason']])
                    ->withInput();
            }

            $startTime = microtime(true);

            $analysis = $this->openAiCvService->analyzeCvText($text);

            $executionTime = microtime(true) - $startTime;

            logger('CV Analysis and Skill Extraction Execution Time: ' . round($executionTime, 2) . ' seconds');
        } catch (\Throwable $e) {
            Storage::delete($path);

            return back()
                ->withErrors([
                    'resume' => 'Resume processing failed. Please check the file content and try again.',
                ])
                ->withInput();
        }

        session([
            'cv.preview.job_title' => $analysis['job_title'] ?? '',
            'cv.preview.skills' => $analysis['skills'] ?? [],
            'cv.preview.file_path' => $path,
            'cv.preview.source' => 'cv',
        ]);

        return redirect()
            ->route('cv.preview')
            ->with('notification_success', 'Resume uploaded and analyzed successfully.');
    }

    /**
     * Evaluates whether the extracted text appears to be a valid CV or resume.
     *
     * This method uses a rule-based scoring heuristic. The score increases when
     * the extracted text contains enough readable content, contact information,
     * common resume sections, and year patterns.
     *
     * @param  string  $text
     * @return array
     */
    private function evaluateResumeLikelihood(string $text): array
    {
        $text = trim($text);

        if ($text === '') {
            return [
                'decision' => 'reject',
                'reason' => 'The document is empty or unreadable.',
            ];
        }

        $lower = mb_strtolower($text);
        $score = 0;

        if (mb_strlen($text) >= 200) {
            $score += 2;
        }

        $hasEmail = preg_match(
            '/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i',
            $text
        );

        $hasPhone = preg_match(
            '/\+?\d[\d\s\-()]{7,}/',
            $text
        );

        if ($hasEmail) {
            $score += 3;
        }

        if ($hasPhone) {
            $score += 2;
        }

        $sections = [
            'experience',
            'education',
            'skills',
            'projects',
            'summary',
            'profile',
            'objective',
            'certifications',
            'courses',
            'languages',
            'internship',
            'volunteer',
            'employment',
            'work history',
            'research',
            'publications',
            'training',
        ];

        $matches = 0;

        foreach ($sections as $section) {
            if (str_contains($lower, $section)) {
                $matches++;
            }
        }

        $score += $matches;

        $hasDates = preg_match('/(19|20)\d{2}/', $text);

        if ($hasDates) {
            $score += 2;
        }
        
        $coreSections = [
            'summary',
            'profile',
            'objective',
            'experience',
            'work experience',
            'employment',
            'education',
            'skills',
            'projects',
            'certifications',
        ];
        
        $coreMatches = 0;
        
        foreach ($coreSections as $section) {
            if (str_contains($lower, $section)) {
                $coreMatches++;
            }
        }
        
        if (
            ($score >= 7 && ($hasEmail || $hasPhone) && $matches >= 2) ||
            ($coreMatches >= 3 && mb_strlen($text) >= 150 && ($hasEmail || $hasPhone || $hasDates))
        )  {
            return [
                'decision' => 'accept',
                'reason' => 'Resume detected successfully.',
                'score' => $score,
            ];
        }
        
        return [
            'decision' => 'reject',
            'reason' => 'The uploaded file does not appear to be a valid CV or resume.',
            'score' => $score,
        ];
    }

    /**
     * Shows the preview page before saving the career profile.
     *
     * Session data is used first. If there is no session data, the saved user
     * skills and selected interest are used as fallback values.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function preview(Request $request)
    {
        $user = $request->user()->load('skills', 'interest');

        $source = session('cv.preview.source', 'cv');
        $skills = session('cv.preview.skills');

        if (empty($skills)) {
            $skills = $user->skills->pluck('name')->toArray();
        }

        $jobTitle = session('cv.preview.job_title');

        if (empty($jobTitle)) {
            $jobTitle = optional($user->interest)->title;
        }

        return view('cv.preview', [
            'jobTitle' => $jobTitle,
            'skills' => $skills,
            'source' => $source,
            'isManual' => $source === 'manual',
        ]);
    }

    /**
     * Saves the confirmed profile and generates missing skills.
     *
     * The confirmed job title and skills are saved permanently. After saving the
     * user's current skills, the system generates missing skills for the selected
     * target role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'job_title' => 'required|string|max:255',
            'skills_text' => 'required|string',
        ]);

        $user = $request->user();
        $jobTitle = trim($request->input('job_title'));

        $skills = preg_split("/\r\n|\n|\r/", trim($request->input('skills_text'))) ?: [];

       $skills = collect($skills)
    ->map(fn ($skill) => trim($skill))
    ->filter()
    ->unique(fn ($skill) => mb_strtolower($skill))
    ->values()
    ->all();

        DB::transaction(function () use ($user, $jobTitle, $skills) {
            if (Schema::hasColumn('users', 'job_title')) {
                $user->job_title = $jobTitle;
            }

            if (Schema::hasTable('interests') && Schema::hasColumn('users', 'interest_id')) {
                $interest = Interest::firstOrCreate([
                    'title' => $jobTitle,
                ]);

                $user->interest_id = $interest->id;
            }

            $user->save();

            $skillIds = [];

            foreach ($skills as $name) {
                $skillIds[] = Skill::firstOrCreate([
                    'name' => $name,
                ])->id;
            }

            if (method_exists($user, 'skills')) {
                $user->skills()->sync($skillIds);            }

            $startTime = microtime(true);

            app(MissingSkillController::class)
                ->generate($user, $jobTitle);

            $executionTime = microtime(true) - $startTime;

            logger('Skill Gap Analysis and Missing Skills Generation Execution Time: ' . round($executionTime, 2) . ' seconds');
        });

        session([
            'cv.preview.job_title' => $jobTitle,
            'cv.preview.skills' => $skills,
        ]);

        session()->forget('cv.preview.source');

        return redirect()
            ->route('dashboard')
            ->with('success', 'Information saved successfully ✅');
    }

    /**
     * Extracts readable text from the uploaded CV file.
     *
     * The file extension determines whether the uploaded document should be
     * processed as a PDF or DOCX file.
     *
     * @param  string  $absolutePath
     * @param  string  $extension
     * @return string
     */
    private function extractCvText(string $absolutePath, string $extension): string
    {
        if (! is_file($absolutePath)) {
            throw new \RuntimeException('CV file not found.');
        }

        return match ($extension) {
            'pdf' => $this->extractPdfText($absolutePath),
            'docx' => $this->extractDocxText($absolutePath),
            default => throw new \RuntimeException('Unsupported CV file type. Please upload a PDF or DOCX file.'),
        };
    }

    /**
     * Extracts readable text from a PDF file.
     *
     * Spatie PDF-to-text is used first when available. If it cannot extract text,
     * Smalot PDF Parser is used as a fallback parser.
     *
     * @param  string  $absolutePath
     * @return string
     */
    private function extractPdfText(string $absolutePath): string
    {
        if (class_exists(\Spatie\PdfToText\Pdf::class)) {
            $text = trim((string) \Spatie\PdfToText\Pdf::getText($absolutePath));

            if ($text !== '') {
                return $text;
            }
        }

        if (class_exists(\Smalot\PdfParser\Parser::class)) {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($absolutePath);

            $text = trim((string) $pdf->getText());

            if ($text !== '') {
                return $text;
            }
        }

        throw new \RuntimeException('Could not extract text from the PDF file.');
    }

    /**
     * Extracts readable text from a DOCX file.
     *
     * DOCX files are zip-based XML documents. The main text is read from
     * word/document.xml, cleaned from XML tags, and normalized before processing.
     *
     * @param  string  $absolutePath
     * @return string
     */
    private function extractDocxText(string $absolutePath): string
    {
        $zip = new \ZipArchive();

        if ($zip->open($absolutePath) !== true) {
            throw new \RuntimeException('Could not open the DOCX file.');
        }

        $documentXml = $zip->getFromName('word/document.xml');

        $zip->close();

        if ($documentXml === false) {
            throw new \RuntimeException('Could not read text from the DOCX file.');
        }

        $documentXml = str_replace(
            ['</w:p>', '<w:br/>', '<w:tab/>'],
            ' ',
            $documentXml
        );

        $text = html_entity_decode(
            strip_tags($documentXml),
            ENT_QUOTES | ENT_XML1,
            'UTF-8'
        );

        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if ($text === '') {
            throw new \RuntimeException('Could not extract text from the DOCX file.');
        }

        return $text;
    }

    /**
     * Clears the CV profile data used by the current flow.
     *
     * The method removes all linked skills and clears the selected career
     * interest so the user can rebuild the career profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function reset(Request $request)
    {
        $user = $request->user();

        DB::table('skill_user')
            ->where('user_id', $user->id)
            ->delete();

        $user->interest_id = null;
        $user->save();

        return redirect()->route('dashboard');
    }
}