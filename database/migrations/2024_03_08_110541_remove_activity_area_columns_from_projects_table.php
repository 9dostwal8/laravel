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
            $table->dropColumn('activity_area_id');
            $table->dropColumn('activity_type_id');

            $table->dropColumn('capital_dinar');
            $table->dropColumn('capital_dollar');
            $table->dropColumn('currency_rate');
            $table->dropColumn('loan_fund');
            $table->dropColumn('non_loan_fund');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('activity_area_id');
            $table->foreignId('activity_type_id');

            $table->decimal('capital_dinar', 14);
            $table->decimal('capital_dollar', 14);
            $table->decimal('currency_rate');
            $table->decimal('loan_fund', 14)
                ->nullable();
            $table->decimal('non_loan_fund', 14)
                ->nullable();
        });
    }
};
