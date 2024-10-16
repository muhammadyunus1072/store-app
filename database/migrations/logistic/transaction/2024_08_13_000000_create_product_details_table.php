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
        Schema::create('product_details', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_product_details', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_details');
        Schema::dropIfExists('_history_product_details');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('product_id', 'product_details_product_id_idx');
            $table->index('company_id', 'product_details_company_id_idx');
            $table->index('warehouse_id', 'product_details_warehouse_id_idx');
            $table->index('entry_date', 'product_details_entry_date_idx');
            $table->index('expired_date', 'product_details_expired_date_idx');
            $table->index('batch', 'product_details_batch_idx');
            $table->index('price', 'product_details_price_idx');
            $table->index('code', 'product_details_code_idx');
            $table->index('remarks_id', 'product_details_remarks_id_idx');
            $table->index('remarks_type', 'product_details_remarks_type_idx');
            $table->index('remarks_note', 'product_details_remarks_note_idx');
        }

        $table->bigInteger("product_id")->unsigned()->comment('Product ID');
        $table->bigInteger("company_id")->unsigned()->comment('Company ID');
        $table->bigInteger("warehouse_id")->unsigned()->comment('Warehouse ID');
        $table->dateTime('entry_date')->comment('Tanggal Masuk Produk');
        $table->date('expired_date')->nullable()->comment('Expired Date Produk');
        $table->string('batch')->nullable()->comment('Batch Produk');
        $table->double('price')->nullable()->comment('Harga Modal');
        $table->string('code')->nullable()->comment('Kode Produk');
        
        $table->bigInteger('remarks_id')->unsigned()->nullable()->comment('FK Polimorfik Penyebab Terbentuk Pertama Kali');
        $table->string('remarks_type')->nullable()->comment('Jenis Polimorfik Penyebab Terbentuk Pertama Kali');
        $table->string('remarks_note')->nullable()->comment('Catatan Polimorfik Penyebab Terbentuk Pertama Kali');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
