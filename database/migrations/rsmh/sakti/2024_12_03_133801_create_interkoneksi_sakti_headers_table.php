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
        Schema::create('interkoneksi_sakti_headers', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_interkoneksi_sakti_headers', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('interkoneksi_sakti_headers');
        Schema::dropIfExists('_history_interkoneksi_sakti_headers');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('kode_upload', 'ish_kode_upload_idx');
            $table->index('no_dokumen', 'ish_no_dokumen_idx');
            $table->index('kode_coa', 'ish_kode_coa_idx');
            $table->index('kode_coa_16', 'ish_kode_coa_16_idx');
        }

        $table->string("kode_upload");
        $table->string("no_dokumen")->nullable() ;
        $table->string("kode_satker")->nullable();
        $table->string("kode_tahun_anggaran")->nullable();
        $table->date("tgl_buku")->nullable();
        $table->date("tgl_dokumen")->nullable();
        $table->string("kategori")->nullable();
        $table->string("nama_penerima")->nullable();
        $table->string("no_rekening")->nullable();
        $table->string("kode_mata_uang")->nullable();
        $table->double("nilai_bast")->nullable();
        $table->string("no_dipa")->nullable();
        $table->date("tgl_dipa")->nullable();
        $table->double("nilai_kurs")->nullable();
        $table->string("kode_coa")->nullable();
        $table->string("npwp")->nullable();
        $table->string("uraian_dokumen")->nullable();
        $table->string("kode_coa_16")->nullable();

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
