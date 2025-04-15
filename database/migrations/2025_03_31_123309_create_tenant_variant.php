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
        Schema::create('tenant_variant', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tenant_master_id')->nullable();
            // $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('name')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('email')->nullable();
            $table->unsignedBigInteger('age')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('photo')->nullable();
            $table->string('photo_name')->nullable();
            $table->string('document')->nullable();
            $table->string('document_name')->nullable();
            $table->string('police_verification')->nullable();
            $table->string('police_verification_name')->nullable();
            $table->text('locality')->nullable();
            $table->text('city')->nullable();
            $table->text('state')->nullable();
            $table->unsignedBigInteger('pincode')->nullable();
            $table->enum('isfamily', ['0', '1'])->default('0');
            $table->unsignedBigInteger('tenant_variant_id')->nullable();

            $table->enum('status', ['0', '1'])->default('1');
            $table->enum('delstatus', ['0', '1'])->default('0');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('tenant_master_id')->references('id')->on('tenant_master')->onupdate('cascade')->onDelete('cascade');
            $table->foreign('tenant_variant_id')->references('id')->on('tenant_variant')->onupdate('cascade')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenant_variant');
    }
};
