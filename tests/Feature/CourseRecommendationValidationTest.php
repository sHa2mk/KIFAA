<?php

namespace Tests\Feature;

use App\Services\CourseRecommendationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CourseRecommendationValidationTest extends TestCase
{
    public function test_course_recommendation_returns_only_valid_unique_courses(): void
    {
        Cache::flush();

        Config::set('services.openai.course_recommendation_key', 'fake-key');
        Config::set('services.openai.model', 'gpt-4.1-mini');
        Config::set('services.openai.url', 'https://api.openai.com/v1/responses');

        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response([
                'output_text' => json_encode([
                    [
                        'title' => 'PHP Fundamentals',
                        'platform' => 'Coursera',
                        'link' => 'https://coursera.org/learn/php',
                        'price' => 'Free',
                        'language' => 'English',
                        'reason' => 'This course directly teaches PHP programming.',
                    ],
                    [
                        'title' => 'PHP Fundamentals Duplicate',
                        'platform' => 'Coursera',
                        'link' => 'https://coursera.org/learn/php',
                        'price' => 'Free',
                        'language' => 'English',
                        'reason' => 'This course directly teaches PHP programming.',
                    ],
                    [
                        'title' => 'Paid PHP Course',
                        'platform' => 'Coursera',
                        'link' => 'https://coursera.org/learn/paid-php',
                        'price' => 'Paid',
                        'language' => 'English',
                        'reason' => 'This course teaches PHP.',
                    ],
                    [
                        'title' => 'Python Fundamentals',
                        'platform' => 'Coursera',
                        'link' => 'https://coursera.org/learn/python',
                        'price' => 'Free',
                        'language' => 'English',
                        'reason' => 'This course teaches Python, not PHP.',
                    ],
                    [
                        'title' => 'PHP Course from Unsupported Platform',
                        'platform' => 'Random Platform',
                        'link' => 'https://random-platform.com/php-course',
                        'price' => 'Free',
                        'language' => 'English',
                        'reason' => 'This course teaches PHP.',
                    ],
                    [
                        'title' => 'PHP Basics',
                        'platform' => 'edX',
                        'link' => 'https://edx.org/learn/php',
                        'price' => 'Free',
                        'language' => 'English',
                        'reason' => 'This course directly improves PHP programming.',
                    ],
                ]),
            ], 200),

            'https://coursera.org/*' => Http::response('', 200),
            'https://edx.org/*' => Http::response('', 200),
            'https://random-platform.com/*' => Http::response('', 200),
        ]);

        $service = app(CourseRecommendationService::class);

        $result = $service->recommendForSkill('PHP', 'Backend Developer');

        $courses = $result['courses'];

        $this->assertCount(2, $courses);

        $this->assertEquals('PHP Fundamentals', $courses[0]['title']);
        $this->assertEquals('PHP Basics', $courses[1]['title']);

        $this->assertEquals(
            collect($courses)->pluck('link')->unique()->count(),
            count($courses)
        );

        foreach ($courses as $course) {
            $this->assertEquals('Free', $course['price']);
            $this->assertContains($course['language'], ['English', 'Arabic']);
            $this->assertStringContainsStringIgnoringCase('php', $course['title']);
        }
    }
}