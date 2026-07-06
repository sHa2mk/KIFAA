<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;

test('non resume documents are rejected', function () {
    $user = User::factory()->create();

    $file = UploadedFile::fake()->create(
        'assignment.pdf',
        100,
        'application/pdf'
    );

    $response = $this->actingAs($user)->post(route('cv.upload'), [
        'resume' => $file,
    ]);

    $response->assertSessionHasErrors('resume');
});