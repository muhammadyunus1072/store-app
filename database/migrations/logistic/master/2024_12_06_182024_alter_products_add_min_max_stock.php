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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('min_stock', 12,2)->nullable()->default(null)->comment('Stok Minimal');
            $table->decimal('max_stock', 12,2)->nullable()->default(null)->comment('Stok Maksimal');
        });
        Schema::table('_history_products', function (Blueprint $table) {
            $table->decimal('min_stock', 12,2)->nullable()->default(null)->comment('Stok Minimal');
            $table->decimal('max_stock', 12,2)->nullable()->default(null)->comment('Stok Maksimal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('min_stock');
            $table->dropColumn('max_stock');
        });
        Schema::table('_history_products', function (Blueprint $table) {
            $table->dropColumn('min_stock');
            $table->dropColumn('max_stock');
        });
    }
};
