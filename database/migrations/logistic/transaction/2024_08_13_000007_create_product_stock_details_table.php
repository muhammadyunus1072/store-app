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
        Schema::create('product_stock_details', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_product_stock_details', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_stock_details');
        Schema::dropIfExists('_history_product_stock_details');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('product_detail_id', 'product_stock_details_product_detail_id_idx');
        }

        $table->bigInteger("product_detail_id")->unsigned()->comment('ProductDetail ID');
        $table->double("quantity")->nullable()->comment('Jumlah Stok');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
