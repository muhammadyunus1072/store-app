<?php

namespace App\Repositories\Logistic\Transaction\ProductDetail;

use App\Models\Logistic\Master\Warehouse\Warehouse;
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
            'display_racks.name as display_rack_name',
            'products.name as product_name',
            'unit_details.name as unit_detail_name',
        )
            ->join('product_details', function ($join) {
                $join->on('product_details.id', '=', 'product_detail_histories.product_detail_id');
            })
            ->join('companies', function ($join) {
                $join->on('companies.id', '=', 'product_details.company_id');
            })
            ->leftJoin('warehouses', function ($join) {
                $join->on('warehouses.id', '=', 'product_details.location_id');
            })
            ->leftJoin('display_racks', function ($join) {
                $join->on('display_racks.id', '=', 'product_details.location_id');
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
            ->where('transaction_date', '<=', "$thresholdDate 23:59:59")
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();
    }

    public static function getLastHistories(
        $productId,
        $thresholdDate,
        $companyId = null,
        $warehouseId = null,
    ) {
        $queryStockRowNumber = ProductDetailHistory::select(
            'product_detail_id',
            'last_stock',
            DB::raw('ROW_NUMBER() OVER (PARTITION BY product_detail_id ORDER BY id DESC) as rn'),
        )
            ->where('transaction_date', '<=', "$thresholdDate 23:59:59")
            ->whereHas('productDetail', function ($query) use ($productId, $companyId, $warehouseId) {
                $query->where('product_id', $productId)
                    ->when($companyId != null, function ($query) use ($companyId) {
                        $query->where('company_id', $companyId);
                    })
                    ->when($warehouseId != null, function ($query) use ($warehouseId) {
                        $query->where('location_id', $warehouseId)
                        ->where('location_type', Warehouse::class);
                    });
            });

        return DB::table($queryStockRowNumber, "histories")
            ->select(
                'product_detail_id',
                'last_stock',
            )
            ->where('rn', '=', 1)
            ->get();
    }
    public static function queryLastStock($thresholdDate, $groupBy, $whereClause = [])
    {
        $query = ProductDetailHistory::select(
            'product_detail_histories.last_stock',
            'product_details.price',
            DB::raw('ROW_NUMBER() OVER (PARTITION BY product_detail_id ORDER BY product_detail_histories.transaction_date DESC, product_detail_histories.id DESC) as rn')
        )
            ->join('product_details', function ($join) {
                $join->on('product_details.id', '=', 'product_detail_histories.product_detail_id')
                    ->whereNull('product_details.deleted_at');
            })
            ->where('transaction_date', '<=', "$thresholdDate 23:59:59");
    
        foreach ($whereClause as $col) {
            $query->where($col[0], $col[1], $col[2]);
        }
    
        foreach ($groupBy as $column) {
            if (!in_array($column, ['price', 'last_stock'])) {
                $query->addSelect($column);
            }
        }
    
        $query = DB::query()->fromSub($query, 'stocks')
            ->select(
                DB::raw('SUM(last_stock) as quantity'),
                DB::raw('SUM(last_stock * price) as value')
            );
    
        foreach ($groupBy as $column) {
            if ($column == 'code' || $column == 'batch') {
                $query->addSelect(DB::raw("COALESCE($column, '') as $column"));
            } elseif ($column == 'expired_date') {
                $query->addSelect(DB::raw("COALESCE($column, '1970-01-01') as $column"));
            } else {
                $query->addSelect($column);
            }
    
            $query->groupBy($column);
        }
    
        $query->where('rn', '=', 1);
    
        return $query;
    }
    
    public static function querySumTransactions($dateStart, $dateEnd, $remarksTypes, $groupBy, $whereClause = [])
    {
        $query = ProductDetailHistory::whereBetween('transaction_date', ["$dateStart 00:00:00", "$dateEnd 23:59:59"])
            ->join('product_details', function ($join) {
                $join->on('product_details.id', '=', 'product_detail_histories.product_detail_id')
                    ->whereNull('product_details.deleted_at');
            });
    
        // Handle Where Clause
        foreach ($whereClause as $col) {
            $query->where($col[0], $col[1], $col[2]);
        }
    
        // Handle Remarks Type
        foreach ($remarksTypes as $key => $columns) {
            $conditions = [];
    
            foreach ($columns as $col) {
                $value = is_numeric($col[2]) ? $col[2] : DB::getPdo()->quote($col[2]);
                $conditions[] = "{$col[0]} {$col[1]} {$value}";
            }
    
            $whereCase = implode(' AND ', $conditions);
    
            $query->addSelect(
                DB::raw("COALESCE(SUM(CASE WHEN $whereCase THEN quantity ELSE 0 END), 0) AS quantity_$key")
            );
    
            $query->addSelect(
                DB::raw("COALESCE(SUM(CASE WHEN $whereCase THEN quantity * price ELSE 0 END), 0) AS value_$key")
            );
        }
    
        // Handle Group By
        foreach ($groupBy as $column) {
            if ($column === 'code' || $column === 'batch') {
                $query->addSelect(DB::raw("COALESCE($column, '') as $column"));
            } elseif ($column === 'expired_date') {
                $query->addSelect(DB::raw("COALESCE($column, '1970-01-01') as $column")); // Adjust from '0001-01-01'
            } else {
                $query->addSelect($column);
            }
    
            $query->groupBy($column);
        }
    
        return $query;
    }
    
}
