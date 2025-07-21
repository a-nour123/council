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
        Schema::create('ldap_settings', function (Blueprint $table) {
            $table->id();
            $table->string('hosts');
            $table->integer('port');
            $table->string('base_dn');
            $table->string('username');
            $table->string('filter')->nullable();
            $table->integer('version');
            $table->string('password');
            $table->integer('timeout');
            $table->boolean('follow')->default(false);
            $table->boolean('ssl')->default(false);
            $table->boolean('tls')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ldap_settings');
    }
};
