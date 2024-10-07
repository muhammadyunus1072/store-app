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
        Schema::create('supplier_categories', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_supplier_categories', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('supplier_categories');
        Schema::dropIfExists('_history_supplier_categories');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        }else{
            $table->index('supplier_id', 'supplier_categories_supplier_id_idx');
            $table->index('category_supplier_id', 'supplier_categories_category_supplier_id_idx');
        }

        $table->bigInteger("supplier_id")->unsigned()->comment('Supplier ID');
        $table->bigInteger("category_supplier_id")->unsigned()->comment('CategorySupplier ID');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
