<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('investors', function (Blueprint $table) {
            $table->smallInteger('type')->default(1)
                ->after('passport')
                ->nullable();;
            $table->string('license_number')->nullable()
                ->after('type')
                ->nullable();;
            $table->string('company_certificate')
                ->after('license_number')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investors', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('company_certificate');
            $table->dropColumn('license_number');
        });
    }
};
