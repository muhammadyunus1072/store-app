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
        Schema::create('interkoneksi_sakti_detail_coas', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_interkoneksi_sakti_detail_coas', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('interkoneksi_sakti_detail_coas');
        Schema::dropIfExists('_history_interkoneksi_sakti_detail_coas');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('kode_upload', 'isdc_kode_upload_idx');
            $table->index('no_dokumen', 'isdc_no_dokumen_idx');
            $table->index('kode_coa', 'isdc_kode_coa_idx');
        }

        $table->string("kode_upload");
        $table->string("no_dokumen")->nullable();
        $table->string("kode_coa")->nullable();
        $table->double("nilai_coa_detail")->nullable();
        $table->double("nilai_valas_detail")->nullable();
        $table->double("vol_sub_output")->nullable();

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
