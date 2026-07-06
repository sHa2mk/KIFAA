<?php

namespace App\Http\Controllers;

use App\Models\Interest;
use App\Models\Skill;
use App\Services\DigitalTwinService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EditSkillsController extends Controller
{
    /**
     * Shows the career profile edit form.
     *
     * The method loads the authenticated user's current skills and selected
     * target role, then sends them to the edit page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function edit(Request $request)
    {
        $user = $request->user()->load('skills', 'interest');

        $skills = $user->skills->pluck('name')->toArray();

        return view('skills.edit', [
            'jobTitle' => old('job_title', optional($user->interest)->title ?? $user->job_title ?? ''),
            'skills' => $skills,
            'hasProfile' => count($skills) > 0 || $user->interest_id,
            'hasSkills' => count($skills) > 0,
        ]);
    }

    /**
     * Saves the edited career profile and refreshes the Digital Twin analysis.
     *
     * The method validates the updated target role and skills, replaces the
     * user's old skill list, deletes old missing skills, regenerates the skill
     * gap analysis, and refreshes the Digital Twin readiness data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\DigitalTwinService  $digitalTwinService
     * @return mixed
     */
    public function update(Request $request, DigitalTwinService $digitalTwinService)
    {
        $validated = $request->validate([
            'job_title' => ['required', 'string', 'max:255'],
            'skills_text' => ['required', 'string'],
        ]);

        $user = $request->user();
        $jobTitle = trim($validated['job_title']);

        $skillsArray = collect(preg_split("/\r\n|\n|\r/", trim($validated['skills_text'])))
            ->map(fn ($skill) => trim($skill))
            ->filter()
            ->unique(fn ($skill) => mb_strtolower($skill))
            ->values();

        DB::transaction(function () use ($user, $jobTitle, $skillsArray, $digitalTwinService) {
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

            foreach ($skillsArray as $skillName) {
                $skillIds[] = Skill::firstOrCreate([
                    'name' => $skillName,
                ])->id;
            }

            DB::table('skill_user')
                ->where('user_id', $user->id)
                ->delete();

            foreach ($skillIds as $skillId) {
                DB::table('skill_user')->insert([
                    'user_id' => $user->id,
                    'skill_id' => $skillId,
                ]);
            }

            DB::table('missing_skills')
                ->where('user_id', $user->id)
                ->delete();

            app(MissingSkillController::class)
                ->generate($user->fresh(), $jobTitle);

            $digitalTwinService->getDashboardData($user->fresh());
        });

        return redirect()
            ->route('skills.edit')
            ->with('success', 'Profile updated and Digital Twin re-analyzed successfully.');
    }

    /**
     * Clears the user's career profile and generated Digital Twin data.
     *
     * The method detaches all current skills, removes generated missing skills,
     * clears the selected target role and job title, deletes the calculated
     * Digital Twin record, and sends the user back to the CV upload flow.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function reset(Request $request)
    {
        $user = $request->user();

        DB::transaction(function () use ($user) {
            $user->skills()->detach();

            DB::table('missing_skills')
                ->where('user_id', $user->id)
                ->delete();

            if (Schema::hasColumn('users', 'interest_id')) {
                $user->interest_id = null;
            }

            if (Schema::hasColumn('users', 'job_title')) {
                $user->job_title = null;
            }

            $user->save();

            DB::table('digital_twins')
                ->where('user_id', $user->id)
                ->delete();
        });

        return redirect()
            ->route('cv.upload.form')
            ->with('success', 'Career profile reset successfully.');
    }
}