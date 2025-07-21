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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('nickname')->nullable();
            $table->string('ar_name')->nullable();
            $table->string('en_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->unique()->nullable();
            $table->foreignId('faculty_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('position_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('acadimic_rank_id')->nullable()->constrained('acadimic_ranks','id')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments','id')->cascadeOnDelete();
            $table->foreignId('headquarter_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('signature')->nullable(); // Add a nullable string column for the signature
            $table->boolean('is_active')->default(1)->comment('0 = disabled, 1 = active, 2 = pending to accept from head of department');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
