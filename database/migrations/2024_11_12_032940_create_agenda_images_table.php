<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgendaImagesTable extends Migration
{
    public function up()
    {
        Schema::create('agenda_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agenda_id');
            $table->string('file_path');  // Path to the uploaded file
            $table->timestamps();

            // Foreign key to TopicAgenda
            $table->foreign('agenda_id')->references('id')->on('topics_agendas')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('agenda_images');
    }
}
