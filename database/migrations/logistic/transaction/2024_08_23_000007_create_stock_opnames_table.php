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
        Schema::create('stock_opnames', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_stock_opnames', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_opnames');
        Schema::dropIfExists('_history_stock_opnames');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('location_id', 'stock_opnames_location_id_idx');
            $table->index('location_type', 'stock_opnames_location_type_idx');
            $table->index('location_name', 'stock_opnames_location_name_idx');
            $table->index('company_id', 'stock_opnames_company_id_idx');
            $table->index('company_name', 'stock_opnames_company_name_idx');
            $table->index('stock_opname_date', 'stock_opnames_stock_opname_date_idx');
            $table->index('status', 'stock_opnames_status_idx');
        }
        $table->string('number')->comment('Nomor Stock Opname');
        // Location Info
        $table->bigInteger("location_id")->unsigned()->comment('Lokasi ID');
        $table->string('location_type')->comment('Jenis Lokasi');
        $table->string('location_name')->comment('Nama Lokasi');
        // Company Info
        $table->bigInteger("company_id")->unsigned()->comment('Perusahaan ID');
        $table->string('company_name')->comment('Nama Perusahaan');
        
        $table->dateTime('stock_opname_date')->comment('Tanggal Stok Opname');
        $table->string('status')->comment('Status Stok Opname');
        $table->text('note')->nullable()->comment('Catatan');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
