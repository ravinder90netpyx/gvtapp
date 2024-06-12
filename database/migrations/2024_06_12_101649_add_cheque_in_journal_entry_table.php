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
        DB::statement("ALTER TABLE journal_entry MODIFY COLUMN payment_mode ENUM('online', 'cash', 'cheque')");
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
            DB::statement("ALTER TABLE journal_entry MODIFY COLUMN payment_mode ENUM('online','cash')");
            $table->dropColumn('cheque_number');
        });
    }
};
