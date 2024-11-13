<?php

namespace App\Repositories\Logistic\Transaction\StockRequest;

use Illuminate\Support\Facades\Crypt;
use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Transaction\StockRequest\StockRequest;

class StockRequestRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return StockRequest::class;
    }

    public static function datatable()
    {
        return StockRequest::with('transactionStock');
    }
}
