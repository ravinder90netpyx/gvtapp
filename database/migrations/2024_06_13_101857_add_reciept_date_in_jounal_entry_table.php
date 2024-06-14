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
        Schema::table('journal_entry', function (Blueprint $table) {
            $table->timestamp('reciept_date')->nullable();
            $table->unsignedBigInteger('charge_type_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('journal_entry', function($table) {
            $table->dropColumn('reciept_date');
            $table->dropColumn('charge_type_id');
        });
    }
};
