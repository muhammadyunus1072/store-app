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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_purchase_orders', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('_history_purchase_orders');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('supplier_id', 'purchase_orders_supplier_id_idx');
            $table->index('company_id', 'purchase_orders_company_id_idx');
            $table->index('warehouse_id', 'purchase_orders_warehouse_id_idx');
            
            $table->index('number', 'purchase_orders_number_idx');
            $table->index('transaction_date', 'purchase_orders_transaction_date_idx');
            $table->index('supplier_invoice_number', 'purchase_orders_supplier_invoice_number_idx');
        }

        $table->string('number')->comment('Nomor');
        $table->dateTime('transaction_date')->comment('Tanggal Transaksi');
        $table->text('note')->nullable()->comment('Catatan');
        $table->string('supplier_invoice_number')->nullable()->comment('Nomor Invoice Dari Supplier');

        // Supplier Info
        $table->unsignedBigInteger("supplier_id")->comment('Supplier ID');
        $table->string('supplier_name')->comment('Nama Supplier');

        // Company Info
        $table->unsignedBigInteger("company_id")->comment('Perusahaan ID');
        $table->string('company_name')->comment('Nama Perusahaan');

        // Warehouse Info
        $table->unsignedBigInteger("warehouse_id")->comment('Warehouse ID');
        $table->string('warehouse_name')->comment('Nama Warehouse');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
