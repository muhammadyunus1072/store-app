<?php

namespace App\Repositories\Sales\Transaction;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Product\ProductUnit;
use App\Models\Logistic\Master\Unit\UnitDetail;
use App\Models\Logistic\Transaction\ProductDetail\ProductDetail;

class CashierTransactionRepository
{

    public static function findProductByInput($code, $productId)
    {
        $queryUnits = ProductUnit::select(
            'product_units.unit_id',
            DB::raw("GROUP_CONCAT(CAST(product_units.id AS CHAR) SEPARATOR ';') AS product_unit_ids"),
            DB::raw("GROUP_CONCAT(product_units.selling_price SEPARATOR ';') AS unit_selling_prices"),

            DB::raw("GROUP_CONCAT(CAST(unit_details.id AS CHAR) SEPARATOR ';') AS unit_detail_ids"),
            DB::raw("GROUP_CONCAT(unit_details.name SEPARATOR ';') AS unit_names"),
            DB::raw("GROUP_CONCAT(unit_details.is_main SEPARATOR ';') AS unit_is_mains"),
            DB::raw("GROUP_CONCAT(unit_details.value SEPARATOR ';') AS unit_values"),
        )
        ->join('unit_details', function($j) {
            $j->on('unit_details.id', '=', 'product_units.unit_detail_id')
            ->whereNull('product_units.deleted_at');
        })
        ->groupBy('unit_id');

        return ProductUnit::select(
            'products.id as product_id',
            'products.plu as product_plu',
            'product_units.code',
            'products.name',
            'query_units.unit_detail_ids',
            'query_units.product_unit_ids',
            'query_units.unit_names',
            'query_units.unit_is_mains',
            'query_units.unit_values',
            'query_units.unit_selling_prices',)
        ->when($code, function($query) use($code){
            $query->where('code', '=', $code);
        })
        ->when($productId, function($query) use($productId){
            $query->where('product_id', '=', $productId);
        })
        ->join('products', function($j) {
            $j->on('product_units.product_id', '=', 'products.id')
            ->whereNull('products.deleted_at');
        })
        ->leftJoinSub($queryUnits, 'query_units', function ($join) {
            $join->on('query_units.unit_id', '=', 'products.unit_id');
        })
        ->first();

    }
}
