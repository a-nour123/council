<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('session_attendance_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('sessions')->onDelete('cascade'); // Assuming 'sessions' is the table for sessions
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->comment('1=attendance, 2=absent with reason, 3=absent')->nullable();
            $table->string('actual_status')->comment('1=attendance, 2=absent with reason, 3=absent')->nullable();
            $table->text('absent_reason')->nullable();
            $table->boolean('taken')->default(0)->comment('1 = absent taken, 0 = absent not taken');
            $table->boolean('apply_signiture')->default(0)->comment('0 = no signiture, 1 = user apply signiture',"2 = user refuse apply signiture'");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_attendance_invites');
    }
};
