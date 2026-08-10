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
        Schema::table('movie_requests', function (Blueprint $table) {
            $table->enum('status', ['Requested', 'Available'])->default('Requested')->after('episodes');
            $table->string('available_token', 64)->nullable()->unique()->after('delete_token');
            $table->string('discord_message_id')->nullable()->after('available_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movie_requests', function (Blueprint $table) {
            $table->dropColumn(['status', 'available_token', 'discord_message_id']);
        });
    }
};
