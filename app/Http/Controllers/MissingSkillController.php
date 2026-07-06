<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Services\MarketSkillAiService;
use Illuminate\Support\Facades\DB;

class MissingSkillController extends Controller
{
    /**
     * Creates a new missing skill controller instance.
     *
     * @param  \App\Services\MarketSkillAiService  $marketSkillAiService
     * @return void
     */
    public function __construct(
        private MarketSkillAiService $marketSkillAiService
    ) {}

    /**
     * Generates and stores the user's missing skills for a target job title.
     *
     * The method retrieves the user's current skills, generates market-required
     * skills for the selected job title, normalizes both skill sets, compares
     * them, and stores only the skills that the user does not already have.
     *
     * @param  mixed  $user
     * @param  string  $jobTitle
     * @return array
     */
    public function generate($user, string $jobTitle)
    {
        $userSkills = $this->normalizeArray(
            $user->fresh()->skills->pluck('name')->toArray()
        );

        $marketSkills = $this->marketSkillAiService->generateMarketSkills($jobTitle);

        $marketSkills = collect($marketSkills)
            ->filter(fn ($item) => is_array($item) && ! empty($item['name']))
            ->map(function ($item) {
                return [
                    'name' => trim($item['name']),
                    'normalized_name' => $this->normalize($item['name']),
                    'priority' => $item['priority'] ?? 'medium',
                    'priority_reason' => $item['priority_reason'] ?? 'Important skill for the target role',
                ];
            })
            ->unique('normalized_name')
            ->values();

        $missing = $marketSkills
            ->filter(fn ($skill) => ! $this->userHasSkill($skill['normalized_name'], $userSkills))
            ->values();

        DB::table('missing_skills')
            ->where('user_id', $user->id)
            ->delete();

        $rows = [];

        foreach ($missing as $skillData) {
            $skillId = Skill::firstOrCreate([
                'name' => $skillData['name'],
            ])->id;

            $rows[] = [
                'user_id' => $user->id,
                'skill_id' => $skillId,
                'priority' => $skillData['priority'],
                'priority_reason' => $skillData['priority_reason'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (! empty($rows)) {
            DB::table('missing_skills')->insert($rows);
        }

        return $missing
            ->map(fn ($skill) => [
                'name' => $skill['name'],
                'priority' => $skill['priority'],
                'priority_reason' => $skill['priority_reason'],
            ])
            ->toArray();
    }

    /**
     * Checks whether a market skill is already covered by the user's skills.
     *
     * Composite skills are split into smaller normalized parts so skills written
     * in different formats can still be matched.
     *
     * @param  string  $marketSkill
     * @param  array  $userSkills
     * @return bool
     */
    private function userHasSkill(string $marketSkill, array $userSkills): bool
    {
        $marketParts = $this->splitCompositeSkill($marketSkill);

        foreach ($userSkills as $userSkill) {
            $userParts = $this->splitCompositeSkill($userSkill);

            foreach ($marketParts as $marketPart) {
                foreach ($userParts as $userPart) {
                    if ($marketPart === $userPart) {
                        return true;
                    }

                    if (
                        mb_strlen($userPart) >= 3 &&
                        str_contains($marketPart, $userPart)
                    ) {
                        return true;
                    }

                    if (
                        mb_strlen($marketPart) >= 3 &&
                        str_contains($userPart, $marketPart)
                    ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Splits a composite skill name into normalized comparable parts.
     *
     * This supports skill names written with separators such as slashes, commas,
     * plus signs, ampersands, or words such as "and" and "or".
     *
     * @param  string  $skill
     * @return array
     */
    private function splitCompositeSkill(string $skill): array
    {
        $skill = $this->normalize($skill);

        $parts = preg_split('/\s+(and|or)\s+|,|\/|&|\+/', $skill);

        return collect($parts)
            ->map(fn ($part) => $this->normalize($part))
            ->filter(fn ($part) => $part !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Normalizes a skill name into a comparable value.
     *
     * The method lowercases the text, removes generic words, normalizes spacing,
     * and maps common aliases to a standard skill name.
     *
     * @param  string  $skill
     * @return string
     */
    private function normalize(string $skill): string
    {
        $skill = mb_strtolower(trim($skill));

        $skill = str_replace(['-', '_'], ' ', $skill);

        $skill = str_replace([
            'framework',
            'frameworks',
            'library',
            'libraries',
            'tools',
            'tool',
            'technologies',
            'technology',
            'skills',
            'skill',
        ], '', $skill);

        $skill = preg_replace('/\s+/', ' ', $skill);
        $skill = trim($skill);

        $map = [
            'js' => 'javascript',
            'nodejs' => 'node.js',
            'node js' => 'node.js',
            'reactjs' => 'react',
            'vuejs' => 'vue',
            'restful api' => 'api',
            'rest api' => 'api',
            'apis' => 'api',
            'sql database' => 'sql',
            'html5' => 'html',
            'css3' => 'css',
        ];

        return $map[$skill] ?? $skill;
    }

    /**
     * Normalizes a list of skill names.
     *
     * The returned list is cleaned, normalized, unique, and ready for comparison
     * with normalized market skills.
     *
     * @param  array  $skills
     * @return array
     */
    private function normalizeArray(array $skills): array
    {
        return collect($skills)
            ->map(fn ($skill) => $this->normalize($skill))
            ->filter(fn ($skill) => ! empty($skill))
            ->unique()
            ->values()
            ->all();
    }
}