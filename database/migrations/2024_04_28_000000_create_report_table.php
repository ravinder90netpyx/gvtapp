<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the   migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
        'name',
        'start_number',
        'next_number',
        'min_length',
        'number_separator',
        'type',
        'item_type',
        'brand_ids',
        'delstatus',
        'status'
        */
        Schema::create('report', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('month');
            $table->unsignedBigInteger('member_id');
            // $table->unsignedBigInteger('rate');
            $table->unsignedBigInteger('money_paid')->nullable();
            $table->string('money_pending')->nullable();

            $table->enum('status', ['0', '1'])->default('1');
            $table->enum('delstatus', ['0', '1'])->default('0');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('member_id')->references('id')->on('members')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report');
    }
};
