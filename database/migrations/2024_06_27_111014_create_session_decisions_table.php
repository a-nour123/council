<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionDecisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('session_decisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agenda_id');
            $table->unsignedBigInteger('session_id');
            $table->string('decision', 1000);
            $table->integer('approval')->comment('1=approved, 2=rejected')->nullable();
            $table->string('order');
            $table->foreign('agenda_id')->references('id')->on('topics_agendas')->onDelete('cascade');
            $table->integer('topic_id')->nullable();
            $table->foreign('session_id')->references('id')->on('sessions')->onDelete('cascade');
            $table->integer('decision_status')->comment('1=accept with all, 2=refuse with all, 3=accept with more half, 4=refuse with more half, 5=leaves to head of department')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('session_decisions');
    }
}
