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
        // Add price and discount to courses table
        Schema::table('cours', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('image');
            $table->decimal('discount', 5, 2)->nullable()->after('price'); // Percentage discount
        });

        // Remove price from groups table
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove price and discount from courses table
        Schema::table('cours', function (Blueprint $table) {
            $table->dropColumn(['price', 'discount']);
        });

        // Add price back to groups table
        Schema::table('groups', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('max_students');
        });
    }
};
