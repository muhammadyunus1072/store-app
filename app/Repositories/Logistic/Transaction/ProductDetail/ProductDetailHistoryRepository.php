<?php

namespace App\Repositories\Logistic\Transaction\ProductDetail;

use App\Models\Logistic\Transaction\ProductDetail\ProductDetailHistory;
use Illuminate\Support\Facades\DB;
use App\Repositories\MasterDataRepository;

class ProductDetailHistoryRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ProductDetailHistory::class;
    }

    public static function datatableByRemarks($remarks)
    {
        if (count($remarks) == 0) {
            return ProductDetailHistory::where('id', 0);
        }

        return ProductDetailHistory::select(
            'product_detail_histories.id',
            'product_detail_histories.product_detail_id',
            'product_detail_histories.transaction_date',
            'product_detail_histories.start_stock',
            'product_detail_histories.quantity',
            'product_detail_histories.last_stock',
            'product_details.price',
            'product_details.entry_date',
            'product_details.expired_date',
            'product_details.batch',
            'product_details.code',
            'companies.name as company_name',
            'warehouses.name as warehouse_name',
            'products.name as product_name',
            'unit_details.name as unit_detail_name',
        )
            ->join('product_details', function ($join) {
                $join->on('product_details.id', '=', 'product_detail_histories.product_detail_id');
            })
            ->join('companies', function ($join) {
                $join->on('companies.id', '=', 'product_details.company_id');
            })
            ->join('warehouses', function ($join) {
                $join->on('warehouses.id', '=', 'product_details.warehouse_id');
            })
            ->join('products', function ($join) {
                $join->on('products.id', '=', 'product_details.product_id');
            })
            ->join('units', function ($join) {
                $join->on('products.unit_id', '=', 'units.id');
            })
            ->join('unit_details', function ($join) {
                $join->on('units.id', '=', 'unit_details.unit_id')->where('is_main', 1);
            })
            ->when(count($remarks) > 0, function ($query) use ($remarks) {
                $query->where(function ($query) use ($remarks) {
                    foreach ($remarks as $remark) {
                        $query->orWhere(function ($query) use ($remark) {
                            $query->where('product_detail_histories.remarks_id', $remark['id'])
                                ->where('product_detail_histories.remarks_type', $remark['type']);
                        });
                    }
                });
            });
    }

    public static function findLastHistory(
        $productDetailId,
        $thresholdDate,
    ) {
        return ProductDetailHistory::where('product_detail_id', $productDetailId)
            ->where('transaction_date', '<=', $thresholdDate)
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();
    }

    public static function getLastHistories(
        $productId,
        $companyId,
        $warehouseId,
        $thresholdDate,
    ) {
        $queryStockRowNumber = ProductDetailHistory::select(
            'product_detail_id',
            'last_stock',
            DB::raw('ROW_NUMBER() OVER (PARTITION BY product_detail_id ORDER BY id DESC) as rn'),
        )
            ->whereHas('productDetail', function ($query) use ($productId, $companyId, $warehouseId) {
                $query->where('product_id', $productId)
                    ->where('company_id', $companyId)
                    ->where('warehouse_id', $warehouseId);
            })
            ->where('transaction_date', '<=', $thresholdDate);

        return DB::table($queryStockRowNumber, "histories")
            ->select(
                'product_detail_id',
                'last_stock',
            )
            ->where('last_stock', '>', 0)
            ->where('rn', '=', 1)
            ->get();
    }
}
