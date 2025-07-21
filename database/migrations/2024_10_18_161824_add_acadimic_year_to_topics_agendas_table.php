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
        Schema::table('topics_agendas', function (Blueprint $table) {
            $table->foreignId('yearly_calendar_id')->after('topic_id')->nullable()->constrained('yearly_calendars','id')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('topics_agendas', function (Blueprint $table) {
            //
        });
    }
};
