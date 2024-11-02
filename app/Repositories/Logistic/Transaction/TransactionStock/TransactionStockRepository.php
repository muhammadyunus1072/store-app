<?php

namespace App\Repositories\Logistic\Transaction\TransactionStock;

use App\Repositories\MasterDataRepository;
use App\Models\Logistic\Transaction\TransactionStock\TransactionStock;

class TransactionStockRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return TransactionStock::class;
    }

    public static function findOldestNeedToBeProcessed()
    {
        return TransactionStock::whereIn('status', [TransactionStock::STATUS_NOT_PROCESSED, TransactionStock::STATUS_REPROCESSED, TransactionStock::STATUS_DELETE])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->first();
    }

    public static function getNewerTransactions(TransactionStock $transaction)
    {
        return TransactionStock::where('id', '>', $transaction->id)
            ->where('transaction_date', '>=', $transaction->transaction_date)
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }

    public static function getNotProcessedTransactions()
    {
        return TransactionStock::where('status', TransactionStock::STATUS_NOT_PROCESSED)
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }
}
