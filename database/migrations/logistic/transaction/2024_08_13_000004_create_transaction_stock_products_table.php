<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_stock_products', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_transaction_stock_products', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaction_stock_products');
        Schema::dropIfExists('_history_transaction_stock_products');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('transaction_stock_id', 'tsp_transaction_stock_id_idx');
            $table->index('product_id', 'tsp_product_id_idx');
            $table->index('unit_detail_id', 'tsp_unit_detail_id_idx');

            $table->index('remarks_id', 'tsp_remarks_id_idx');
            $table->index('remarks_type', 'tsp_remarks_type_idx');
        }

        $table->unsignedBigInteger("transaction_stock_id")->comment('Transaction Stock ID');

        // Product Information
        $table->bigInteger("product_id")->unsigned()->comment('Product ID');
        $table->string('product_name')->comment('Product Nama Produk');
        $table->string('product_type')->comment('Product Tipe Produk');

        // UnitDetail Information
        $table->bigInteger("unit_detail_id")->unsigned()->comment('UnitDetail ID');
        $table->bigInteger("unit_detail_unit_id")->unsigned()->comment('UnitDetail Unit ID');
        $table->boolean('unit_detail_is_main')->default(false)->comment('UnitDetail Satuan Utama');
        $table->string('unit_detail_name')->comment('UnitDetail Satuan');
        $table->decimal('unit_detail_value', 12,2)->comment('UnitDetail Nilai Konversi');

        $table->decimal('quantity', 12,2)->comment('Jumlah Barang');
        $table->string('batch')->nullable()->comment('Kode Produksi Barang');
        $table->decimal('price', 12,2)->nullable()->comment('Harga Beli Satuan Barang');
        $table->string('code')->nullable()->comment('Kode Barang');
        $table->date('expired_date')->nullable()->comment('Tanggal Expired');

        $table->unsignedBigInteger("remarks_id")->nullable();
        $table->string('remarks_type')->nullable();

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
