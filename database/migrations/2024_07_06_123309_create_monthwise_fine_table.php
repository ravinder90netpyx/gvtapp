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
        Schema::create('monthwise_fine', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('member_id')->nullable();
            $table->unsignedBigInteger('entrywise_fine_id')->nullable();
            $table->string('month')->nullable();
            $table->string('fine_amount')->nullable()->default(0);
            // $table->string('fine_waveoff')->nullable()->default(0);
            $table->enum('fine_waveoff',['0', '1'])->default('0');

            $table->enum('status', ['0', '1'])->default('1');
            $table->enum('delstatus', ['0', '1'])->default('0');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('member_id')->references('id')->on('members')->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('entrywise_fine_id')->references('entrywise_fine')->on('id')->onUpdate('cascade')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('monthwise_fine');
    }
};
