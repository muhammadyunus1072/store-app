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
        Schema::create('approvals', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_approvals', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('_history_approvals');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('remarks_id', 'approvals_remarks_id_idx');
            $table->index('remarks_type', 'approvals_remarks_type_idx');
        }

        $table->text('note')->nullable()->comment('Catatan');
        $table->boolean('is_sequentially')->default(false)->comment('Penentu harus berurutan');
        $table->bigInteger("remarks_id")->unsigned()->nullable()->comment('FK Polimorfik');
        $table->string('remarks_type')->nullable()->comment('Jenis Polimorfik');
        $table->json('config')->comment('Konfigurasi Aturan');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
