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
        Schema::table('projects', function (Blueprint $table) {
            $table->integer('execution_time_days')
                ->after('execution_time_months')
                ->nullable();
            $table->text('attachment_of_decision')
                ->after('full_address')
                ->nullable();
            $table->json('activity_area_limit')
                ->after('attachment_of_decision')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('execution_time_days');
            $table->dropColumn('attachment_of_decision');
        });
    }
};
