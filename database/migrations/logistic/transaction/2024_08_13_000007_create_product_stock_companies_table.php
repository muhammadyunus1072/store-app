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
        Schema::create('product_stock_companies', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_product_stock_companies', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_stock_companies');
        Schema::dropIfExists('_history_product_stock_companies');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('product_id', 'product_stock_companies_product_id_idx');
            $table->index('company_id', 'product_stock_companies_company_id_idx');
        }

        $table->bigInteger("product_id")->unsigned()->comment('Product ID');
        $table->bigInteger("company_id")->unsigned()->comment('Company ID');
        $table->double("quantity")->nullable()->comment('Jumlah Stok');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
