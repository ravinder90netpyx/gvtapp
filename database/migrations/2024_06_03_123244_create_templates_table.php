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
    public function up() {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('organization_id');
            $table->enum('name', ['reminder', 'reciept']);
            $table->string('template_id', 100);
            $table->text('params')->nullable();

            $table->enum('delstatus', ['0', '1'])->default('0');
            $table->enum('status', ['0', '1'])->default('1');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['name','organization_id']);
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
        Schema::dropIfExists('templates');
    }
};
