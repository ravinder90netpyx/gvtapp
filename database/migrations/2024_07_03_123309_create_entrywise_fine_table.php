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
        Schema::create('entrywise_fine', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('member_id')->nullable();
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->string('total_fine')->nullable()->default(0);
            $table->string('fine_paid')->nullable()->default(0);
            // $table->enum('fine_waveoff')->default('0');

            $table->enum('status', ['0', '1'])->default('1');
            $table->enum('delstatus', ['0', '1'])->default('0');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('member_id')->references('id')->on('members')->onUpdate('cascade')->onDelete('cascade');

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
        Schema::dropIfExists('entrywise_fine');
    }
};
