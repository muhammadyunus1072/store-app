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
        Schema::create('user_warehouses', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_user_warehouses', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_warehouses');
        Schema::dropIfExists('_history_user_warehouses');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('user_id', 'user_warehouses_user_id_idx');
            $table->index('warehouse_id', 'user_warehouses_warehouse_id_idx');
        }

        $table->bigInteger("user_id")->unsigned()->comment('User ID');
        $table->bigInteger("warehouse_id")->unsigned()->comment('Warehouse ID');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
