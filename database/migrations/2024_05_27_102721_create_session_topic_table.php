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
        Schema::create('session_topics', function (Blueprint $table) {
            $table->id();
            // Foreign key for session_id
            $table->unsignedBigInteger('session_id');
            $table->foreign('session_id')->references('id')->on('sessions')->onDelete('cascade');
            // Foreign key for topic_agenda_id
            $table->unsignedBigInteger('topic_agenda_id');
            $table->foreign('topic_agenda_id')->references('id')->on('topics_agendas')->onDelete('cascade');
            // Foreign key for report_template_id
            $table->longText('report_template_content'); // Column to store CKEditor data
            $table->longText('cover_letter_template_content'); // Column to store CKEditor data
            $table->integer('escalation_authority')->comment("1 refer to departement , 2 refer to college")->nullable();
            $table->longText('topic_formate')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_topics');
    }
};
