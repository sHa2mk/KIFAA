<?php

namespace App\Console\Commands;

use App\Models\MissingSkill;
use App\Models\Skill;
use App\Models\User;
use App\Notifications\NewMarketSkillDetectedNotification;
use App\Services\JobMarketSkillService;
use Illuminate\Console\Command;

class SyncJobMarketSkills extends Command
{
    protected $signature = 'skills:sync-job-market';

    protected $description = 'Sync missing skills with weekly job market trends';

    /**
     * Runs the weekly job market skills synchronization.
     *
     * The command checks users who have a selected career interest, retrieves
     * recent in-demand skills for their target role, compares the results with
     * the user's current and missing skills, then stores only newly detected
     * market skills and sends a database notification.
     *
     * @param  \App\Services\JobMarketSkillService  $jobMarketSkillService
     * @return int
     */
    public function handle(JobMarketSkillService $jobMarketSkillService): int
    {
        $users = User::query()
            ->with('interest')
            ->whereNotNull('interest_id')
            ->get();

        foreach ($users as $user) {
            $role = $user->interest?->title;

            if (! $role) {
                continue;
            }

            $marketSkills = $jobMarketSkillService->getLatestSkillsForRole($role);

            foreach ($marketSkills as $marketSkill) {
                $skillName = trim($marketSkill['skill'] ?? '');

                if ($skillName === '') {
                    continue;
                }

                $skill = Skill::firstOrCreate([
                    'name' => $skillName,
                ]);

                $userAlreadyHasSkill = $user->skills()
                    ->where('skills.id', $skill->id)
                    ->exists();

                if ($userAlreadyHasSkill) {
                    continue;
                }

                $missingSkill = MissingSkill::where('user_id', $user->id)
                    ->where('skill_id', $skill->id)
                    ->first();

                if ($missingSkill) {
                    continue;
                }

                $missingSkill = MissingSkill::create([
                    'user_id' => $user->id,
                    'skill_id' => $skill->id,
                    'source' => 'weekly_job_market_sync',
                    'detected_at' => now(),
                    'priority' => 'medium',
                    'priority_reason' => $marketSkill['reason'] ?? 'This skill is currently in demand in the job market.',
                ]);

                $user->notify(new NewMarketSkillDetectedNotification($missingSkill));
            }
        }

        $this->info('Weekly job market skills sync completed.');

        return self::SUCCESS;
    }
}