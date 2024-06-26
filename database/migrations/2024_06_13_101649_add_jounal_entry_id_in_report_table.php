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
      

    Schema::table('report', function (Blueprint $table) {
        $table->unsignedBigInteger('journal_entry_id')->nullable();
        $table->foreign('journal_entry_id')->references('id')->on('journal_entry')->onUpdate('cascade')->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('report', function($table) {
            $table->dropColumn('journal_entry_id');
        });
    }
};
