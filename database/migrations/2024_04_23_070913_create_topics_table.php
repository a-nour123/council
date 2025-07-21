<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('order');
            $table->string('title');
            $table->integer('classification_reference')->comment("1 refer to mix , 2 refer to departement , 3 refer to the college")->nullable();
            $table->integer('escalation_authority')->comment("1 refer to departement , 2 refer to college")->nullable();
            $table->unsignedBigInteger('main_topic_id')->nullable();
            $table->foreign('main_topic_id')->references('id')->on('topics')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
