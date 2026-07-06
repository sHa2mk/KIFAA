<?php

namespace App\Services;

use App\Models\CourseProgress;
use App\Models\DigitalTwin;
use Illuminate\Support\Facades\DB;

class DigitalTwinService
{
    /**
     * Prepares the full dashboard data for the Digital Twin page.
     *
     * This method collects the user's current skills, missing skills,
     * readiness score, Digital Twin level, completed courses, chart data,
     * and newly detected market skills. The returned data is ready to be
     * displayed directly by the dashboard Blade view.
     *
     * @param  mixed  $user
     * @return array
     */
    public function getDashboardData($user)
    {
        $userSkills = $user->skills()
            ->pluck('name')
            ->toArray();

        if (count($userSkills) === 0 && ! $user->interest_id) {
            return [
                'twin' => null,
                'hasProfile' => false,
                'userSkills' => [],
                'missingSkills' => [],
                'newMarketSkills' => [],
                'marketSkillsCount' => 0,
                'matchScore' => 0,
                'readiness' => 0,
                'twinLevel' => 'Beginner',
                'skillProgress' => 100,
                'courses' => collect(),
                'chartData' => [
                    'skillsDistribution' => [
                        'current' => 0,
                        'missing' => 0,
                    ],
                ],
            ];
        }

        $missingSkills = $user->missingSkills()
            ->with('skill:id,name')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->skill->id,
                'name' => $item->skill->name,
                'priority' => $item->priority ?? 'medium',
                'priority_reason' => $item->priority_reason ?? null,
                'source' => $item->source,
                'detected_at' => $item->detected_at,
            ])
            ->filter(fn ($item) => ! empty($item['name']))
            ->sortBy(function ($item) {
                $marketOrder = ($item['source'] ?? null) === 'weekly_job_market_sync' ? 2 : 1;

                $priorityOrder = match ($item['priority']) {
                    'high' => 1,
                    'medium' => 2,
                    'low' => 3,
                    default => 2,
                };

                return $marketOrder . '-' . $priorityOrder . '-' . strtolower($item['name']);
            })
            ->values()
            ->toArray();

        $newMarketSkills = collect($missingSkills)
            ->filter(fn ($skill) => ($skill['source'] ?? null) === 'weekly_job_market_sync')
            ->sortByDesc('detected_at')
            ->take(5)
            ->values()
            ->toArray();

        $marketSkillsCount = count($userSkills) + count($missingSkills);

        $matchScore = 0;

        if ($marketSkillsCount > 0) {
            $matchScore = round(
                (count($userSkills) / $marketSkillsCount) * 100
            );
        }

        $twin = DigitalTwin::updateOrCreate(
            ['user_id' => $user->id],
            [
                'readiness_score' => $matchScore,
                'status' => 'active',
                'last_updated_at' => now(),
            ]
        );

        $readiness = $twin->readiness_score ?? $matchScore;

        $twinLevel = match (true) {
            $readiness < 40 => 'Beginner',
            $readiness < 75 => 'Intermediate',
            default => 'Advanced',
        };

        $skillProgress = count($missingSkills) > 0
            ? round(100 / max(count($missingSkills), 1))
            : 100;

        $courses = CourseProgress::where('user_id', $user->id)->get();

        return [
            'twin' => $twin,
            'hasProfile' => true,
            'userSkills' => $userSkills,
            'missingSkills' => $missingSkills,
            'newMarketSkills' => $newMarketSkills,
            'marketSkillsCount' => $marketSkillsCount,
            'matchScore' => $matchScore,
            'readiness' => $readiness,
            'twinLevel' => $twinLevel,
            'skillProgress' => $skillProgress,
            'courses' => $courses,
            'chartData' => [
                'skillsDistribution' => [
                    'current' => count($userSkills),
                    'missing' => count($missingSkills),
                ],
            ],
        ];
    }

    /**
     * Marks a skill as completed and updates the user's Digital Twin.
     *
     * The completed skill is saved in course progress, attached to the user's
     * current skills, removed from the missing skills list, and then the fresh
     * dashboard data is returned.
     *
     * @param  mixed  $user
     * @param  array  $courseData
     * @return array
     */
    public function completeCourse($user, array $courseData)
    {
        return DB::transaction(function () use ($user, $courseData) {
            $skillId = $courseData['skill_id'];

            CourseProgress::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'skill_id' => $skillId,
                ],
                [
                    'status' => 'completed',
                    'completed_at' => now(),
                ]
            );

            $user->skills()->syncWithoutDetaching([$skillId]);

            DB::table('missing_skills')
                ->where('user_id', $user->id)
                ->where('skill_id', $skillId)
                ->delete();

            return $this->getDashboardData($user);
        });
    }

    /**
     * Simulates how learning one missing skill may change the readiness score.
     *
     * This method does not save any data. It calculates the current readiness
     * score and a predicted readiness score based on the selected missing
     * skill and its priority.
     *
     * @param  mixed  $user
     * @param  string  $skillName
     * @return array
     */
    public function simulateSkillImpact($user, string $skillName)
    {
        $currentSkills = $user->skills()->count();

        $missingSkill = DB::table('missing_skills')
            ->join('skills', 'missing_skills.skill_id', '=', 'skills.id')
            ->where('missing_skills.user_id', $user->id)
            ->where('skills.name', $skillName)
            ->select(
                'missing_skills.priority',
                'missing_skills.priority_reason'
            )
            ->first();

        $missingSkills = DB::table('missing_skills')
            ->where('user_id', $user->id)
            ->count();

        $total = $currentSkills + $missingSkills;

        if ($total === 0) {
            return [
                'skill' => $skillName,
                'current' => 0,
                'predicted' => 0,
                'increase' => 0,
                'priority' => 'medium',
                'priority_reason' => null,
            ];
        }

        $currentScore = round(($currentSkills / $total) * 100);

        $priority = $missingSkill->priority ?? 'medium';

        $impactWeight = match ($priority) {
            'high' => 1.5,
            'medium' => 1.0,
            'low' => 0.6,
            default => 1.0,
        };

        $predictedSkills = $currentSkills + $impactWeight;
        $predictedScore = round(($predictedSkills / $total) * 100);
        $predictedScore = min($predictedScore, 100);

        return [
            'skill' => $skillName,
            'current' => $currentScore,
            'predicted' => $predictedScore,
            'increase' => max($predictedScore - $currentScore, 0),
            'priority' => $priority,
            'priority_reason' => $missingSkill->priority_reason ?? null,
        ];
    }
}