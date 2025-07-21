<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('sessions', 'yearly_calendar_id')) {
                $table->unsignedBigInteger('yearly_calendar_id')->nullable()->after('code');
                $table->foreign('yearly_calendar_id')->references('id')->on('yearly_calendars')->onDelete('set null');
            }
        });

        Schema::table('faculty_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('faculty_sessions', 'yearly_calendar_id')) {
                $table->unsignedBigInteger('yearly_calendar_id')->nullable()->after('code');
                $table->foreign('yearly_calendar_id')->references('id')->on('yearly_calendars')->onDelete('set null');
            }
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign(['yearly_calendar_id']);
            $table->dropColumn('yearly_calendar_id');
        });

        Schema::table('faculty_sessions', function (Blueprint $table) {
            $table->dropForeign(['yearly_calendar_id']);
            $table->dropColumn('yearly_calendar_id');
        });
    }

};
