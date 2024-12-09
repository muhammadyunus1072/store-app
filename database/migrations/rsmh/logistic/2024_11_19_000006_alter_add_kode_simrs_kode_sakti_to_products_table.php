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
    }
};
