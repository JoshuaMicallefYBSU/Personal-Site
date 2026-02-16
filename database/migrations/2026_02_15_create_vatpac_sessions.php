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
        Schema::create('vatpac_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('callsign');
            $table->unsignedBigInteger('user');
            $table->timestamp('logged_on');
            $table->timestamp('logged_off')->nullable();
            $table->string('time_logged')->nullable();
            $table->timestamps();

            $table->foreign('user')->references('id')->on('vatpac_users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vatpac_sessions');
    }
};
