<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::table('missing_skills', function (Blueprint $table) {
        $table->string('priority')->default('medium')->after('skill_id');
        $table->text('priority_reason')->nullable()->after('priority');
    });
}

public function down(): void
{
    Schema::table('missing_skills', function (Blueprint $table) {
        $table->dropColumn(['priority', 'priority_reason']);
    });
}
};
