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
        Schema::create('event_txn', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('series_id');
            $table->unsignedBigInteger('member_id')->nullable();
            $table->string('series_number');
            $table->string('series_next_number');
            $table->unsignedInteger('amount');
            $table->string('name')->nullable();
            $table->string('mobile_number')->nullable();
            $table->enum('outsider', ['0','1'])->default('0');
            $table->enum('mode',['cash','online','personal_online']);
            $table->timestamp('payment_date')->nullable();
            $table->enum('payment_status',['paid','unpaid'])->default('unpaid');
            $table->string('file_name')->nullable();
            $table->text('remark')->nullable();
            $table->unsignedInteger('count')->default(0);

            $table->enum('delstatus', ['0', '1'])->default('0');
            $table->enum('status', ['0', '1'])->default('1');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('organization_id')->references('id')->on('organization')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('event_id')->references('id')->on('event')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('series_id')->references('id')->on('series')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('event_txn');
    }
};
