<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rcl_online_pilots', function (Blueprint $table) {
            $table->string('aircraft')->nullable();
            $table->string('registration')->nullable();
            $table->string('route',4096)->nullable();
            $table->string('elt')->nullable();
            $table->string('fl')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rcl_online_pilots', function (Blueprint $table) {
            $table->dropColumn(['ralt_time']);
        });
    }
};
