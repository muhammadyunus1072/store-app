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
        Schema::create('purchase_order_product_taxes', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_purchase_order_product_taxes', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_order_product_taxes');
        Schema::dropIfExists('_history_purchase_order_product_taxes');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('purchase_order_product_id', 'popt_purchase_order_product_id_idx');
            $table->index('tax_id', 'popt_tax_id_idx');
        }

        $table->bigInteger("purchase_order_product_id")->unsigned()->comment('Purchase Order Product ID');

        // Tax Information
        $table->bigInteger("tax_id")->unsigned()->comment('Tax ID');
        $table->string('tax_name')->comment('Nama Pajak');
        $table->string('tax_type')->comment('Jenis Pajak');
        $table->decimal('tax_value', 12,2)->comment('Nilai Persen Pajak');
        $table->boolean('tax_is_active')->comment('Aktif / Tidak Aktif');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
