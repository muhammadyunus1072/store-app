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
        Schema::create('transaction_details', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_transaction_details', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaction_details');
        Schema::dropIfExists('_history_transaction_details');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('transaction_id', 'transaction_details_transaction_id_idx');
            $table->index('product_name', 'transaction_details_product_name_idx');
            $table->index('product_plu', 'transaction_details_product_plu_idx');
        }

        $table->unsignedBigInteger('transaction_id')->comment('Transaction ID');

        // Product Information
        $table->unsignedBigInteger('product_id')->comment('Product ID');
        $table->string('product_plu')->comment('PLU Produk');
        $table->string('product_name')->comment('Nama Produk');
        $table->string('product_type')->comment('Tipe Produk');

        // Product Unit Information
        $table->unsignedBigInteger('product_unit_id')->comment('Product Unit ID');
        $table->bigInteger("product_unit_unit_id")->unsigned()->comment('Unit ID');
        $table->bigInteger("product_unit_unit_detail_id")->unsigned()->comment('Unit Detail ID');
        $table->boolean('product_unit_unit_detail_is_main')->default(false)->comment('Penanda Unit Detail Satuan Utama');
        $table->string('product_unit_unit_detail_name')->comment('Unit Detail Satuan');
        $table->decimal('product_unit_unit_detail_value', 12,2)->comment('Unit Detail Nilai Konversi');
        $table->decimal('product_unit_selling_price', 12,2)->default(0)->comment('Harga Jual');
        $table->string('product_unit_code')->nullable()->comment('Barcode Barang');


        // Unit Detail Information
        $table->decimal('quantity', 12,2)->comment('Jumlah Barang');

        // Main Unit Detail Information
        $table->decimal('converted_quantity', 12,2)->comment('Konversi Jumlah Barang');
        $table->decimal('converted_price', 12,2)->comment('Konversi Harga Barang');
        
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
