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
        Schema::create('product_detail_histories', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_product_detail_histories', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_detail_histories');
        Schema::dropIfExists('_history_product_detail_histories');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('product_detail_id', 'pdh_product_detail_id_idx');
            $table->index('transaction_date', 'pdh_transaction_date_idx');
            $table->index('last_stock', 'pdh_last_stock_idx');
            $table->index('remarks_id', 'pdh_remarks_id_idx');
            $table->index('remarks_type', 'pdh_remarks_type_idx');
            $table->index('remarks_note', 'pdh_remarks_note_idx');
        }

        $table->bigInteger("product_detail_id")->unsigned()->comment('ProductDetail ID');
        $table->dateTime('transaction_date')->comment('Tanggal dan Waktu Transaksi');
        $table->text('note')->nullable()->comment('Catatan');
        $table->decimal('start_stock', 12,2)->comment('Stok Awal');
        $table->decimal('quantity', 12,2)->comment('Jumlah Keluar Masuk');
        $table->decimal('last_stock', 12,2)->comment('Stok Akhir');
        
        $table->bigInteger('remarks_id')->nullable()->unsigned()->comment('FK Polimorfik Penyebab Keluar / Masuk');
        $table->string('remarks_type')->nullable()->comment('Jenis Polimorfik Penyebab Keluar / Masuk');
        $table->string('remarks_note')->nullable()->comment('Catatan Polimorfik Penyebab Keluar / Masuk');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
