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
        Schema::table('purchase_orders', function (Blueprint $table) {
            // $table->string('no_spk')->nullable();
            $table->string('supplier_kode_simrs')->nullable();
        });
        Schema::table('_history_purchase_orders', function (Blueprint $table) {
            // $table->string('no_spk')->nullable();
            $table->string('supplier_kode_simrs')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // $table->dropColumn('no_spk');
            $table->dropColumn('supplier_kode_simrs');
        });
        Schema::table('_history_purchase_orders', function (Blueprint $table) {
            // $table->dropColumn('no_spk');
            $table->dropColumn('supplier_kode_simrs');
        });
    }
};
