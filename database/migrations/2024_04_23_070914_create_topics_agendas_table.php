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
        Schema::create('topics_agendas', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->unsignedBigInteger('topic_id');
            $table->foreign('topic_id')->references('id')->on('topics');
            $table->unsignedBigInteger('faculty_id');
            $table->foreign('faculty_id')->references('id')->on('faculties');
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreignId('department_id')->constrained('departments', 'id')->onDelete('cascade');
            $table->boolean('status')->default(0)->comment('0 = pending, 1 = accepted, 2 = rejected');
            $table->integer('classification_reference')->comment("1 refer to mix , 2 refer to departement , 3 refer to the college")->nullable();
            $table->integer('escalation_authority')->comment("1 refer to departement , 2 refer to college")->nullable();
            $table->integer('order');
            $table->integer('updates')->nullable()->default(0)
                ->comment('0 = pending, 1 = accepted from head_of_dep, 2 = rejected from head_of_dep, 3 = accepted from department_council, 4 = rejected from department_council, 5 = accepted from faculty_council, 6 = rejected from faculty_council');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics_agendas');
    }
};
