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
        Schema::table('templates', function (Blueprint $table) {
            DB::statement("ALTER TABLE templates MODIFY COLUMN name ENUM('welcome', 'reminder', 'reciept', 'overdue')");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('templates', function($table) {
            DB::statement("ALTER TABLE templates MODIFY COLUMN name ENUM('welcome', 'reminder', 'reciept')");
            
        });
    }
};
