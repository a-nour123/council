<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update the 'ar_name' column in the 'positions' table
        DB::table('positions')
            ->where('id', '=', '1')
            ->update(['ar_name' => 'عضو هيئة تدريس']);
    }

    public function down(): void
    {
        DB::table('positions')
            ->where('id', '=', '1')
            ->update(['ar_name' => 'عضو هيئة تدريس']);
    }
};
