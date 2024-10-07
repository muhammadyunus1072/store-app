<?php

namespace App\Repositories\Logistic\Transaction\StockExpense;

use Illuminate\Support\Facades\Crypt;
use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Transaction\StockExpense\StockExpenseProduct;

class StockExpenseProductRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return StockExpenseProduct::class;
    }
}
