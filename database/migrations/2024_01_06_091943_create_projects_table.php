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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id');
            $table->foreignId('activity_area_id');
            $table->foreignId('activity_type_id');
            $table->foreignId('state_id')
                ->nullable();
            $table->foreignId('area_id')
                ->nullable();
            $table->foreignId('department_id')
                ->nullable();
            $table->string('village')->nullable();
            $table->json('project_name');
            $table->string('company_name')->nullable();
            $table->tinyInteger('status');
            $table->string('old_file_number')
                ->nullable();
            $table->string('file_number')
                ->nullable();
            $table->tinyInteger('investment_type');
            $table->tinyInteger('licensing_authority');
            $table->string('license_number');
            $table->decimal('capital_dinar', 14);
            $table->decimal('capital_dollar', 14);
            $table->decimal('currency_rate');
            $table->decimal('loan_fund', 14)
                ->nullable();
            $table->decimal('non_loan_fund', 14)
                ->nullable();
            $table->smallInteger('execution_time_years')
                ->default(0);
            $table->smallInteger('execution_time_months')
                ->default(0);
            $table->decimal('hectare_area', 14);
            $table->integer('meter_area');

            $table->string('project_location');
            $table->string('place_of_land_allocation')->nullable();
            $table->json('land_number');
            $table->string('type_of_land_allocation');
            $table->string('land_granting_organization');

            $table->mediumInteger('total_permanent_working_group')->nullable();
            $table->mediumInteger('kurdistan_fixed_workforce_count')->nullable();
            $table->mediumInteger('foreign_fixed_workforce_count')->nullable();
            $table->mediumInteger('iraq_fixed_workforce_count')->nullable();
            $table->mediumInteger('seperated_areas_fixed_workforce_count')->nullable();
            $table->mediumInteger('total_temporary_labor')->nullable();
            $table->mediumInteger('kurdistan_temporary_workforce_count')->nullable();
            $table->mediumInteger('foreign_temporary_workforce_count')->nullable();
            $table->mediumInteger('iraq_temporary_workforce_count')->nullable();
            $table->mediumInteger('seperated_areas_temporary_workforce_count')->nullable();

            $table->timestamp('requested_at');
            $table->timestamp('licence_received_at')
                ->nullable();
            $table->timestamp('land_delivered_at')
                ->nullable();
            $table->timestamp('started_at')
                ->nullable();
            $table->timestamp('estimated_project_end_date')
                ->nullable();
            $table->timestamp('actual_project_end_date')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
