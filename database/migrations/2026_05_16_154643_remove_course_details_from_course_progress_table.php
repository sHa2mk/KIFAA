<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_progress', function (Blueprint $table) {
            if (Schema::hasColumn('course_progress', 'course_title')) {
                $table->dropColumn('course_title');
            }

            if (Schema::hasColumn('course_progress', 'course_link')) {
                $table->dropColumn('course_link');
            }

            if (Schema::hasColumn('course_progress', 'platform')) {
                $table->dropColumn('platform');
            }
        });
    }

    public function down(): void
    {
        Schema::table('course_progress', function (Blueprint $table) {
            $table->string('course_title')->nullable()->after('skill_id');
            $table->text('course_link')->nullable()->after('course_title');
            $table->string('platform')->nullable()->after('course_link');
        });
    }
};