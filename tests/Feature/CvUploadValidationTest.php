<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\OpenAiCvService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class CvUploadValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cv_upload_rejects_unsupported_file_format(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/cv/upload', [
            'resume' => UploadedFile::fake()->create('not-cv.txt', 100, 'text/plain'),
        ]);

        $response->assertSessionHasErrors('resume');
    }

    public function test_cv_upload_rejects_file_larger_than_5mb(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/cv/upload', [
            'resume' => UploadedFile::fake()->create('large-cv.pdf', 6000, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('resume');
    }

    public function test_cv_upload_requires_resume_file(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/cv/upload', []);

        $response->assertSessionHasErrors('resume');
    }

    public function test_cv_upload_rejects_readable_document_that_is_not_resume(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $file = $this->fakeDocxFile(
            'assignment.docx',
            'This is a university assignment about software engineering principles.
            It discusses general concepts and project documentation.
            It does not include resume sections, contact information, skills, education, or work experience.'
        );

        $response = $this->actingAs($user)->post('/cv/upload', [
            'resume' => $file,
        ]);

        $response->assertSessionHasErrors('resume');
        $response->assertSessionHasErrors('resume');    }

    public function test_cv_upload_accepts_readable_resume_content(): void
    {
        Storage::fake('local');

        $this->mock(OpenAiCvService::class, function ($mock) {
            $mock->shouldReceive('analyzeCvText')
                ->once()
                ->andReturn([
                    'job_title' => 'Backend Developer',
                    'skills' => ['PHP', 'Laravel', 'SQL'],
                ]);
        });

        $user = User::factory()->create();

        $file = $this->fakeDocxFile(
            'resume.docx',
            'John Doe
            john@example.com
            +966500000000

            Summary
            Backend Developer with experience in web applications.

            Experience
            Developed Laravel applications and REST APIs from 2021 to 2024.

            Education
            Bachelor degree in Computer Science 2023.

            Skills
            PHP
            Laravel
            SQL
            Git

            Projects
            Built a career platform using Laravel and MySQL.'
        );

        $response = $this->actingAs($user)->post('/cv/upload', [
            'resume' => $file,
        ]);

        $response->assertRedirect(route('cv.preview'));

        $this->assertEquals('Backend Developer', session('cv.preview.job_title'));
        $this->assertEquals(['PHP', 'Laravel', 'SQL'], session('cv.preview.skills'));
    }

    private function fakeDocxFile(string $name, string $text): UploadedFile
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'docx_');

        $zip = new ZipArchive();

        $zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
</Types>');

        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>');

        $paragraphs = collect(preg_split("/\r\n|\n|\r/", $text))
            ->map(fn ($line) => '<w:p><w:r><w:t>' . htmlspecialchars(trim($line), ENT_XML1) . '</w:t></w:r></w:p>')
            ->implode('');

        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
    <w:body>' . $paragraphs . '</w:body>
</w:document>');

        $zip->close();

        return new UploadedFile(
            $tempPath,
            $name,
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            null,
            true
        );
    }
}