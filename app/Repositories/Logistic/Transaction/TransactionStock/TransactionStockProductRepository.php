<?php

namespace App\Repositories\Logistic\Transaction\TransactionStock;

use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Transaction\TransactionStock\TransactionStockProduct;

class TransactionStockProductRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return TransactionStockProduct::class;
    }
}
