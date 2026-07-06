<?php

namespace Tests\Feature;

use App\Http\Controllers\MissingSkillController;
use App\Models\Interest;
use App\Models\Skill;
use App\Models\User;
use App\Services\MarketSkillAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillGapAnalysisTest extends TestCase
{
    use RefreshDatabase;

    public function test_skill_gap_analysis_saves_only_missing_skills(): void
    {
        $this->mock(MarketSkillAiService::class, function ($mock) {
            $mock->shouldReceive('generateMarketSkills')
                ->once()
                ->with('Backend Developer')
                ->andReturn([
                    [
                        'name' => 'PHP',
                        'priority' => 'high',
                        'priority_reason' => 'Core backend language',
                    ],
                    [
                        'name' => 'Laravel',
                        'priority' => 'high',
                        'priority_reason' => 'Primary backend framework',
                    ],
                    [
                        'name' => 'SQL',
                        'priority' => 'medium',
                        'priority_reason' => 'Used for database queries',
                    ],
                    [
                        'name' => 'Docker',
                        'priority' => 'medium',
                        'priority_reason' => 'Used for deployment workflows',
                    ],
                    [
                        'name' => 'Git',
                        'priority' => 'high',
                        'priority_reason' => 'Used for version control',
                    ],
                ]);
        });

        $interest = Interest::create([
            'title' => 'Backend Developer',
        ]);

        $user = User::factory()->create([
            'interest_id' => $interest->id,
        ]);

        $php = Skill::create(['name' => 'PHP']);
        $laravel = Skill::create(['name' => 'Laravel']);
        $sql = Skill::create(['name' => 'SQL']);

        $user->skills()->attach([
            $php->id,
            $laravel->id,
            $sql->id,
        ]);

        $missingSkills = app(MissingSkillController::class)
            ->generate($user, 'Backend Developer');

        $this->assertCount(2, $missingSkills);

        $this->assertEquals(
            ['Docker', 'Git'],
            collect($missingSkills)->pluck('name')->sort()->values()->toArray()
        );

        $this->assertDatabaseHas('missing_skills', [
            'user_id' => $user->id,
            'priority' => 'medium',
            'priority_reason' => 'Used for deployment workflows',
        ]);

        $this->assertDatabaseHas('missing_skills', [
            'user_id' => $user->id,
            'priority' => 'high',
            'priority_reason' => 'Used for version control',
        ]);

        $this->assertDatabaseMissing('missing_skills', [
            'user_id' => $user->id,
            'skill_id' => $php->id,
        ]);

        $this->assertDatabaseMissing('missing_skills', [
            'user_id' => $user->id,
            'skill_id' => $laravel->id,
        ]);

        $this->assertDatabaseMissing('missing_skills', [
            'user_id' => $user->id,
            'skill_id' => $sql->id,
        ]);
    }

    public function test_skill_gap_analysis_handles_duplicates_and_normalized_skill_names(): void
{
    $this->mock(MarketSkillAiService::class, function ($mock) {
        $mock->shouldReceive('generateMarketSkills')
            ->once()
            ->with('Frontend Developer')
            ->andReturn([
                [
                    'name' => 'JavaScript',
                    'priority' => 'high',
                    'priority_reason' => 'Core frontend language',
                ],
                [
                    'name' => 'HTML5',
                    'priority' => 'high',
                    'priority_reason' => 'Used to structure web pages',
                ],
                [
                    'name' => 'CSS3',
                    'priority' => 'high',
                    'priority_reason' => 'Used for styling web pages',
                ],
                [
                    'name' => 'ReactJS',
                    'priority' => 'medium',
                    'priority_reason' => 'Common frontend library',
                ],
                [
                    'name' => 'React',
                    'priority' => 'medium',
                    'priority_reason' => 'Duplicate normalized frontend library',
                ],
                [
                    'name' => 'Docker',
                    'priority' => 'low',
                    'priority_reason' => 'Useful for deployment',
                ],
            ]);
    });

    $interest = Interest::create([
        'title' => 'Frontend Developer',
    ]);

    $user = User::factory()->create([
        'interest_id' => $interest->id,
    ]);

    $js = Skill::create(['name' => 'JS']);
    $html = Skill::create(['name' => 'HTML']);
    $css = Skill::create(['name' => 'CSS']);

    $user->skills()->attach([
        $js->id,
        $html->id,
        $css->id,
    ]);

    $missingSkills = app(MissingSkillController::class)
        ->generate($user, 'Frontend Developer');

    $this->assertEquals(
        ['Docker', 'ReactJS'],
        collect($missingSkills)->pluck('name')->sort()->values()->toArray()
    );

    $this->assertCount(2, $missingSkills);

    $this->assertDatabaseHas('missing_skills', [
        'user_id' => $user->id,
        'priority' => 'medium',
        'priority_reason' => 'Common frontend library',
    ]);

    $this->assertDatabaseHas('missing_skills', [
        'user_id' => $user->id,
        'priority' => 'low',
        'priority_reason' => 'Useful for deployment',
    ]);
}
}