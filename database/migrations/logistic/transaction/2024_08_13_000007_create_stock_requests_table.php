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
            $table->index('destination_company_id', 'stock_requests_destination_company_id_idx');
            $table->index('source_company_id', 'stock_requests_source_company_id_idx');
            $table->index('destination_warehouse_id', 'stock_requests_destination_warehouse_id_idx');
            $table->index('source_warehouse_id', 'stock_requests_source_warehouse_id_idx');
            $table->index('number', 'stock_requests_number_idx');
            $table->index('transaction_date', 'stock_requests_transaction_date_idx');
        }
        // Company Info
        $table->bigInteger("destination_company_id")->unsigned()->comment('Perusahaan Destination ID');
        $table->string('destination_company_name')->comment('Nama Perusahaan Destination');

        // Warehouse Destination Info
        $table->bigInteger("destination_warehouse_id")->unsigned()->comment('Warehouse ID Destination');
        $table->string('destination_warehouse_name')->comment('Nama Warehouse Destination');

        // Company Info
        $table->bigInteger("source_company_id")->unsigned()->comment('Perusahaan Source ID');
        $table->string('source_company_name')->comment('Nama Perusahaan Source');

        // Warehouse Source Info
        $table->bigInteger("source_warehouse_id")->unsigned()->comment('Warehouse ID Source');
        $table->string('source_warehouse_name')->comment('Nama Warehouse Source');

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
