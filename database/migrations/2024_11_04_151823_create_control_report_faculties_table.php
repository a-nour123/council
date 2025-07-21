<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateControlReportFacultiesTable extends Migration
{
    public function up()
    {
        Schema::create('control_report_faculties', function (Blueprint $table) {
            $table->id();
             $table->longText('content'); // Column to store CKEditor data
            $table->unsignedBigInteger('topic_id'); // Add topic_id column
            $table->foreign('topic_id')->references('id')->on('topics')->onDelete('cascade'); // Define foreign key constraint
            $table->longText('topic_formate')->nullable(); // Column to store CKEditor data for topic
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('control_report_faculties');
    }
}

