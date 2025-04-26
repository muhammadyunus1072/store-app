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
        Schema::create('transactions', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_transactions', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('_history_transactions');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('number', 'transactions_number_idx');
            $table->index('cashier_shift_id', 'transactions_cashier_shift_id_idx');
            $table->index('user_id', 'transactions_user_id_idx');
            $table->index('status', 'transactions_status_idx');
            $table->index('payment_method_id', 'transactions_payment_method_id_idx');
        }

        $table->unsignedBigInteger('cashier_shift_id')->comment('ID Cashier Shift');
        $table->unsignedBigInteger('user_id')->comment('ID User / Kasir');
        $table->string('number')->comment('Nomor Transaksi');

        $table->string('status')->comment('Status transaksi');
        $table->decimal('subtotal', 12, 2)->default(0)->comment('Total sebelum diskon dan biaya administrasi');
        $table->decimal('discount', 12, 2)->default(0)->comment('Total diskon yang diberikan');
        $table->decimal('admin_fee', 12, 2)->default(0)->comment('Biaya administrasi tambahan');
        $table->decimal('grand_total', 12, 2)->default(0)->comment('Total akhir setelah diskon dan biaya administrasi');
        $table->decimal('paid_amount', 12, 2)->default(0)->comment('Jumlah uang yang dibayarkan oleh pelanggan');
        $table->decimal('change_amount', 12, 2)->default(0)->comment('Uang kembalian yang diberikan ke pelanggan');

        $table->text('cancellation_reason')->nullable()->comment('Transaction Caancellation Reason');

        // Payment Method Information
        $table->unsignedBigInteger('payment_method_id')->comment('ID Payment Method');
        $table->string('payment_method_code')->comment('Payment Method Code');
        $table->string('payment_method_name')->comment('Payment Method Name');
        $table->string('payment_method_type')->comment('Payment Method Type');
        $table->decimal('payment_method_amount', 12,2)->comment('Payment Method amount');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
