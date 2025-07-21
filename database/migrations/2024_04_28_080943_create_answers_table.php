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
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_agenda_id');
            $table->foreign('topic_agenda_id')->references('id')->on('topics_agendas');
            $table->unsignedBigInteger('input_id');
            $table->foreign('input_id')->references('id')->on('topics_axes_inputs');
            $table->unsignedBigInteger('option_id');
            $table->foreign('option_id')->references('id')->on('topics_axes_inputs_options');
            $table->json('multiple_options')->nullable(); // This column to store multiple selected options from user.
            $table->string('text')->nullable();
            $table->string('file')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
