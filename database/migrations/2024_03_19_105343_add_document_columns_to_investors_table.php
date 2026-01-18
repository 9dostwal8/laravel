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
        Schema::dropIfExists('investor_documents');

        Schema::table('investors', function (Blueprint $table) {
            $table->string('identity_card')
                ->after('avatar')
                ->nullable();
            $table->string('national_card')
                ->after('identity_card')
                ->nullable();
            $table->string('passport')
                ->after('national_card')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investors', function (Blueprint $table) {
            $table->dropColumn('identify_card');
            $table->dropColumn('national_card');
            $table->dropColumn('passport');
        });
    }
};
