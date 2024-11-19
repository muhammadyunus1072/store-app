<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('kode_simrs')->nullable();
            $table->string('kode_sakti')->nullable();
        });
        Schema::table('_history_products', function (Blueprint $table) {
            $table->string('kode_simrs')->nullable();
            $table->string('kode_sakti')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('kode_simrs');
            $table->dropColumn('kode_sakti');
        });
        Schema::table('_history_products', function (Blueprint $table) {
            $table->dropColumn('kode_simrs');
            $table->dropColumn('kode_sakti');
        });
    }
};
