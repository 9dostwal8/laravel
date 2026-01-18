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

            $table->smallInteger('plan_of_ministry_mines')
                ->default(0);
            $table->smallInteger('is_brand')
                ->default(0);
            $table->string('brand_type')
                ->nullable();
            $table->smallInteger('decision_of_committee')
                ->default(0);
            $table->smallInteger('decision_of_chairman_committee')
                ->default(0);

            $table->integer('cancellation_number')
                ->nullable();
            $table->date('cancellation_date')
                ->nullable();
            $table->string('cancellation_transfer_land')
                ->nullable();
            $table->string('cancellation_attachment')
                ->nullable();

            $table->date('first_customs_date')
                ->nullable();
            $table->date('last_customs_date')
                ->nullable();

            $table->string('license_order')
                ->nullable();
            $table->string('information_form')
                ->nullable();
            $table->string('license_certificate')
                ->nullable();

            $table->smallInteger('bank_guarantee')
                ->default(0);
            $table->string('bank_guarantee_amount')
                ->default(0);
            $table->date('bank_guarantee_date')
                ->nullable();

            $table->integer('land_allocation_number')
                ->nullable();
            $table->date('land_allocation_date')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {

            $table->dropColumn('plan_of_ministry_mines');
            $table->dropColumn('is_brand');
            $table->dropColumn('brand_type');
            $table->dropColumn('decision_of_committee');
            $table->dropColumn('decision_of_chairman_committee');

            $table->dropColumn('cancellation_number');
            $table->dropColumn('cancellation_date');
            $table->dropColumn('cancellation_transfer_land');
            $table->dropColumn('cancellation_attachment');

            $table->dropColumn('first_customs_date');
            $table->dropColumn('last_customs_date');

            $table->dropColumn('license_order');
            $table->dropColumn('information_form');
            $table->dropColumn('license_certificate');

            $table->dropColumn('bank_guarantee');
            $table->dropColumn('bank_guarantee_amount');
            $table->dropColumn('bank_guarantee_date');

            $table->dropColumn('land_allocation_number');
            $table->dropColumn('land_allocation_date');
        });
    }
};
