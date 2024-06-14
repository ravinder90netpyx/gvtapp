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
      
if (!Schema::hasColumn('journal_entry', 'name')) //check the column
    {
        Schema::table('journal_entry', function (Blueprint $table) {
            $table->string('name')->nullable();
        });
    }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('journal_entry', function($table) {
            $table->dropColumn('name');
        });
    }
};
