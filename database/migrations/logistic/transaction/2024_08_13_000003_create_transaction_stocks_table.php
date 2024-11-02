<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_stocks', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_transaction_stocks', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transaction_stocks');
        Schema::dropIfExists('_history_transaction_stocks');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('status', 'transaction_stocks_status_idx');
            $table->index('transaction_type', 'transaction_stocks_transaction_type_idx');
            $table->index('transaction_date', 'transaction_stocks_transaction_date_idx');

            $table->index('source_company_id', 'transaction_stocks_source_company_id_idx');
            $table->index('source_warehouse_id', 'transaction_stocks_source_warehouse_id_idx');
            $table->index('destination_company_id', 'transaction_stocks_destination_company_id_idx');
            $table->index('destination_warehouse_id', 'transaction_stocks_destination_warehouse_id_idx');

            $table->index('remarks_id', 'transaction_stocks_remarks_id_idx');
            $table->index('remarks_type', 'transaction_stocks_remarks_type_idx');
        }

        $table->string('status');
        $table->text('status_message')->nullable();

        $table->datetime('transaction_date');
        $table->string('transaction_type');

        $table->unsignedBigInteger("source_company_id");
        $table->unsignedBigInteger("source_warehouse_id");
        $table->unsignedBigInteger("destination_company_id")->nullable();
        $table->unsignedBigInteger("destination_warehouse_id")->nullable();

        $table->unsignedBigInteger("remarks_id")->nullable();
        $table->string('remarks_type')->nullable();

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
