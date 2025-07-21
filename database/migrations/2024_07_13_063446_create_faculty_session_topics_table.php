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
        Schema::create('faculty_session_topics', function (Blueprint $table) {
            $table->id();

            // Foreign key for session_id
            $table->unsignedBigInteger('faculty_session_id');
            $table->foreign('faculty_session_id')->references('id')->on('faculty_sessions')->onDelete('cascade');

            // Foreign key for topic_agenda_id
            $table->unsignedBigInteger('topic_agenda_id');
            $table->foreign('topic_agenda_id')->references('id')->on('topics_agendas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculty_session_topics');
    }
};
