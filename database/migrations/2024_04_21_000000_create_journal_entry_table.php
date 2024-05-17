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
        Schema::create('journal_entry', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('series_id')->nullable();
            $table->string('series_number')->nullable();
            $table->unsignedBigInteger('series_next_number')->nullable();
            $table->string('entry_year')->nullable();
            $table->timestamp('entry_date')->nullable();
            $table->string('file_name')->nullable();
            $table->string('from_month')->nullable();
            $table->string('to_month')->nullable();
            $table->enum('payment_mode', ['online', 'cash']);
            $table->unsignedInteger('charge')->nullable();
            $table->enum('partial', ['0', '1'])->default('0');
            
            $table->enum('status', ['0', '1'])->default('1');
            $table->enum('delstatus', ['0', '1'])->default('0');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('series_id')->references('id')->on('series')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('members')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('organization_id')->references('id')->on('organization')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('journal_entry');
    }
};
