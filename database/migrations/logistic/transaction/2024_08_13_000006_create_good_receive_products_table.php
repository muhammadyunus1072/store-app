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
        Schema::create('good_receive_products', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_good_receive_products', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('good_receive_products');
        Schema::dropIfExists('_history_good_receive_products');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('good_receive_id', 'grp_good_receive_id_idx');
            $table->index('purchase_order_product_id', 'grp_purchase_order_product_id_idx');
            $table->index('product_id', 'grp_product_id_idx');
            $table->index('unit_detail_id', 'grp_unit_detail_id_idx');
        }

        $table->bigInteger("good_receive_id")->unsigned()->comment('GoodReceive ID');
        $table->bigInteger("purchase_order_product_id")->unsigned()->nullable()->comment('PurchaseOrderProduct ID');

        // Product Information
        $table->bigInteger("product_id")->unsigned()->comment('Product ID');
        $table->bigInteger("product_unit_id")->unsigned()->comment('Product Unit ID');
        $table->string('product_name')->comment('Product Nama Produk');
        $table->string('product_type')->comment('Product Tipe Produk');
        
        // UnitDetail Information
        $table->bigInteger("unit_detail_id")->unsigned()->comment('UnitDetail ID');
        $table->bigInteger("unit_detail_unit_id")->unsigned()->comment('UnitDetail Unit ID');
        $table->boolean('unit_detail_is_main')->default(false)->comment('UnitDetail Satuan Utama');
        $table->string('unit_detail_name')->comment('UnitDetail Satuan');
        $table->double('unit_detail_value')->comment('UnitDetail Nilai Konversi');

        $table->string('batch')->nullable()->comment('Kode Produksi Barang');
        $table->double('quantity')->comment('Jumlah Barang Diterima');
        $table->double('price')->comment('Harga Beli Satuan Barang');
        $table->string('code')->nullable()->comment('Kode Barang');
        $table->date('expired_date')->nullable()->comment('Tanggal Expired');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
