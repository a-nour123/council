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
        Schema::create('topics_axes_inputs_options', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('topic_axis_input_id')->constrained('topics_axes_inputs')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics_axes_inputs_options');
    }
};
