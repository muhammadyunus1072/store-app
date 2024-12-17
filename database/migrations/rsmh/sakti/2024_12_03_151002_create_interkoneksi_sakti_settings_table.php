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
        Schema::create('interkoneksi_sakti_settings', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_interkoneksi_sakti_settings', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('interkoneksi_sakti_settings');
        Schema::dropIfExists('_history_interkoneksi_sakti_settings');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
        }

        $table->unsignedBigInteger('barang_interkoneksi_sakti_kbki_id')->nullable();
        $table->double("barang_persentase_tkdn")->nullable();
        $table->string("barang_kategori_pdn")->nullable();
        $table->string("barang_kode_uakpb")->nullable();

        $table->double("coa_vol_sub_output")->nullable();

        $table->unsignedBigInteger('header_interkoneksi_sakti_coa_12_id')->nullable();
        $table->string("header_kode_satker")->nullable();
        $table->string("header_kategori")->nullable();
        $table->string("header_nama_penerima")->nullable();
        $table->string("header_no_rekening")->nullable();
        $table->string("header_kode_mata_uang")->nullable();
        $table->double("header_nilai_kurs")->nullable();
        $table->double("header_npwp")->nullable();
        $table->double("header_uraian_dokumen")->nullable();

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
