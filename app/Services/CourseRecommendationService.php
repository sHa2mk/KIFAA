<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CourseRecommendationService
{
    /**
     * Gets course recommendations for one missing skill.
     *
     * The service first asks OpenAI for course candidates, then applies
     * two validation layers:
     * 1. Basic validation: free, direct URL, allowed platform, supported language.
     * 2. Strong relevance validation: title, reason, objectives, and outcomes match the missing skill.
     *
     * If no strongly matched course is found, the service returns the basically valid
     * courses instead of showing an empty result page.
     *
     * @param  string  $skillName
     * @param  string|null  $jobTitle
     * @return array
     */
    public function recommendForSkill(string $skillName, ?string $jobTitle = null): array
    {
        $cacheKey = 'courses_v6_' . md5($skillName . '_' . ($jobTitle ?? ''));

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($skillName, $jobTitle) {
            $rawCourses = $this->searchCourses($skillName, $jobTitle);

            $normalizedCourses = collect($rawCourses)
                ->map(fn ($course) => $this->normalizeCourse($course, $skillName))
                ->filter(fn ($course) => $this->isBasicallyValidCourse($course))
                ->unique(fn ($course) => strtolower($course['link'] ?? ''))
                ->values();

            $strongCourses = $normalizedCourses
                ->filter(fn ($course) => $this->courseStronglyMatchesSkill($course, $skillName))
                ->values();

            $courses = $strongCourses->isNotEmpty()
                ? $strongCourses
                : $normalizedCourses;

            return [
                'courses' => $courses
                    ->take(3)
                    ->toArray(),
            ];
        });
    }

    /**
     * Calls OpenAI with web search to find direct course links.
     *
     * @param  string  $skillName
     * @param  string|null  $jobTitle
     * @return array
     */
    private function searchCourses(string $skillName, ?string $jobTitle = null): array
    {
        $apiKey = config('services.openai.course_recommendation_key');
        $model = config('services.openai.model', 'gpt-4.1-mini');
        $url = config('services.openai.url', 'https://api.openai.com/v1/responses');

        if (! $apiKey) {
            throw new \RuntimeException('OPENAI_COURSE_RECOMMENDATION_KEY is missing.');
        }

        $prompt = $this->buildPrompt($skillName, $jobTitle);

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->contentType('application/json')
            ->timeout(60)
            ->post($url, [
                'model' => $model,
                'tools' => [
                    [
                        'type' => 'web_search_preview',
                    ],
                ],
                'input' => $prompt,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'OpenAI course recommendation failed: ' . $response->status() . ' - ' . $response->body()
            );
        }

        $text = $this->extractOutputText($response->json());

        return $this->parseJson($text);
    }

    /**
     * Builds the AI prompt used for course search.
     *
     * @param  string  $skillName
     * @param  string|null  $jobTitle
     * @return string
     */
    private function buildPrompt(string $skillName, ?string $jobTitle = null): string
    {
        return "
You are a course recommendation agent with web search.

Your task:
Find exactly 3 FREE courses ONLY for this missing skill: {$skillName}

Allowed platforms ONLY:
- Coursera
- LinkedIn Learning
- edX
- Microsoft Learn
- Google Skillshop
- FreeCodeCamp
- Kaggle Learn
- Doroob
- Ethraei

Allowed domains ONLY:
- coursera.org
- linkedin.com/learning
- edx.org
- learn.microsoft.com
- skillshop.withgoogle.com
- freecodecamp.org
- kaggle.com
- doroob.sa
- ethraei.sa

Strict price rules:
- Recommend FREE courses only.
- Do NOT recommend paid courses.
- Do NOT recommend subscription-only courses.
- Do NOT recommend free-trial-only courses.
- Do NOT recommend courses that require payment to access learning content.
- Coursera and edX are allowed ONLY if the course content can be audited for free.
- LinkedIn Learning is allowed ONLY if the course can be accessed for free or openly.
- The price field must always be exactly: Free.
- If you are not sure the course is free, reject it.

Language rules:
- Recommend courses in English or Arabic only.
- Do NOT recommend courses in French, Spanish, or any other language.
- The language field must be either English or Arabic.

Skill relevance rules:
- The course must directly develop this missing skill: {$skillName}
- Do not judge relevance by the course title only.
- Check the course title, objectives, learning outcomes, syllabus, and description.
- The course is valid if its objectives, outcomes, syllabus, or description directly develop the missing skill, even if the title uses different wording.
- The course title, objectives, outcomes, syllabus, or description must contain the missing skill, a close synonym, or a direct practical equivalent.
- The reason must explicitly mention which objective, outcome, syllabus topic, or description point supports the missing skill.
- Do NOT recommend unrelated soft-skill, language-learning, career-advice, or general-purpose courses unless the missing skill itself is about that topic.
- Do NOT recommend full career paths.
- Do NOT recommend bootcamps.
- Do NOT recommend broad programs, specializations, or certificates unless a specific course page directly teaches {$skillName}.
- Do NOT recommend a broader technology, advanced version, framework, or related topic unless its objectives or outcomes directly teach {$skillName}.
- If the missing skill is a soft skill, professional skill, ethical skill, analytical skill, or general competency, recommend courses that directly develop that competency even if the exact skill wording is not in the course title.
- Treat words such as basic, basics, beginner, beginners, fundamentals, introduction, intro, skill, skills, knowledge, judgment, ability, competency, awareness, understanding, and proficiency as descriptive words, not required title words.
- If you cannot find 3 free direct courses that match the skill based on title, objectives, outcomes, syllabus, or description, return fewer valid courses instead of adding weak, paid, or unrelated results.

Important:
- The job title is only context: " . ($jobTitle ?? 'Not provided') . "
- Do NOT recommend courses based mainly on the job title.
- Return direct course page URLs only.
- Do not return search result pages.
- Do not return homepage links.
- Do not return category pages.
- Reject any course outside the allowed platforms/domains.
- Return JSON only.
- Do not add markdown.
- Do not add explanations outside JSON.

JSON format:
[
  {
    \"title\": \"Course title\",
    \"platform\": \"Coursera | LinkedIn Learning | edX | Microsoft Learn | Google Skillshop | FreeCodeCamp | Kaggle Learn | Doroob | Ethraei\",
    \"link\": \"https://direct-course-url\",
    \"price\": \"Free\",
    \"language\": \"English | Arabic\",
    \"objectives\": [
      \"Course objective 1\",
      \"Course objective 2\"
    ],
    \"outcomes\": [
      \"Learning outcome 1\",
      \"Learning outcome 2\"
    ],
    \"reason\": \"Explain how the objectives and outcomes directly improve {$skillName}\"
  }
]
";
    }

    /**
     * Extracts output text from the OpenAI Responses API structure.
     *
     * @param  array  $responseJson
     * @return string
     */
    private function extractOutputText(array $responseJson): string
    {
        if (! empty($responseJson['output_text'])) {
            return trim((string) $responseJson['output_text']);
        }

        $text = '';

        foreach ($responseJson['output'] ?? [] as $item) {
            foreach ($item['content'] ?? [] as $contentItem) {
                if (($contentItem['type'] ?? null) === 'output_text') {
                    $text .= (string) ($contentItem['text'] ?? '');
                }
            }
        }

        return trim($text);
    }

    /**
     * Parses the AI response into a PHP array.
     *
     * @param  string  $text
     * @return array
     */
    private function parseJson(string $text): array
    {
        $text = trim(str_replace(['```json', '```'], '', $text));

        $decoded = json_decode($text, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return [];
    }

    /**
     * Converts one course item into the structure expected by the controller and Blade.
     *
     * @param  array  $course
     * @param  string  $skillName
     * @return array
     */
    private function normalizeCourse(array $course, string $skillName): array
    {
        $title = trim((string) ($course['title'] ?? 'Course Recommendation'));
        $platform = trim((string) ($course['platform'] ?? 'Online'));
        $link = trim((string) ($course['link'] ?? $course['url'] ?? '#'));
        $price = trim((string) ($course['price'] ?? ''));
        $language = trim((string) ($course['language'] ?? ''));
        $reason = trim((string) ($course['reason'] ?? ''));

        $objectives = $this->normalizeListField($course['objectives'] ?? []);
        $outcomes = $this->normalizeListField($course['outcomes'] ?? []);

        $platformMap = [
            'linkedin' => 'LinkedIn Learning',
            'linkedin learning' => 'LinkedIn Learning',
            'microsoft' => 'Microsoft Learn',
            'microsoft learn' => 'Microsoft Learn',
            'google skillshop' => 'Google Skillshop',
            'skillshop' => 'Google Skillshop',
            'freecodecamp' => 'FreeCodeCamp',
            'free code camp' => 'FreeCodeCamp',
            'kaggle' => 'Kaggle Learn',
            'kaggle learn' => 'Kaggle Learn',
            'edx' => 'edX',
            'coursera' => 'Coursera',
            'doroob' => 'Doroob',
            'ethraei' => 'Ethraei',
        ];

        $platformKey = strtolower($platform);
        $platform = $platformMap[$platformKey] ?? $platform;

        $priceText = strtolower($price);

        if ($priceText === 'free' || Str::contains($priceText, ['free audit', 'audit for free'])) {
            $price = 'Free';
        }

        $language = match (strtolower($language)) {
            'arabic', 'ar' => 'Arabic',
            'english', 'en' => 'English',
            default => $language,
        };

        return [
            'title' => $title,
            'platform' => $platform,
            'link' => $link,
            'price' => $price,
            'language' => $language,
            'objectives' => $objectives,
            'outcomes' => $outcomes,
            'reason' => $reason ?: "This course helps improve {$skillName}.",
            'icon' => 'fa-graduation-cap',
        ];
    }

    /**
     * Normalizes objectives and outcomes into clean arrays.
     *
     * @param  mixed  $value
     * @return array
     */
    private function normalizeListField($value): array
    {
        if (is_string($value)) {
            $value = [$value];
        }

        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Checks basic validity only: URL, price, language, domain, and availability.
     *
     * This function intentionally does not check strong skill relevance.
     * Strong relevance is handled separately by courseStronglyMatchesSkill().
     *
     * @param  array  $course
     * @return bool
     */
    private function isBasicallyValidCourse(array $course): bool
    {
        if (empty($course['title'])) {
            return false;
        }

        if (empty($course['link'])) {
            return false;
        }

        if (! filter_var($course['link'], FILTER_VALIDATE_URL)) {
            return false;
        }

        if (strtolower(trim($course['price'] ?? '')) !== 'free') {
            return false;
        }

        $language = strtolower(trim($course['language'] ?? ''));

        if ($language !== '' && ! in_array($language, ['english', 'arabic'])) {
            return false;
        }

        $link = strtolower(trim($course['link']));
        $host = parse_url($link, PHP_URL_HOST);

        if (! $host) {
            return false;
        }

        $host = str_replace('www.', '', strtolower($host));

        if (! $this->isAllowedDomain($host)) {
            return false;
        }

        if (! $this->isDirectCourseUrl($host, $link)) {
            return false;
        }

        $blockedWords = [
            'search',
            'query=',
            'keywords=',
            '/courses?search',
            '/search?',
            '/browse',
            '/category',
            '404',
            '403',
            'not-found',
        ];

        foreach ($blockedWords as $word) {
            if (Str::contains($link, $word)) {
                return false;
            }
        }

        if (! $this->urlLooksAvailable($link)) {
            return false;
        }

        return true;
    }

    /**
     * Compatibility wrapper.
     *
     * If another controller or test still calls isValidCourse internally,
     * it will keep the stricter behavior.
     *
     * @param  array  $course
     * @param  string  $skillName
     * @return bool
     */
    private function isValidCourse(array $course, string $skillName): bool
    {
        return $this->isBasicallyValidCourse($course)
            && $this->courseStronglyMatchesSkill($course, $skillName);
    }

    /**
     * Checks whether the host is part of the approved learning platforms.
     *
     * @param  string  $host
     * @return bool
     */
    private function isAllowedDomain(string $host): bool
    {
        $allowedDomains = [
            'coursera.org',
            'linkedin.com',
            'edx.org',
            'learn.microsoft.com',
            'skillshop.withgoogle.com',
            'freecodecamp.org',
            'kaggle.com',
            'doroob.sa',
            'ethraei.sa',
        ];

        return collect($allowedDomains)->contains(function ($domain) use ($host) {
            return $host === $domain || str_ends_with($host, '.' . $domain);
        });
    }

    /**
     * Checks whether the URL looks like a direct course or learning page.
     *
     * @param  string  $host
     * @param  string  $link
     * @return bool
     */
    private function isDirectCourseUrl(string $host, string $link): bool
    {
        if (Str::contains($host, 'coursera.org')) {
            return Str::contains($link, '/learn/');
        }

        if (Str::contains($host, 'linkedin.com')) {
            return Str::contains($link, 'linkedin.com/learning/');
        }

        if (Str::contains($host, 'edx.org')) {
            return Str::contains($link, ['/learn/', '/course/']);
        }

        if (Str::contains($host, 'learn.microsoft.com')) {
            return Str::contains($link, ['/training/modules/', '/training/paths/']);
        }

        if (Str::contains($host, 'freecodecamp.org')) {
            return Str::contains($link, '/learn/');
        }

        if (Str::contains($host, 'kaggle.com')) {
            return Str::contains($link, 'kaggle.com/learn');
        }

        if (Str::contains($host, 'skillshop.withgoogle.com')) {
            return true;
        }

        if (Str::contains($host, 'doroob.sa') || Str::contains($host, 'ethraei.sa')) {
            return true;
        }

        return false;
    }

    /**
     * Checks whether the full course evidence matches the selected missing skill.
     *
     * Evidence includes:
     * - title
     * - reason
     * - objectives
     * - outcomes
     *
     * @param  array  $course
     * @param  string  $skillName
     * @return bool
     */
    private function courseStronglyMatchesSkill(array $course, string $skillName): bool
    {
        $skill = $this->normalizeText($skillName);

        if ($skill === '') {
            return false;
        }

        $title = $this->normalizeText($course['title'] ?? '');
        $reason = $this->normalizeText($course['reason'] ?? '');
        $objectives = $this->normalizeText(implode(' ', $course['objectives'] ?? []));
        $outcomes = $this->normalizeText(implode(' ', $course['outcomes'] ?? []));

        $evidenceText = trim($title . ' ' . $reason . ' ' . $objectives . ' ' . $outcomes);

        return $this->courseMatchesSkill($evidenceText, $reason, $skill);
    }

    /**
     * Checks whether the evidence text matches the selected missing skill.
     *
     * This method is generic and reusable across technical, business, legal,
     * design, marketing, and soft-skill backgrounds.
     *
     * @param  string  $evidenceText
     * @param  string  $reason
     * @param  string  $skillName
     * @return bool
     */
    private function courseMatchesSkill(string $evidenceText, string $reason, string $skillName): bool
    {
        if ($skillName === '') {
            return false;
        }

        if ($this->containsPhrase($evidenceText, $skillName)) {
            return true;
        }

        foreach ($this->skillAliases($skillName) as $alias) {
            if ($this->containsPhrase($evidenceText, $alias)) {
                return true;
            }
        }

        $tokens = $this->importantSkillTokens($skillName);

        if ($tokens->isEmpty()) {
            return false;
        }

        $matchedTokens = $tokens
            ->filter(fn ($token) => $this->containsPhrase($evidenceText, $token))
            ->count();

        if ($tokens->count() === 1 && $matchedTokens === 1) {
            return true;
        }

        if ($matchedTokens >= min(2, $tokens->count())) {
            return true;
        }

        if ($matchedTokens >= 1 && $this->containsAnyLearningEvidence($reason)) {
            return true;
        }

        return false;
    }

    /**
     * Normalizes text for safer matching.
     *
     * @param  string  $text
     * @return string
     */
    private function normalizeText(string $text): string
    {
        $text = strtolower(trim($text));
        $text = str_replace(['-', '_', '/', '\\', '&'], ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * Checks a phrase as a full phrase, not inside another word.
     *
     * Example: css should not match css3.
     *
     * @param  string  $text
     * @param  string  $phrase
     * @return bool
     */
    private function containsPhrase(string $text, string $phrase): bool
    {
        $text = $this->normalizeText($text);
        $phrase = $this->normalizeText($phrase);

        if ($phrase === '') {
            return false;
        }

        return preg_match(
            '/(?<![a-z0-9])' . preg_quote($phrase, '/') . '(?![a-z0-9])/i',
            $text
        ) === 1;
    }

    /**
     * Extracts important tokens from a skill name.
     *
     * @param  string  $skillName
     * @return \Illuminate\Support\Collection
     */
    private function importantSkillTokens(string $skillName)
    {
        $stopWords = [
            // connectors
            'and',
            'or',
            'the',
            'a',
            'an',
            'for',
            'with',
            'in',
            'on',
            'of',
            'to',
            'by',
            'from',
            'using',

            // level words
            'basic',
            'basics',
            'beginner',
            'beginners',
            'intro',
            'introduction',
            'introductory',
            'fundamental',
            'fundamentals',
            'foundation',
            'foundations',
            'intermediate',
            'advanced',

            // generic skill words
            'skill',
            'skills',
            'knowledge',
            'ability',
            'abilities',
            'competency',
            'competencies',
            'capability',
            'capabilities',
            'understanding',
            'awareness',
            'proficiency',
            'experience',
            'practice',
            'practices',

            // broad professional words
            'judgment',
            'judgement',
            'learning',
            'training',
        ];

        return collect(explode(' ', $this->normalizeText($skillName)))
            ->map(fn ($word) => trim($word))
            ->filter(fn ($word) => strlen($word) >= 2)
            ->reject(fn ($word) => in_array($word, $stopWords))
            ->values();
    }

    /**
     * Provides common aliases for well-known skill names.
     *
     * This list is not the only matching mechanism. It only improves matching
     * for common abbreviations and alternative names.
     *
     * @param  string  $skillName
     * @return array
     */
    private function skillAliases(string $skillName): array
    {
        $skill = $this->normalizeText($skillName);

        $aliases = [
            // Programming and software
            'object oriented' => ['oop', 'object oriented programming', 'object oriented design'],
            'object oriented programming' => ['oop', 'object oriented', 'object oriented design'],
            'oop' => ['object oriented', 'object oriented programming'],

            'javascript' => ['js', 'java script'],
            'js' => ['javascript', 'java script'],
            'typescript' => ['ts', 'type script'],
            'python' => ['python programming'],
            'java' => ['java programming'],
            'php' => ['php programming'],
            'c sharp' => ['c#', 'csharp'],
            'c#' => ['c sharp', 'csharp'],
            'c plus plus' => ['c++', 'cplusplus'],
            'c++' => ['c plus plus', 'cplusplus'],

            // Web development
            'html' => ['html5', 'hypertext markup language'],
            'css' => ['css fundamentals', 'css basics'],
            'react' => ['react js', 'reactjs'],
            'react js' => ['react', 'reactjs'],
            'vue' => ['vue js', 'vuejs'],
            'node' => ['node js', 'nodejs'],
            'node js' => ['node', 'nodejs'],
            'laravel' => ['laravel framework'],
            'bootstrap' => ['bootstrap css'],
            'tailwind' => ['tailwind css'],

            // Databases
            'sql' => ['structured query language'],
            'mysql' => ['my sql'],
            'postgresql' => ['postgres', 'postgre sql'],
            'mongodb' => ['mongo db', 'mongo'],

            // Data and AI
            'machine learning' => ['ml'],
            'ml' => ['machine learning'],
            'artificial intelligence' => ['ai'],
            'ai' => ['artificial intelligence'],
            'deep learning' => ['dl'],
            'data analysis' => ['data analytics'],
            'data analytics' => ['data analysis'],
            'data visualization' => ['data visualisation'],
            'natural language processing' => ['nlp'],
            'nlp' => ['natural language processing'],

            // Cloud and DevOps
            'aws' => ['amazon web services'],
            'amazon web services' => ['aws'],
            'azure' => ['microsoft azure'],
            'google cloud' => ['gcp', 'google cloud platform'],
            'gcp' => ['google cloud', 'google cloud platform'],
            'docker' => ['containerization', 'containers'],
            'kubernetes' => ['k8s'],
            'k8s' => ['kubernetes'],
            'ci cd' => ['ci/cd', 'continuous integration', 'continuous deployment'],
            'devops' => ['development operations'],

            // Cybersecurity
            'cybersecurity' => ['cyber security', 'information security'],
            'cyber security' => ['cybersecurity', 'information security'],
            'network security' => ['cybersecurity fundamentals'],
            'ethical hacking' => ['penetration testing'],
            'penetration testing' => ['ethical hacking', 'pentesting'],
            'pentesting' => ['penetration testing', 'ethical hacking'],

            // Business and management
            'project management' => ['pm', 'project planning'],
            'agile' => ['agile methodology', 'agile project management'],
            'scrum' => ['scrum framework'],
            'requirements analysis' => ['requirements engineering'],
            'business analysis' => ['business analytics'],

            // Marketing
            'seo' => [
                'search engine optimization',
                'seo fundamentals',
                'seo basics',
                'seo for beginners',
                'search engine optimization fundamentals',
            ],
            'basic seo knowledge' => [
                'seo',
                'search engine optimization',
                'seo fundamentals',
                'seo basics',
                'seo for beginners',
                'search engine optimization fundamentals',
            ],
            'search engine optimization' => ['seo'],

            // Law and ethics
            'ethical judgment' => [
                'ethics',
                'professional ethics',
                'business ethics',
                'ethical decision making',
                'ethical decision-making',
                'ethical reasoning',
            ],
            'ethics' => [
                'ethical judgment',
                'professional ethics',
                'business ethics',
                'ethical decision making',
                'ethical reasoning',
            ],
            'legal research' => [
                'legal writing',
                'legal analysis',
                'legal reasoning',
            ],

            // Soft and professional skills
            'communication' => ['communication skills', 'effective communication'],
            'communication skills' => ['communication', 'effective communication', 'professional communication'],
            'teamwork' => ['team collaboration', 'collaboration', 'working in teams'],
            'leadership' => ['leadership skills', 'team leadership'],
            'leadership skills' => ['leadership', 'team leadership', 'management skills'],
            'problem solving' => ['problem-solving', 'critical thinking', 'analytical thinking'],
            'critical thinking' => ['problem solving', 'analytical thinking'],
            'analytical skills' => ['critical thinking', 'analytical thinking', 'analysis', 'data analysis'],
            'time management' => ['productivity', 'personal productivity'],
            'presentation' => ['presentation skills', 'public speaking'],
            'public speaking' => ['presentation skills'],
        ];

        return $aliases[$skill] ?? [];
    }

    /**
     * Checks whether the reason contains learning-related support text.
     *
     * @param  string  $text
     * @return bool
     */
    private function containsAnyLearningEvidence(string $text): bool
    {
        $learningWords = [
            'objective',
            'outcome',
            'learn',
            'develop',
            'improve',
            'practice',
            'apply',
            'understand',
            'analyze',
            'analyse',
            'evaluate',
            'create',
            'build',
            'design',
            'manage',
        ];

        foreach ($learningWords as $word) {
            if ($this->containsPhrase($text, $word)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether the course URL appears available.
     *
     * Some platforms may block server-side requests with 403 or 429, so only
     * clear 404 or 410 responses are rejected.
     *
     * @param  string  $link
     * @return bool
     */
    private function urlLooksAvailable(string $link): bool
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ])
                ->get($link);

            if (in_array($response->status(), [404, 410])) {
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            return true;
        }
    }
}