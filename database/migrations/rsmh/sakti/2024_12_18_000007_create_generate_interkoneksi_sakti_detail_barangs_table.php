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
        Schema::create('generate_interkoneksi_sakti_detail_barangs', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_generate_interkoneksi_sakti_detail_barangs', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('generate_interkoneksi_sakti_detail_barangs');
        Schema::dropIfExists('_history_generate_interkoneksi_sakti_detail_barangs');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
        }

        $table->boolean('is_done')->default(false)->comment('Penanda Selesai');
        $table->boolean('is_error')->default(false)->comment('Penanda Kesalahan');
        $table->text('error_message')->nullable()->comment('Pesan Kesalahan');
        $table->integer('total')->comment('Total');
        $table->integer('progress')->default(0)->comment('Total Progress');
        $table->unsignedBigInteger('warehouse_id')->comment('Warehouse ID');
        $table->date('date_start')->comment('Tanggal Mulai');
        $table->date('date_end')->comment('Tanggal Akhir');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
