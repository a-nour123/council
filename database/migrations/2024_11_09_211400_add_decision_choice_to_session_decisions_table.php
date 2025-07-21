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
        Schema::table('session_decisions', function (Blueprint $table) {
            $table->string('decisionChoice')->nullable(); // Adds the 'decisionChoice' column, adjust the type if needed
        });
    }
    
    public function down()
    {
        Schema::table('session_decisions', function (Blueprint $table) {
            $table->dropColumn('decisionChoice');
        });
    }
};
