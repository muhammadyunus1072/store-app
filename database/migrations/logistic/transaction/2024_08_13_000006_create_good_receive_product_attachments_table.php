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
        Schema::create('good_receive_product_attachments', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_good_receive_product_attachments', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('good_receive_product_attachments');
        Schema::dropIfExists('_history_good_receive_product_attachments');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('good_receive_product_id', 'grpa_good_receive_product_id_idx');
        }

        $table->bigInteger("good_receive_product_id")->unsigned()->comment('GoodReceiveProduct ID');
        $table->string('file_name')->comment('Nama File Yang Tersimpan');
        $table->string('original_file_name')->comment('Nama Asli File');
        $table->text('note')->nullable()->comment('Catatan');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
