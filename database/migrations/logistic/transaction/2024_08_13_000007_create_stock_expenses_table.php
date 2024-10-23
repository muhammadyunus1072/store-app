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
        Schema::create('stock_expenses', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_stock_expenses', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_expenses');
        Schema::dropIfExists('_history_stock_expenses');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('company_id', 'stock_expenses_company_id_idx');
            $table->index('warehouse_id', 'stock_expenses_warehouse_id_idx');
            $table->index('number', 'stock_expenses_number_idx');
            $table->index('transaction_date', 'stock_expenses_transaction_date_idx');
        }

        // Company Info
        $table->bigInteger("company_id")->unsigned()->comment('Perusahaan ID');
        $table->string('company_name')->comment('Nama Perusahaan');

        // Warehouse Info
        $table->bigInteger("warehouse_id")->unsigned()->comment('Warehouse ID');
        $table->string('warehouse_name')->comment('Nama Warehouse');

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
