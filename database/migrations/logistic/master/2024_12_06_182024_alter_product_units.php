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
        Schema::table('product_units', function (Blueprint $table) {    
            $table->boolean('unit_detail_is_main')->default(false)->comment('Penanda Unit Detail Satuan Utama');
            $table->string('unit_detail_name')->comment('Unit Detail Satuan');
            $table->decimal('unit_detail_value', 12,2)->comment('Unit Detail Nilai Konversi');
        });
        Schema::table('_history_product_units', function (Blueprint $table) {    
            $table->boolean('unit_detail_is_main')->default(false)->comment('Penanda Unit Detail Satuan Utama');
            $table->string('unit_detail_name')->comment('Unit Detail Satuan');
            $table->decimal('unit_detail_value', 12,2)->comment('Unit Detail Nilai Konversi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->dropColumn('unit_detail_is_main');
            $table->dropColumn('unit_detail_name');
            $table->dropColumn('unit_detail_value');
        });
        Schema::table('_history_product_units', function (Blueprint $table) {
            $table->dropColumn('unit_detail_is_main');
            $table->dropColumn('unit_detail_name');
            $table->dropColumn('unit_detail_value');
        });
    }
};
