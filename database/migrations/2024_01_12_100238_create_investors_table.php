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
        Schema::create('investors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id');
            $table->foreignId('country_id');
            $table->json('name');
            $table->tinyInteger('gender');
            $table->string('nationality');
            $table->unsignedBigInteger('national_code');
            $table->string('email');
            $table->string('first_phone_number');
            $table->string('second_phone_number')
                ->nullable();
            $table->string('passport_number');
            $table->string('address');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investors');
    }
};
