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
        Schema::create('journal_demand_relation', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('demand_charge_id');
            $table->unsignedBigInteger('journal_entry_id');
            $table->unsignedBigInteger('demand_id');
            $table->unsignedBigInteger('charge_type_id');
            $table->enum('partial',['0','1'])->default('0');
            $table->unsignedInteger('amount');
            $table->enum('demand_status',['paid','partially_paid']);

            $table->enum('delstatus', ['0', '1'])->default('0');
            $table->enum('status', ['0', '1'])->default('1');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('demand_charge_id')->references('id')->on('demand_charge')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('organization_id')->references('id')->on('organization')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('members')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('journal_entry_id')->references('id')->on('journal_entry')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('demand_id')->references('id')->on('demand')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('charge_type_id')->references('id')->on('charge_type')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('journal_demand_relation');
    }
};
