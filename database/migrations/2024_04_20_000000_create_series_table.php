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
        Schema::create('series', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('name');
            $table->unsignedBigInteger('start_number');
            $table->unsignedBigInteger('next_number');
            $table->unsignedTinyInteger('min_length')->default('0');
            $table->enum('number_separator', ['/', '-', '_'])->default('/');
            $table->enum('type', ['journal_entry']);

            $table->enum('status', ['0', '1'])->default('1');
            $table->enum('delstatus', ['0', '1'])->default('0');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['organization_id','name']);
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
        Schema::dropIfExists('series');
    }
};
