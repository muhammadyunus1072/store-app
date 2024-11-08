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
        Schema::create('stock_request_products', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_stock_request_products', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_request_products');
        Schema::dropIfExists('_history_stock_request_products');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('stock_request_id', 'stock_request_products_stock_request_id_idx');
            $table->index('product_id', 'stock_request_products_product_id_idx');
            $table->index('unit_detail_id', 'stock_request_products_unit_detail_id_idx');
        }
        $table->bigInteger("stock_request_id")->unsigned()->comment('StockRequest ID');

        // Product Information
        $table->bigInteger("product_id")->unsigned()->comment('Product ID');
        $table->string('product_name')->comment('Product Nama Produk');
        $table->string('product_type')->comment('Product Tipe Produk');

        // Unit Detail Information
        $table->double('quantity')->comment('Jumlah Barang');
        $table->bigInteger("unit_detail_id")->unsigned()->comment('Unit Detail ID');
        $table->bigInteger("unit_detail_unit_id")->unsigned()->comment('Unit Detail Unit ID');
        $table->boolean('unit_detail_is_main')->default(false)->comment('Penanda Unit Detail Satuan Utama');
        $table->string('unit_detail_name')->comment('Unit Detail Satuan');
        $table->double('unit_detail_value')->comment('Unit Detail Nilai Konversi');

        // Main Unit Detail Information
        $table->double('converted_quantity')->comment('Konversi Jumlah Barang');
        $table->bigInteger("main_unit_detail_id")->unsigned()->comment('Unit Detail ID Utama');
        $table->bigInteger("main_unit_detail_unit_id")->unsigned()->comment('Unit Detail Unit ID Utama');
        $table->boolean('main_unit_detail_is_main')->default(false)->comment('Penanda Unit Detail Satuan Utama');
        $table->string('main_unit_detail_name')->comment('Unit Detail Satuan Utama');
        $table->double('main_unit_detail_value')->comment('Unit Detail Nilai Konversi Utama');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
