<?php

use App\Http\Controllers\CourseRecommendationController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\DigitalTwinController;
use App\Http\Controllers\EditSkillsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

require __DIR__.'/settings.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DigitalTwinController::class, 'index'])
        ->name('dashboard');

    Route::get('/career-profile', [CvController::class, 'index'])
        ->name('career-profile.index');

    Route::get('/cv/upload', [CvController::class, 'showUpload'])
        ->name('cv.upload.form');

    Route::post('/cv/upload', [CvController::class, 'upload'])
        ->name('cv.upload');

    Route::get('/cv/manual', [CvController::class, 'manual'])
        ->name('cv.manual');

    Route::get('/cv/preview', [CvController::class, 'preview'])
        ->name('cv.preview');

    Route::post('/cv/confirm', [CvController::class, 'confirm'])
        ->name('cv.confirm');

    Route::post('/cv/reset', [CvController::class, 'reset'])
        ->name('cv.reset');

    Route::get('/courses/{skill}', [CourseRecommendationController::class, 'show'])
        ->name('courses.show');

    Route::post('/complete-course', [DigitalTwinController::class, 'completeCourse'])
        ->name('course.complete');

    Route::post('/simulate', [DigitalTwinController::class, 'simulate'])
        ->name('skill.simulate');

    Route::get('/skills/edit', [EditSkillsController::class, 'edit'])
        ->name('skills.edit');

    Route::put('/skills/edit', [EditSkillsController::class, 'update'])
        ->name('skills.update');

    Route::delete('/skills/reset', [EditSkillsController::class, 'reset'])
        ->name('skills.reset');
});