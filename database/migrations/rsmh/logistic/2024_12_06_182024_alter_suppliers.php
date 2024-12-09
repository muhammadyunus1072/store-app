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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('kode_simrs')->nullable();
        });
        Schema::table('_history_suppliers', function (Blueprint $table) {
            $table->string('kode_simrs')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('kode_simrs');
        });
        Schema::table('_history_suppliers', function (Blueprint $table) {
            $table->dropColumn('kode_simrs');
        });
    }
};
