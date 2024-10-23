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
        Schema::create('stock_requests', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_stock_requests', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_requests');
        Schema::dropIfExists('_history_stock_requests');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('company_requester_id', 'stock_requests_company_requester_id_idx');
            $table->index('company_requested_id', 'stock_requests_company_requested_id_idx');
            $table->index('warehouse_requester_id', 'stock_requests_warehouse_requester_id_idx');
            $table->index('warehouse_requested_id', 'stock_requests_warehouse_requested_id_idx');
            $table->index('number', 'stock_requests_number_idx');
            $table->index('transaction_date', 'stock_requests_transaction_date_idx');
        }
        // Company Info
        $table->bigInteger("company_requester_id")->unsigned()->comment('Perusahaan Requester ID');
        $table->string('company_requester_name')->comment('Nama Perusahaan Requester');

        // Warehouse Requester Info
        $table->bigInteger("warehouse_requester_id")->unsigned()->comment('Warehouse ID Requester');
        $table->string('warehouse_requester_name')->comment('Nama Warehouse Requester');

        // Company Info
        $table->bigInteger("company_requested_id")->unsigned()->comment('Perusahaan Requested ID');
        $table->string('company_requested_name')->comment('Nama Perusahaan Requested');

        // Warehouse Requested Info
        $table->bigInteger("warehouse_requested_id")->unsigned()->comment('Warehouse ID Requested');
        $table->string('warehouse_requested_name')->comment('Nama Warehouse Requested');

        $table->string('number')->comment('Nomor');
        $table->dateTime('transaction_date')->comment('Tanggal Transaksi');
        $table->text('note')->nullable()->comment('Catatan');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
