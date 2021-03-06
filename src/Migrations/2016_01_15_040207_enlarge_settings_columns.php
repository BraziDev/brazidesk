<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class EnlargeSettingsColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //make value, default columns bigger
        Schema::table('brazidesk_settings', function (Blueprint $table) {
            $table->mediumText('value')->change();
            $table->mediumText('default')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('brazidesk_settings', function (Blueprint $table) {
            $table->string('value')->change();
            $table->string('default')->change();
        });
    }
}
