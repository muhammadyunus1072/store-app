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
        Schema::create('sync_pengeluaran_rts', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_sync_pengeluaran_rts', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sync_pengeluaran_rts');
        Schema::dropIfExists('_history_sync_pengeluaran_rts');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
        }

        $table->boolean('is_done')->default(false)->comment('Penanda Selesai');
        $table->boolean('is_error')->default(false)->comment('Penanda Kesalahan');
        $table->text('error_message')->nullable()->comment('Pesan Kesalahan');
        $table->integer('total')->comment('Total');
        $table->integer('progress')->default(0)->comment('Total Progress');
        $table->unsignedBigInteger("source_warehouse_id")->comment('Warehouse ID');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
