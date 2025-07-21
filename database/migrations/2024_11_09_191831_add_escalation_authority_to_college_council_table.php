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
        Schema::table('college_councils', function (Blueprint $table) {
            $table->integer('escalation_authority')->comment("1= refer to departement, 2= refer to college")->nullable()->after('topic_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('college_councils', function (Blueprint $table) {
            //
        });
    }
};
