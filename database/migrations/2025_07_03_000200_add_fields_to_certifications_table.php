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
        Schema::table('certifications', function (Blueprint $table) {
            $table->string('certificate_number')->unique()->after('id');
            $table->string('student_name')->after('certificate_number');
            $table->string('course_name')->after('student_name');
            $table->string('instructor_name')->nullable()->after('course_name');
            $table->decimal('score', 5, 2)->after('instructor_name');
            $table->decimal('required_score', 5, 2)->after('score');
            $table->date('issued_date')->after('required_score');
            $table->string('validity_period')->default('Permanent')->after('issued_date');
            $table->text('achievement_description')->nullable()->after('validity_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certifications', function (Blueprint $table) {
            $table->dropColumn([
                'certificate_number',
                'student_name',
                'course_name',
                'instructor_name',
                'score',
                'required_score',
                'issued_date',
                'validity_period',
                'achievement_description'
            ]);
        });
    }
};
