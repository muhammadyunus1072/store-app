<?php

namespace App\Repositories\Purchasing\Transaction\PurchaseOrder;

use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrder;
use App\Repositories\MasterDataRepository;

class PurchaseOrderRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return PurchaseOrder::class;
    }

    public static function datatable(
        $dateStart,
        $dateEnd,
        $warehouseId,
        $companyId,
        $productIds,
        $supplierIds,
    ) {
        return PurchaseOrder::with('transactionStock')
            ->whereBetween('transaction_date', ["$dateStart 00:00:00", "$dateEnd 23:59:59"])
            ->when($warehouseId, function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            })
            ->when($companyId, function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->when(is_array($supplierIds) && count($supplierIds) > 0, function ($query) use ($supplierIds) {
                $query->whereIn('supplier_id', $supplierIds);
            })
            ->when(is_array($productIds) && count($productIds) > 0, function ($query) use ($productIds) {
                $query->whereHas('purchaseOrderProducts', function ($query) use ($productIds) {
                    $query->whereIn('product_id', $productIds);
                });
            });
    }

    public static function deleteWithEmptyProducts()
    {
        $data = PurchaseOrder::whereDoesntHave('purchaseOrderProducts')->get();
        foreach ($data as $item) {
            $item->delete();
        }
    }
}
