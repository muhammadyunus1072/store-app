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
        Schema::create('purchase_order_products', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_purchase_order_products', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_order_products');
        Schema::dropIfExists('_history_purchase_order_products');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('purchase_order_id', 'pop_purchase_order_id_idx');
            $table->index('product_id', 'pop_product_id_idx');
            $table->index('unit_detail_id', 'pop_unit_detail_id_idx');
        }

        $table->bigInteger("purchase_order_id")->unsigned()->comment('Purchase Order ID');

        // Product Information
        $table->bigInteger("product_id")->unsigned()->comment('Product ID');
        $table->string('product_name')->comment('Product Nama Produk');
        $table->string('product_type')->comment('Product Tipe Produk');

        // Unit Detail Information
        $table->decimal('price', 12,2)->comment('Harga Beli Satuan Barang');
        $table->decimal('quantity', 12,2)->comment('Jumlah Barang');
        $table->bigInteger("unit_detail_id")->unsigned()->comment('Unit Detail ID');
        $table->bigInteger("unit_detail_unit_id")->unsigned()->comment('Unit Detail Unit ID');
        $table->boolean('unit_detail_is_main')->default(false)->comment('Penanda Unit Detail Satuan Utama');
        $table->string('unit_detail_name')->comment('Unit Detail Satuan');
        $table->decimal('unit_detail_value', 12,2)->comment('Unit Detail Nilai Konversi');

        // Main Unit Detail Information
        $table->decimal('converted_price', 12,2)->comment('Konversi Harga Beli Satuan Barang');
        $table->decimal('converted_quantity', 12,2)->comment('Konversi Jumlah Barang');
        $table->bigInteger("main_unit_detail_id")->unsigned()->comment('Unit Detail ID Utama');
        $table->bigInteger("main_unit_detail_unit_id")->unsigned()->comment('Unit Detail Unit ID Utama');
        $table->boolean('main_unit_detail_is_main')->default(false)->comment('Penanda Unit Detail Satuan Utama');
        $table->string('main_unit_detail_name')->comment('Unit Detail Satuan Utama');
        $table->decimal('main_unit_detail_value', 12,2)->comment('Unit Detail Nilai Konversi Utama');

        // Additional Information
        $table->string('batch')->nullable()->comment('Kode Produksi Barang');
        $table->string('code')->nullable()->comment('Kode Barang');
        $table->date('expired_date')->nullable()->comment('Tanggal Expired');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
