<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Populate the yearly_calendar_id column with random values between 1 and 3
        DB::table('sessions')->update([
            'yearly_calendar_id' => DB::raw('FLOOR(1 + (RAND() * 3))')
        ]);

        DB::table('faculty_sessions')->update([
            'yearly_calendar_id' => DB::raw('FLOOR(1 + (RAND() * 3))')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally reset the values in the yearly_calendar_id column
        DB::table('sessions')->update(['yearly_calendar_id' => null]);
        DB::table('faculty_sessions')->update(['yearly_calendar_id' => null]);
    }
};
