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
        Schema::create('tenant_master', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('member_id')->nullable();
            $table->string('member_name')->nullable();
            $table->date('start_date')->nullable();
            $table->enum('type', ['family', 'individual'])->nullable();

            // Agreement Copies
            $table->string('rent_agreement')->nullable();
            $table->string('rent_agreement_name')->nullable();
            // $table->string('police_verification')->nullable();
            // $table->string('police_verification_name')->nullable();
            $table->string('undertaking')->nullable();
            $table->string('undertaking_name')->nullable();
            $table->string('acceptance')->nullable();
            $table->string('acceptance_name')->nullable();

            $table->string('pdf_file')->nullable();

            $table->enum('status', ['0', '1'])->default('1');
            $table->enum('delstatus', ['0', '1'])->default('0');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('tenant_master');
    }
};
