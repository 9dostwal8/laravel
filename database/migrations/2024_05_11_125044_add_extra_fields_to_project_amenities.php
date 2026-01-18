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
        Schema::table('project_amenities', function (Blueprint $table) {
            $table->string('product_type')
                ->nullable();
            $table->string('amount')
                ->nullable();
            $table->string('measurement_unit')
                ->nullable();
            $table->smallInteger('ranking')
                ->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_amenities', function (Blueprint $table) {
            $table->dropColumn('product_type');
            $table->dropColumn('amount');
            $table->dropColumn('measurement_unit');
            $table->dropColumn('ranking');
        });
    }
};
