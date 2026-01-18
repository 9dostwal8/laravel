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
        Schema::table('users', function (Blueprint $table) {
            $table->smallInteger('can_see')
                ->default(0);
            $table->smallInteger('can_edit')
                ->default(0);
            $table->smallInteger('can_insert_progress')
                ->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('can_see');
            $table->dropColumn('can_edit');
            $table->dropColumn('can_insert_progress');
        });
    }
};
