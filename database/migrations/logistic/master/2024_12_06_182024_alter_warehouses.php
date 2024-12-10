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
        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('id_sub')->nullable();
            $table->string('id_bagian')->nullable();
            $table->string('id_direktorat')->nullable();
        });
        Schema::table('_history_warehouses', function (Blueprint $table) {
            $table->string('id_sub')->nullable();
            $table->string('id_bagian')->nullable();
            $table->string('id_direktorat')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('id_sub');
            $table->dropColumn('id_bagian');
            $table->dropColumn('id_direktorat');
        });
        Schema::table('_history_warehouses', function (Blueprint $table) {
            $table->dropColumn('id_sub');
            $table->dropColumn('id_bagian');
            $table->dropColumn('id_direktorat');
        });
    }
};
