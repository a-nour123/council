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
        Schema::create('agandes_topic_form', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('topics'); // assuming topics is the name of the table
            $table->foreignId('agenda_id')->constrained('topics_agendas');
            $table->json('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agandes_topic_form');
    }
};
