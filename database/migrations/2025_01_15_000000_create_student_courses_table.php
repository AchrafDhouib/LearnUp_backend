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
        Schema::create('student_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('cours_id')->constrained()->onDelete('cascade');
            $table->enum('enrollment_type', ['direct', 'group'])->default('direct');
            $table->enum('status', ['active', 'completed', 'dropped'])->default('active');
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->integer('progress')->default(0); // Progress percentage (0-100)
            $table->timestamps();
            
            // Ensure a student can only be enrolled once per course
            $table->unique(['user_id', 'cours_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_courses');
    }
}; 