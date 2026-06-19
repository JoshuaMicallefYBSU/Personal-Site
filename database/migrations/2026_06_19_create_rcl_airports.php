<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rcl_airports', function (Blueprint $table) {
            $table->id();
            $table->string('icao', 4)->unique();
            $table->string('name');
            $table->decimal('latitude',  10, 6);
            $table->decimal('longitude', 10, 6);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rcl_airports');
    }
};