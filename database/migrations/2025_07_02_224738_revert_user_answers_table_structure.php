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
        Schema::table('user_answers', function (Blueprint $table) {
            // Drop exam_id column
            $table->dropForeign(['exam_id']);
            $table->dropColumn('exam_id');
            
            // Re-add passed_exam_id column
            $table->foreignId('passed_exam_id')->nullable()->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_answers', function (Blueprint $table) {
            // Drop passed_exam_id column
            $table->dropForeign(['passed_exam_id']);
            $table->dropColumn('passed_exam_id');
            
            // Re-add exam_id column
            $table->foreignId('exam_id')->nullable()->constrained()->onDelete('cascade');
        });
    }
};
