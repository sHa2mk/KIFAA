<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Skill;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_progress', function (Blueprint $table) {
            $table->foreignId('skill_id')
                ->nullable()
                ->after('user_id')
                ->constrained('skills')
                ->cascadeOnDelete();
        });

        // Convert old skill_name values to skill_id before removing the column.
        if (Schema::hasColumn('course_progress', 'skill_name')) {
            DB::table('course_progress')->orderBy('id')->each(function ($progress) {
                if (! empty($progress->skill_name)) {
                    $skill = Skill::firstOrCreate([
                        'name' => $progress->skill_name,
                    ]);

                    DB::table('course_progress')
                        ->where('id', $progress->id)
                        ->update(['skill_id' => $skill->id]);
                }
            });
        }

        Schema::table('course_progress', function (Blueprint $table) {
            if (Schema::hasColumn('course_progress', 'skill_name')) {
                $table->dropColumn('skill_name');
            }
        });

        Schema::table('course_progress', function (Blueprint $table) {
            $table->foreignId('skill_id')
                ->nullable(false)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('course_progress', function (Blueprint $table) {
            $table->string('skill_name')->nullable()->after('user_id');
        });

        DB::table('course_progress')
            ->join('skills', 'course_progress.skill_id', '=', 'skills.id')
            ->select('course_progress.id', 'skills.name')
            ->orderBy('course_progress.id')
            ->each(function ($progress) {
                DB::table('course_progress')
                    ->where('id', $progress->id)
                    ->update(['skill_name' => $progress->name]);
            });

        Schema::table('course_progress', function (Blueprint $table) {
            $table->dropForeign(['skill_id']);
            $table->dropColumn('skill_id');
        });
    }
};