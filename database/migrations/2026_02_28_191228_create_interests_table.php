<?php

use Illuminate\Database\Migrations\Migration; 
use Illuminate\Database\Schema\Blueprint; 
use Illuminate\Support\Facades\Schema; 

return new class extends Migration
{
     /**
     * Creates the interests table.
     *
     * Each record stores one unique career interest or target job title that
     * can be linked to multiple users.
     */
    public function up(): void
    {
        Schema::create('interests', function (Blueprint $table) {
            $table->id(); 

            $table->string('title')->unique(); 

            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interests'); 
    }
};