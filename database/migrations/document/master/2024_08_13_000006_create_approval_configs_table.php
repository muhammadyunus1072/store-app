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
        Schema::create('approval_configs', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_approval_configs', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('approval_configs');
        Schema::dropIfExists('_history_approval_configs');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('key', 'approval_configs_key_idx');
        }

        $table->string('title')->comment('Judul');
        $table->string('key')->comment('Kunci');
        $table->integer('priority')->comment('Prioritas');
        $table->boolean('is_sequentially')->default(false)->comment('Penentu harus berurutan');
        $table->boolean('is_done_when_all_submitted')->default(false)->comment('Selesai Jika Seluruh Submit Status');
        $table->json('config')->nullable()->comment('Konfigurasi Aturan');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
