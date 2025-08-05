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
        Schema::create('event_attendes_relation', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('attendes_id');
            $table->unsignedBigInteger('member_id')->nullable();
            $table->unsignedBigInteger('event_txn_id');
            $table->string('name')->nullable();
            $table->unsignedInteger('amount');
            $table->unsignedInteger('number');

            $table->enum('delstatus', ['0', '1'])->default('0');
            $table->enum('status', ['0', '1'])->default('1');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('organization_id')->references('id')->on('organization')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('event_id')->references('id')->on('event')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('attendes_id')->references('id')->on('attendes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('members')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('event_txn_id')->references('id')->on('event_txn')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_attendes_relation');
    }
};
