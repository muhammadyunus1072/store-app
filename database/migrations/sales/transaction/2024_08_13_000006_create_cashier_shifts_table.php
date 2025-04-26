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
        Schema::create('cashier_shifts', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_cashier_shifts', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cashier_shifts');
        Schema::dropIfExists('_history_cashier_shifts');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('user_id', 'cashier_shifts_user_id_idx');
            $table->index('start_at', 'cashier_shifts_start_at_idx');
            $table->index('closed_at', 'cashier_shifts_closed_at_idx');
        }

        $table->unsignedBigInteger('user_id')->comment('User ID');
        $table->dateTime('start_at')->comment('Waktu Buka');
        $table->dateTime('closed_at')->nullable()->comment('Waktu Tutup');
        $table->decimal('opening_balance', 15, 2)->default(0)->comment('Saldo Awal');
        $table->decimal('closing_balance', 15, 2)->nullable()->comment('Saldo Akhir');
        $table->text('notes')->nullable()->comment('User ID');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
