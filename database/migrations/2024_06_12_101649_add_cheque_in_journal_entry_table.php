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
    $table->enum('payment_mode',['online', 'cash', 'cheque'])->change(); 
    $table->string('cheque_number')->nullable();
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
            $table->dropColumn('payment_mode');
            $table->dropColumn('cheque_number');
        });
    }
};
