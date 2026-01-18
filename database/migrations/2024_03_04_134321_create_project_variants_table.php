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
        Schema::create('project_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id');
            $table->foreignId('activity_area_id');
            $table->foreignId('activity_type_id');
            $table->string('capital_dinar')->nullable();
            $table->string('capital_dollar')->nullable();
            $table->string('currency_rate')->nullable();
            $table->string('loan_fund')
                ->nullable();
            $table->string('non_loan_fund')
                ->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_variants');
    }
};
