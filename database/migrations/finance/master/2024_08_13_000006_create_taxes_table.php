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
        Schema::create('taxes', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_taxes', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('taxes');
        Schema::dropIfExists('_history_taxes');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('type', 'taxes_type_idx');
            $table->index('is_active', 'taxes_is_active_idx');
        }

        $table->string('name')->comment('Nama Pajak');
        $table->string('type')->comment('Jenis Pajak');
        $table->decimal('value', 12,2)->comment('Nilai Persen Pajak');
        $table->boolean('is_active')->comment('Aktif / Tidak Aktif');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
