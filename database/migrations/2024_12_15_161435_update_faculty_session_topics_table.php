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
        Schema::table('faculty_session_topics', function (Blueprint $table) {
            // Add new columns
            $table->longText('cover_letter_template_content')->nullable()->after('report_template_content');
            $table->integer('escalation_authority')->nullable()
                ->comment("1 refers to department, 2 refers to college")->after('cover_letter_template_content');
            $table->longText('topic_formate')->nullable()->after('escalation_authority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faculty_session_topics', function (Blueprint $table) {
            //
        });
    }
};
