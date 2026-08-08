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
        Schema::create('movie_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->enum('type', ['Movie', 'Series', 'TV Show']);
            $table->string('title');
            $table->unsignedSmallInteger('release_year');
            $table->boolean('all_episodes')->default(false);
            $table->string('episodes')->nullable();
            $table->string('delete_token', 64)->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movie_requests');
    }
};
