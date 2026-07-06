<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MarketSkillAiService
{
    /**
     * Generates realistic market skills for a target job title.
     *
     * The method sends the target job title to OpenAI and expects a JSON array
     * containing skill names, priority levels, and short priority reasons.
     * The returned data is validated, cleaned, and normalized before being used
     * in the skill gap analysis.
     *
     * @param  string  $jobTitle
     * @return array
     */
    public function generateMarketSkills(string $jobTitle): array
    {
        $prompt = "
You are a strict job market expert.

Job Title: {$jobTitle}

Step 1:
List 15 commonly required skills (technical and soft).

Step 2:
Remove:
- Rare skills
- Uncommon tools
- Overly generic or vague skills

Step 3:
For each skill, assign a priority:

- high → core skill strongly required for the job
- medium → important supporting skill
- low → useful but not critical skill

Step 4:
Return ONLY the 15 most essential and realistic skills with priority.

Rules:
- Output MUST start with [ and end with ]
- Must reflect real job postings
- Include both technical and soft skills
- No explanations outside JSON
- No extra words
- priority MUST be: high, medium, or low
- Keep reason short and practical

JSON format example:
[
  {\"name\":\"PHP\",\"priority\":\"high\",\"reason\":\"Core backend language\"},
  {\"name\":\"Laravel\",\"priority\":\"high\",\"reason\":\"Primary framework for this role\"},
  {\"name\":\"Docker\",\"priority\":\"medium\",\"reason\":\"Used in deployment workflows\"}
]
";

        $response = Http::withToken(config('services.openai.market_key'))
            ->acceptJson()
            ->contentType('application/json')
            ->post('https://api.openai.com/v1/responses', [
                'model' => config('services.openai.model'),
                'input' => $prompt,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'OpenAI market skill error: ' .
                $response->status() .
                ' - ' .
                $response->body()
            );
        }

        $data = $response->json();

        $text = trim($this->extractOutputText($data));

        if (! str_starts_with($text, '[')) {
            preg_match('/\[(.*?)\]/s', $text, $matches);
            $text = $matches[0] ?? $text;
        }

        $skills = json_decode($text, true);

        if (! is_array($skills)) {
            throw new \RuntimeException('Invalid AI JSON: ' . $text);
        }

        return collect($skills)
            ->filter(function ($item) {
                return is_array($item)
                    && isset($item['name'])
                    && is_string($item['name'])
                    && trim($item['name']) !== '';
            })
            ->map(function ($item) {
                $priority = strtolower(trim($item['priority'] ?? 'medium'));

                if (! in_array($priority, ['high', 'medium', 'low'], true)) {
                    $priority = 'medium';
                }

                $reason = $item['reason'] ?? null;

                return [
                    'name' => trim($item['name']),
                    'priority' => $priority,
                    'priority_reason' => is_string($reason) && trim($reason) !== ''
                        ? trim($reason)
                        : 'Important skill for the target role',
                ];
            })
            ->unique(fn ($item) => mb_strtolower($item['name']))
            ->values()
            ->all();
    }

    /**
     * Extracts output text from the OpenAI Responses API structure.
     *
     * The response may contain multiple output items. This method searches for
     * the first message content item with the output_text type and returns its
     * generated text.
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