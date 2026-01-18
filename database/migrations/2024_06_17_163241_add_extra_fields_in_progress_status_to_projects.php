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
            $table->bigInteger('financial_bank_capacity')->nullable();
            $table->bigInteger('project_code')->nullable();
            $table->text('proceedings_doc')->nullable();
            $table->text('application_of_investor_doc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('financial_bank_capacity');
            $table->dropColumn('project_code');
            $table->dropColumn('proceedings_doc');
            $table->dropColumn('application_of_investor_doc');
        });
    }
};
