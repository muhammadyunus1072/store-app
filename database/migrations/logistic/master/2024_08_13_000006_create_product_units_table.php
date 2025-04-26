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
        Schema::create('product_units', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_product_units', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_units');
        Schema::dropIfExists('_history_product_units');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        }else{
            $table->index('product_id', 'product_units_product_id_idx');
            $table->index('unit_id', 'product_units_unit_id_idx');
            $table->index('unit_detail_id', 'product_units_unit_detail_id_idx');
        }

        $table->bigInteger("product_id")->unsigned()->comment('Product ID');
        $table->bigInteger("unit_id")->unsigned()->comment('Unit ID');
        $table->bigInteger("unit_detail_id")->unsigned()->comment('Unit Detail ID');
        $table->decimal('selling_price', 12,2)->default(0)->comment('Harga Jual');
        $table->string('code')->nullable()->comment('Barcode Barang');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
