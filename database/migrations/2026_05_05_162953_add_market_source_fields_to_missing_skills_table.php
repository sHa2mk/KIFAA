<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('missing_skills', function (Blueprint $table) {
            
            $table->string('source')->nullable()->after('skill_id');

            
            $table->timestamp('detected_at')->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('missing_skills', function (Blueprint $table) {
            $table->dropColumn(['source', 'detected_at']);
        });
    }
};