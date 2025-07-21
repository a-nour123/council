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
        Schema::table('faculty_session_attendance_invites', function (Blueprint $table) {
            $table->boolean('apply_signiture')->default(0)->comment('0 = no signiture, 1 = user apply signiture',"2 = user refuse apply signiture'")->after('taken');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faculty_session_attendance_invites', function (Blueprint $table) {
            //
        });
    }
};
