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
        Schema::create('unit_details', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_unit_details', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('unit_details');
        Schema::dropIfExists('_history_unit_details');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('unit_id', 'unit_details_unit_id_idx');
            $table->index('is_main', 'unit_details_is_main_idx');
        }

        $table->bigInteger("unit_id")->unsigned()->comment('Unit ID');
        $table->boolean('is_main')->default(false)->comment('Satuan Utama');
        $table->string('name')->comment('Satuan');
        $table->decimal('value', 12,2)->comment('Nilai Konversi');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
