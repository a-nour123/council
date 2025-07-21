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
        Schema::create('sessions', function (Blueprint $table) {
                $table->id();
                $table->string('code');
                $table->string('place');
                $table->string('reject_reason')->nullable();
                $table->integer('status')->default(0)->comment('0=pending, 1=accepted, 2=Rejected, 3=Reject with reason');
                $table->integer('order');
                // $table->integer('approval')->comment('1=approved, 2=rejected')->nullable();
                $table->string('decision_by')->comment('0=Members, 1=Secretary of the Department Council')->nullable();
                // $table->unsignedBigInteger('topic_id');
                $table->unsignedBigInteger('responsible_id');
                $table->unsignedBigInteger('department_id');
                // $table->foreign('topic_id')->references('topic_id')->on('topics_agendas')->onDelete('cascade');
                $table->foreign('responsible_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
                $table->unsignedBigInteger('created_by');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->dateTime('start_time');
                $table->float('total_hours');
                $table->dateTime('scheduled_end_time');
                $table->dateTime('actual_end_time')->nullable();
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
