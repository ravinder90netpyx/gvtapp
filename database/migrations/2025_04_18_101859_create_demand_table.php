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
        Schema::create('demand', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('organization_id');
            $table->text('charge_type_id');
            $table->unsignedBigInteger('series_id');
            $table->string('demand_reciept_number');
            $table->string('series_next_number');
            $table->string('member_name');
            $table->unsignedBigInteger('member_id');
            $table->text('remark');
            $table->string('file_name')->nullable();
            $table->string('from_date')->nullable();
            $table->string('to_date')->nullable();
            $table->timestamp('current_date')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->unsignedInteger('charge')->nullable();
            $table->enum('demand_status', ['paid', 'partially_paid', 'unpaid'])->default('unpaid');
            $table->string('count')->nullable();
            $table->enum('partial',['0','1'])->default('0');

            $table->enum('delstatus', ['0', '1'])->default('0');
            $table->enum('status', ['0', '1'])->default('1');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('organization_id')->references('id')->on('organization')->onUpdate('cascade')->onDelete('cascade');
            // $table->foreign('charge_type_id')->references('id')->on('charge_type')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('demand');
    }
};
