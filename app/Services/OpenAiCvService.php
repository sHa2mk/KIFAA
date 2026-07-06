<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenAiCvService
{
    /**
     * Analyzes extracted CV text and returns a job title with a clean skill list.
     *
     * The method sends the extracted CV text to OpenAI using structured output.
     * The response is expected to contain only a job title and an array of skills.
     * The returned skills are cleaned, lightly normalized, de-duplicated, and
     * returned to the controller.
     *
     * @param  string  $cvText
     * @return array
     */
    public function analyzeCvText(string $cvText): array
    {
        $apiKey = config('services.openai.cv_key');
        $model = config('services.openai.model');

        $payload = [
            'model' => $model,
            'temperature' => 0,
            'input' => [
                [
                    'role' => 'system',
                    'content' =>
                        "You are an expert CV parser. Extract a concise job title and a clean list of skills.\n" .
                        "Rules:\n" .
                        "- job_title: short and common (e.g. 'Data Analyst', 'Backend Developer').\n" .
                        "- skills: array of unique skill names (strings). No duplicates. No additional skills.\n" .
                        "- Keep skills generic (e.g. 'Laravel', 'SQL', 'REST APIs').",
                ],
                [
                    'role' => 'user',
                    'content' => "CV TEXT:\n\n" . $cvText,
                ],
            ],
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'cv_analysis',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'job_title' => [
                                'type' => 'string',
                            ],
                            'skills' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                        'required' => ['job_title', 'skills'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ];

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->contentType('application/json')
            ->post('https://api.openai.com/v1/responses', $payload);

        if (! $response->successful()) {
            throw new \RuntimeException(
                "OpenAI failed: {$response->status()} - " . $response->body()
            );
        }

        $data = $response->json();

        $jsonString = $this->extractOutputText($data);

        $parsed = json_decode($jsonString, true);

        if (
            ! is_array($parsed) ||
            ! isset($parsed['job_title'], $parsed['skills']) ||
            ! is_array($parsed['skills'])
        ) {
            throw new \RuntimeException('OpenAI returned invalid structured output: ' . $jsonString);
        }

        $jobTitle = trim((string) $parsed['job_title']);

        $skills = collect($parsed['skills'])
            ->filter(fn ($skill) => is_string($skill) && trim($skill) !== '')
            ->map(function ($skill) {
                $skill = trim($skill);
                $skill = preg_replace('/\s+/', ' ', $skill);

                $normalized = mb_strtolower($skill);

                $aliases = [
                    'js' => 'JavaScript',
                    'ui ux' => 'UI/UX',
                    'ux ui' => 'UI/UX',
                    'ml' => 'Machine Learning',
                ];

                return $aliases[$normalized] ?? $skill;
            })
            ->unique(fn ($skill) => mb_strtolower($skill))
            ->values()
            ->all();

        return [
            'job_title' => $jobTitle,
            'skills' => $skills,
        ];
    }

    /**
     * Extracts output text from the OpenAI Responses API structure.
     *
     * The structured JSON text is returned inside an output_text item. This
     * helper keeps response parsing separate from the CV analysis request.
     *
     * @param  array  $responseJson
     * @return string
     */
    private function extractOutputText(array $responseJson): string
    {
        $output = $responseJson['output'] ?? [];

        foreach ($output as $item) {
            if (($item['type'] ?? null) === 'message') {
                $content = $item['content'] ?? [];

                foreach ($content as $contentItem) {
                    if (($contentItem['type'] ?? null) === 'output_text') {
                        return (string) ($contentItem['text'] ?? '');
                    }
                }
            }
        }

        throw new \RuntimeException('OpenAI response missing output_text.');
    }
}