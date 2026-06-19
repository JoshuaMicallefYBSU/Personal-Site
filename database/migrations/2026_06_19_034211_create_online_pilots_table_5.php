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
        Schema::create('rcl_online_pilots', function (Blueprint $table) {
            $table->id();
            $table->string('callsign');
            $table->string('lat');
            $table->string('lon');
            $table->string('gs');
            $table->string('name');
            $table->string('dep')->nullable();
            $table->string('arr')->nullable();
            $table->string('altn')->nullable();
            $table->string('ralt')->nullable();
            $table->string('eobt')->nullable();
            $table->integer('dep_distance')->nullable();
            $table->integer('arr_distance')->nullable();
            $table->integer('hoppie_connected')->default(0);
            $table->integer('status')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rcl_online_pilots');
    }
};
