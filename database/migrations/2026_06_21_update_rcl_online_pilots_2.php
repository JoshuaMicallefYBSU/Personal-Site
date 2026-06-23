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
            $table->datetime('logon_time')->nullable();
            $table->integer('dep_atis')->default(0);
            $table->integer('arr_atis')->default(0);
            $table->string('last_atis_recieved')->nullable();
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
            $table->dropColumn(['logon_time']);
            $table->dropColumn(['dep_atis']);
            $table->dropColumn(['arr_atis']);
        });
    }
};
