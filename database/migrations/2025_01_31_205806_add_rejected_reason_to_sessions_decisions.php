<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('session_decisions', function (Blueprint $table) {
            $table->text('rejected_reason')->nullable()->after('approval');
        });

        Schema::table('faculty_session_decisions', function (Blueprint $table) {
            $table->text('rejected_reason')->nullable()->after('approval');
        });
    }

    public function down(): void
    {
        Schema::table('session_decisions', function (Blueprint $table) {
            $table->dropColumn('rejected_reason');
        });

        Schema::table('faculty_session_decisions', function (Blueprint $table) {
            $table->dropColumn('rejected_reason');
        });
    }
};
