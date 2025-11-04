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
        Schema::create('obi_declarations', function (Blueprint $table) {
            $table->id();
            $table->string('file')->unique(); // Source file name
            $table->string('function_name')->unique()->index(); // Gemini function name
            $table->text('function_description');
            $table->longText('declaration'); // Serialized Declaration instance
            $table->string('target_class'); // Target class to execute
            $table->string('target_method'); // Target method to execute
            $table->string('tag')->nullable()->index();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obi_declarations');
    }
};
