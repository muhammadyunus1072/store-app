<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // PRODUCT
        Schema::table('products', function (Blueprint $table) {
            $table->string('kode_simrs')->nullable();
            $table->string('kode_sakti')->nullable();

            $table->double('interkoneksi_sakti_persentase_tkdn')->nullable();
            $table->string('interkoneksi_sakti_kategori_pdn')->nullable();
            $table->unsignedBigInteger('interkoneksi_sakti_kbki_id')->nullable();
            $table->unsignedBigInteger('interkoneksi_sakti_coa_id')->nullable();

            $table->index('kode_simrs', 'products_kode_simrs_idx');
            $table->index('kode_sakti', 'products_kode_sakti_idx');
            $table->index('interkoneksi_sakti_kbki_id', 'products_interkoneksi_sakti_kbki_id_idx');
            $table->index('interkoneksi_sakti_coa_id', 'products_interkoneksi_sakti_coa_id_idx');
        });
        Schema::table('_history_products', function (Blueprint $table) {
            $table->string('kode_simrs')->nullable();
            $table->string('kode_sakti')->nullable();

            $table->double('interkoneksi_sakti_persentase_tkdn')->nullable();
            $table->string('interkoneksi_sakti_kategori_pdn')->nullable();
            $table->unsignedBigInteger('interkoneksi_sakti_kbki_id')->nullable();
            $table->unsignedBigInteger('interkoneksi_sakti_coa_id')->nullable();
        });

        // TRANSACTION STOCK PRODUCT
        Schema::table('transaction_stock_products', function (Blueprint $table) {
            $table->string('product_kode_simrs')->nullable();
            $table->string('product_kode_sakti')->nullable();

            $table->double('product_interkoneksi_sakti_persentase_tkdn')->nullable();
            $table->string('product_interkoneksi_sakti_kategori_pdn')->nullable();
            $table->unsignedBigInteger('product_interkoneksi_sakti_kbki_id')->nullable();
            $table->unsignedBigInteger('product_interkoneksi_sakti_coa_id')->nullable();

            $table->index('kode_simrs', 'tsp_product_kode_simrs_idx');
            $table->index('kode_sakti', 'tsp_product_kode_sakti_idx');
            $table->index('interkoneksi_sakti_kbki_id', 'tsp_product_interkoneksi_sakti_kbki_id_idx');
            $table->index('interkoneksi_sakti_coa_id', 'tsp_product_interkoneksi_sakti_coa_id_idx');
        });
        Schema::table('_history_transaction_stock_products', function (Blueprint $table) {
            $table->string('product_kode_simrs')->nullable();
            $table->string('product_kode_sakti')->nullable();

            $table->double('product_interkoneksi_sakti_persentase_tkdn')->nullable();
            $table->string('product_interkoneksi_sakti_kategori_pdn')->nullable();
            $table->unsignedBigInteger('product_interkoneksi_sakti_kbki_id')->nullable();
            $table->unsignedBigInteger('product_interkoneksi_sakti_coa_id')->nullable();
        });

        // STOCK EXPENSE PRODUCT
        Schema::table('stock_expense_products', function (Blueprint $table) {
            $table->string('product_kode_simrs')->nullable();
            $table->string('product_kode_sakti')->nullable();

            $table->double('product_interkoneksi_sakti_persentase_tkdn')->nullable();
            $table->string('product_interkoneksi_sakti_kategori_pdn')->nullable();
            $table->unsignedBigInteger('product_interkoneksi_sakti_kbki_id')->nullable();
            $table->unsignedBigInteger('product_interkoneksi_sakti_coa_id')->nullable();

            $table->index('kode_simrs', 'sep_product_kode_simrs_idx');
            $table->index('kode_sakti', 'sep_product_kode_sakti_idx');
            $table->index('interkoneksi_sakti_kbki_id', 'sep_product_interkoneksi_sakti_kbki_id_idx');
            $table->index('interkoneksi_sakti_coa_id', 'sep_product_interkoneksi_sakti_coa_id_idx');
        });
        Schema::table('_history_stock_expense_products', function (Blueprint $table) {
            $table->string('product_kode_simrs')->nullable();
            $table->string('product_kode_sakti')->nullable();

            $table->double('product_interkoneksi_sakti_persentase_tkdn')->nullable();
            $table->string('product_interkoneksi_sakti_kategori_pdn')->nullable();
            $table->unsignedBigInteger('product_interkoneksi_sakti_kbki_id')->nullable();
            $table->unsignedBigInteger('product_interkoneksi_sakti_coa_id')->nullable();
        });

        // STOCK REQUEST PRODUCT
        Schema::table('stock_request_products', function (Blueprint $table) {
            $table->string('product_kode_simrs')->nullable();
            $table->string('product_kode_sakti')->nullable();

            $table->double('product_interkoneksi_sakti_persentase_tkdn')->nullable();
            $table->string('product_interkoneksi_sakti_kategori_pdn')->nullable();
            $table->unsignedBigInteger('product_interkoneksi_sakti_kbki_id')->nullable();
            $table->unsignedBigInteger('product_interkoneksi_sakti_coa_id')->nullable();

            $table->index('kode_simrs', 'srp_product_kode_simrs_idx');
            $table->index('kode_sakti', 'srp_product_kode_sakti_idx');
            $table->index('interkoneksi_sakti_kbki_id', 'srp_product_interkoneksi_sakti_kbki_id_idx');
            $table->index('interkoneksi_sakti_coa_id', 'srp_product_interkoneksi_sakti_coa_id_idx');
        });
        Schema::table('_history_stock_request_products', function (Blueprint $table) {
            $table->string('product_kode_simrs')->nullable();
            $table->string('product_kode_sakti')->nullable();

            $table->double('product_interkoneksi_sakti_persentase_tkdn')->nullable();
            $table->string('product_interkoneksi_sakti_kategori_pdn')->nullable();
            $table->unsignedBigInteger('product_interkoneksi_sakti_kbki_id')->nullable();
            $table->unsignedBigInteger('product_interkoneksi_sakti_coa_id')->nullable();
        });

        // PURCHASE ORDER PRODUCT
        Schema::table('purchase_order_products', function (Blueprint $table) {
            $table->string('product_kode_simrs')->nullable();
            $table->string('product_kode_sakti')->nullable();

            $table->double('product_interkoneksi_sakti_persentase_tkdn')->nullable();
            $table->string('product_interkoneksi_sakti_kategori_pdn')->nullable();
            $table->unsignedBigInteger('product_interkoneksi_sakti_kbki_id')->nullable();
            $table->unsignedBigInteger('product_interkoneksi_sakti_coa_id')->nullable();

            $table->index('kode_simrs', 'pop_product_kode_simrs_idx');
            $table->index('kode_sakti', 'pop_product_kode_sakti_idx');
            $table->index('interkoneksi_sakti_kbki_id', 'pop_product_interkoneksi_sakti_kbki_id_idx');
            $table->index('interkoneksi_sakti_coa_id', 'pop_product_interkoneksi_sakti_coa_id_idx');
        });
        Schema::table('_history_purchase_order_products', function (Blueprint $table) {
            $table->string('product_kode_simrs')->nullable();
            $table->string('product_kode_sakti')->nullable();

            $table->double('product_interkoneksi_sakti_persentase_tkdn')->nullable();
            $table->string('product_interkoneksi_sakti_kategori_pdn')->nullable();
            $table->unsignedBigInteger('product_interkoneksi_sakti_kbki_id')->nullable();
            $table->unsignedBigInteger('product_interkoneksi_sakti_coa_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // PRODUCT
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_kode_simrs_idx');
            $table->dropIndex('products_kode_sakti_idx');
            $table->dropIndex('products_interkoneksi_sakti_kbki_id_idx');
            $table->dropIndex('products_interkoneksi_sakti_coa_id_idx');

            $table->dropColumn('kode_simrs');
            $table->dropColumn('kode_sakti');

            $table->dropColumn('interkoneksi_sakti_persentase_tkdn');
            $table->dropColumn('interkoneksi_sakti_kategori_pdn');
            $table->dropColumn('interkoneksi_sakti_kbki_id');
            $table->dropColumn('interkoneksi_sakti_coa_id');
        });
        Schema::table('_history_products', function (Blueprint $table) {
            $table->dropColumn('kode_simrs');
            $table->dropColumn('kode_sakti');

            $table->dropColumn('interkoneksi_sakti_persentase_tkdn');
            $table->dropColumn('interkoneksi_sakti_kategori_pdn');
            $table->dropColumn('interkoneksi_sakti_kbki_id');
            $table->dropColumn('interkoneksi_sakti_coa_id');
        });

        // TRANSACTION STOCK PRODUCT
        Schema::table('transaction_stock_products', function (Blueprint $table) {
            $table->dropIndex('tsp_product_kode_simrs_idx');
            $table->dropIndex('tsp_product_kode_sakti_idx');
            $table->dropIndex('tsp_product_interkoneksi_sakti_kbki_id_idx');
            $table->dropIndex('tsp_product_interkoneksi_sakti_coa_id_idx');

            $table->dropColumn('product_kode_simrs');
            $table->dropColumn('product_kode_sakti');

            $table->dropColumn('product_interkoneksi_sakti_persentase_tkdn');
            $table->dropColumn('product_interkoneksi_sakti_kategori_pdn');
            $table->dropColumn('product_interkoneksi_sakti_kbki_id');
            $table->dropColumn('product_interkoneksi_sakti_coa_id');
        });
        Schema::table('_history_transaction_stock_products', function (Blueprint $table) {
            $table->dropColumn('product_kode_simrs');
            $table->dropColumn('product_kode_sakti');

            $table->dropColumn('product_interkoneksi_sakti_persentase_tkdn');
            $table->dropColumn('product_interkoneksi_sakti_kategori_pdn');
            $table->dropColumn('product_interkoneksi_sakti_kbki_id');
            $table->dropColumn('product_interkoneksi_sakti_coa_id');
        });

        // STOCK EXPENSE PRODUCT
        Schema::table('stock_expense_products', function (Blueprint $table) {
            $table->dropIndex('sep_product_kode_simrs_idx');
            $table->dropIndex('sep_product_kode_sakti_idx');
            $table->dropIndex('sep_product_interkoneksi_sakti_kbki_id_idx');
            $table->dropIndex('sep_product_interkoneksi_sakti_coa_id_idx');

            $table->dropColumn('product_kode_simrs');
            $table->dropColumn('product_kode_sakti');

            $table->dropColumn('product_interkoneksi_sakti_persentase_tkdn');
            $table->dropColumn('product_interkoneksi_sakti_kategori_pdn');
            $table->dropColumn('product_interkoneksi_sakti_kbki_id');
            $table->dropColumn('product_interkoneksi_sakti_coa_id');
        });
        Schema::table('_history_stock_expense_products', function (Blueprint $table) {
            $table->dropColumn('product_kode_simrs');
            $table->dropColumn('product_kode_sakti');

            $table->dropColumn('product_interkoneksi_sakti_persentase_tkdn');
            $table->dropColumn('product_interkoneksi_sakti_kategori_pdn');
            $table->dropColumn('product_interkoneksi_sakti_kbki_id');
            $table->dropColumn('product_interkoneksi_sakti_coa_id');
        });

        // STOCK REQUEST PRODUCT
        Schema::table('stock_request_products', function (Blueprint $table) {
            $table->dropIndex('srp_product_kode_simrs_idx');
            $table->dropIndex('srp_product_kode_sakti_idx');
            $table->dropIndex('srp_product_interkoneksi_sakti_kbki_id_idx');
            $table->dropIndex('srp_product_interkoneksi_sakti_coa_id_idx');

            $table->dropColumn('product_kode_simrs');
            $table->dropColumn('product_kode_sakti');

            $table->dropColumn('product_interkoneksi_sakti_persentase_tkdn');
            $table->dropColumn('product_interkoneksi_sakti_kategori_pdn');
            $table->dropColumn('product_interkoneksi_sakti_kbki_id');
            $table->dropColumn('product_interkoneksi_sakti_coa_id');
        });
        Schema::table('_history_stock_request_products', function (Blueprint $table) {
            $table->dropColumn('product_kode_simrs');
            $table->dropColumn('product_kode_sakti');

            $table->dropColumn('product_interkoneksi_sakti_persentase_tkdn');
            $table->dropColumn('product_interkoneksi_sakti_kategori_pdn');
            $table->dropColumn('product_interkoneksi_sakti_kbki_id');
            $table->dropColumn('product_interkoneksi_sakti_coa_id');
        });

        // PURCHASE ORDER PRODUCT
        Schema::table('purchase_order_products', function (Blueprint $table) {
            $table->dropIndex('srp_product_kode_simrs_idx');
            $table->dropIndex('srp_product_kode_sakti_idx');
            $table->dropIndex('srp_product_interkoneksi_sakti_kbki_id_idx');
            $table->dropIndex('srp_product_interkoneksi_sakti_coa_id_idx');

            $table->dropColumn('product_kode_simrs');
            $table->dropColumn('product_kode_sakti');

            $table->dropColumn('product_interkoneksi_sakti_persentase_tkdn');
            $table->dropColumn('product_interkoneksi_sakti_kategori_pdn');
            $table->dropColumn('product_interkoneksi_sakti_kbki_id');
            $table->dropColumn('product_interkoneksi_sakti_coa_id');
        });
        Schema::table('_history_purchase_order_products', function (Blueprint $table) {
            $table->dropColumn('product_kode_simrs');
            $table->dropColumn('product_kode_sakti');

            $table->dropColumn('product_interkoneksi_sakti_persentase_tkdn');
            $table->dropColumn('product_interkoneksi_sakti_kategori_pdn');
            $table->dropColumn('product_interkoneksi_sakti_kbki_id');
            $table->dropColumn('product_interkoneksi_sakti_coa_id');
        });
    }
};
