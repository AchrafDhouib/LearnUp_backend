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
        Schema::table('groups', function (Blueprint $table) {
            $table->dateTime('start_date')->default(now());
            $table->dateTime('end_date')->default(now()->addMonths(1));
            $table->text('description')->nullable();
            $table->string('image',1024)->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('max_students')->nullable();   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
            $table->dropColumn('description');
            $table->dropColumn('image');
            $table->dropColumn('price');
            $table->dropColumn('max_students');
            });
    }
};
