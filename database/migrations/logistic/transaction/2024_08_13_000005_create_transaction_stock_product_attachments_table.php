<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_stock_product_attachments', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_transaction_stock_product_attachments', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaction_stock_product_attachments');
        Schema::dropIfExists('_history_transaction_stock_product_attachments');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('transaction_stock_product_id', 'tspa_transaction_stock_product_id_idx');
            
            $table->index('remarks_id', 'tspa_remarks_id_idx');
            $table->index('remarks_type', 'tspa_remarks_type_idx');
        }

        $table->unsignedBigInteger("transaction_stock_product_id")->comment('Transaction Stock Product ID');
        $table->string('file_name')->comment('Nama File Yang Tersimpan');
        $table->string('original_file_name')->comment('Nama Asli File');
        $table->text('note')->nullable()->comment('Catatan');

        $table->unsignedBigInteger("remarks_id")->nullable();
        $table->string('remarks_type')->nullable();

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
