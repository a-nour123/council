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
        Schema::create('faculty_session_attendance_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_session_id')->constrained('faculty_sessions')->onDelete('cascade'); // Assuming 'faculty_sessions' is the table for faculty_sessions
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->comment('1=attendance, 2=absent with reason, 3=absent')->nullable();
            $table->string('actual_status')->comment('1=attendance, 2=absent with reason, 3=absent')->nullable();
            $table->text('absent_reason')->nullable();
            $table->boolean('taken')->default(0)->comment('1 = absent taken, 0 = absent not taken');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculty_session_attendance_invites');
    }
};
