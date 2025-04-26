<?php

namespace App\Repositories\Sales\Transaction;

use App\Models\Sales\Transaction\Transaction;
use App\Repositories\MasterDataRepository;

class TransactionRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return Transaction::class;
    }

    public static function datatable()
    {
        return Transaction::query();
    }
}
