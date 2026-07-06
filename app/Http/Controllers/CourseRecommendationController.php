<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Services\CourseRecommendationService;
use Illuminate\Http\Request;

class CourseRecommendationController extends Controller
{
    /**
     * Shows recommended courses for a selected missing skill.
     *
     * The selected skill must belong to the authenticated user's missing skills.
     * The method uses the user's target role as additional context, requests
     * course recommendations from the service, normalizes the returned courses,
     * and sends them to the course recommendation view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Skill  $skill
     * @param  \App\Services\CourseRecommendationService  $courseRecommendationService
     * @return mixed
     */
    public function show(
        Request $request,
        Skill $skill,
        CourseRecommendationService $courseRecommendationService
    ) {
        $user = $request->user();

        abort_unless(
            $user->missingSkills()->where('skill_id', $skill->id)->exists(),
            403
        );

        $jobTitle = optional($user->interest)->title;

        $startTime = microtime(true);

        $recommendationResult = $courseRecommendationService->recommendForSkill(
            $skill->name,
            $jobTitle
        );

        $executionTime = microtime(true) - $startTime;

        logger('Course Recommendation Generation Execution Time: ' . round($executionTime, 2) . ' seconds');

        $courses = $recommendationResult['courses'] ?? [];

        $courses = collect($courses)
            ->map(function ($course) {
                return [
                    'title' => $course['title'] ?? 'Course Recommendation',
                    'platform' => $course['platform'] ?? 'Online',
                    'link' => $course['url'] ?? $course['link'] ?? '#',
                    'reason' => $course['reason'] ?? '',
                    'icon' => 'fa-graduation-cap',
                    'free' => 'Online',
                ];
            })
            ->toArray();

        if (empty($courses)) {
            return view('courses.show', [
                'skill' => $skill,
                'skillName' => $skill->name,
                'courses' => [],
                'message' => 'No direct course links were found. Please try again later.',
            ]);
        }

        return view('courses.show', [
            'skill' => $skill,
            'skillName' => $skill->name,
            'courses' => $courses,
        ]);
    }
}