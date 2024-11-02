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
        Schema::create('stock_expense_products', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_stock_expense_products', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_expense_products');
        Schema::dropIfExists('_history_stock_expense_products');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('stock_expense_id', 'stock_expense_products_stock_expense_id_idx');
            $table->index('product_id', 'stock_expense_products_product_id_idx');
            $table->index('unit_detail_id', 'stock_expense_products_unit_detail_id_idx');
        }
        $table->bigInteger("stock_expense_id")->unsigned()->comment('StockExpense ID');

        // Product Information
        $table->bigInteger("product_id")->unsigned()->comment('Product ID');
        $table->string('product_name')->comment('Product Nama Produk');
        $table->string('product_type')->comment('Product Tipe Produk');
        
        // UnitDetail Information
        $table->bigInteger("unit_detail_id")->unsigned()->comment('UnitDetail ID');
        $table->bigInteger("unit_detail_unit_id")->unsigned()->comment('UnitDetail Unit ID');
        $table->boolean('unit_detail_is_main')->default(false)->comment('UnitDetail Satuan Utama');
        $table->string('unit_detail_name')->comment('UnitDetail Satuan');
        $table->double('unit_detail_value')->comment('UnitDetail Nilai Konversi');

        $table->double('quantity')->comment('Jumlah Barang Diterima');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
