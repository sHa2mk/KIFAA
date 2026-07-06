<?php

use Illuminate\Database\Migrations\Migration; 
use Illuminate\Database\Schema\Blueprint; 
use Illuminate\Support\Facades\Schema; 

return new class extends Migration
{
     /**
     * Adds the selected career interest reference to the users table.
     *
     * The interest_id column links each user to one target role. It remains
     * nullable because new users may not have created a career profile yet.
     */

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->foreignId('interest_id')
                ->nullable()
                ->constrained('interests')
                ->nullOnDelete()
                ->after('password');

            
        });
    }
    /**
     * Removes the selected career interest reference from the users table.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('interest_id');
        });
    }
};