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
            $table->string('lat')
                ->after('land_granting_organization')
                ->nullable();
            $table->string('lng')
                ->after('lat')
                ->nullable();
            $table->string('full_address')
                ->after('lng')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('lat');
            $table->dropColumn('lng');
            $table->dropColumn('full_address');
        });
    }
};
