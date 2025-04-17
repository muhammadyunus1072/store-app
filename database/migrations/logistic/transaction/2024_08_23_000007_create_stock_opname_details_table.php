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
        Schema::create('stock_opname_details', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_stock_opname_details', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_opname_details');
        Schema::dropIfExists('_history_stock_opname_details');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('stock_opname_id', 'sod_stock_opname_id_idx');
            $table->index('product_id', 'sod_product_id_idx');
            $table->index('product_name', 'sod_product_name_idx');
            $table->index('product_type', 'sod_product_type_idx');
            $table->index('difference', 'sod_difference_idx');
        }
        // Stock Opname Info
        $table->bigInteger("stock_opname_id")->unsigned()->comment('Stock Opname ID');

        // Data Info

        // Product Information
        $table->bigInteger("product_id")->unsigned()->comment('Product ID');
        $table->string('product_name')->comment('Product Nama Produk');
        $table->string('product_type')->comment('Product Tipe Produk');

        
        // Real Stock Information
        $table->decimal('real_stock', 12,2)->comment('Jumlah Barang');
        $table->bigInteger("real_unit_detail_id")->unsigned()->comment('Unit Detail ID');
        $table->bigInteger("real_unit_detail_unit_id")->unsigned()->comment('Unit Detail Unit ID');
        $table->boolean('real_unit_detail_is_main')->default(false)->comment('Penanda Unit Detail Satuan Utama');
        $table->string('real_unit_detail_name')->comment('Unit Detail Satuan');
        $table->decimal('real_unit_detail_value', 12,2)->comment('Unit Detail Nilai Konversi');

        // Main Unit Detail Information
        $table->decimal('converted_real_stock', 12,2)->comment('Konversi Jumlah Barang');

        // System Stock Information
        $table->decimal("system_stock", 12,2)->comment('Stok Sistem');

        // Selisih Stock System - Real Stock
        $table->decimal("difference", 12,2)->comment('Selisih Stok');
        
        $table->bigInteger("main_unit_detail_id")->unsigned()->comment('Unit Detail ID Utama');
        $table->bigInteger("main_unit_detail_unit_id")->unsigned()->comment('Unit Detail Unit ID Utama');
        $table->boolean('main_unit_detail_is_main')->default(false)->comment('Penanda Unit Detail Satuan Utama');
        $table->string('main_unit_detail_name')->comment('Unit Detail Satuan Utama');
        $table->decimal('main_unit_detail_value', 12,2)->comment('Unit Detail Nilai Konversi Utama');


        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
