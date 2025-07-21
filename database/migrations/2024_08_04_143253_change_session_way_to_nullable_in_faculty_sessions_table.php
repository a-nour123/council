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
        Schema::table('faculty_sessions', function (Blueprint $table) {
            $table->integer('session_way')->comment('1= topics from departments sessions, 2= topics in general')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faculty_sessions', function (Blueprint $table) {
            $table->dropColumn('session_way');
        });
    }
};
