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

    public static function findWithDetails($id)
    {
        return StockRequest::with([
            'warehouseRequester',    
            'warehouseRequested',    
            'stockRequestProducts',    
        ]
        )->where('id', $id)->first();
    }

    public static function datatable()
    {
        return StockRequest::query();
    }
}
