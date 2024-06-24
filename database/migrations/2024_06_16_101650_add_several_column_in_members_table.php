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
        Schema::table('members', function (Blueprint $table) {
            $table->string('sublet_number')->nullable();
            $table->string('sublet_message')->nullable();
            $table->string('mobile_message')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('members', function($table) {
            $table->string('sublet_number')->nullable();
            $table->string('sublet_message')->nullable();
            $table->string('mobile_message')->nullable();
        });
    }
};
