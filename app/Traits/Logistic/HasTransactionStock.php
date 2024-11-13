<?php

namespace App\Traits\Logistic;

use App\Jobs\Logistic\TransactionStock\CreateUpdateTransactionStockJob;
use App\Jobs\Logistic\TransactionStock\DeleteTransactionStockJob;
use App\Models\Logistic\Transaction\TransactionStock\TransactionStock;
use Carbon\Carbon;

trait HasTransactionStock
{
    abstract public function transactionStockData(): array;

    public function transactionStockProcess()
    {
        $data = $this->transactionStockData();

        CreateUpdateTransactionStockJob::dispatch(
            remarksId: $this->id,
            remarksType: self::class,
            transactionDate: $data['transaction_date'],
            transactionType: $data['transaction_type'],
            sourceCompanyId: $data['source_company_id'],
            sourceWarehouseId: $data['source_warehouse_id'],
            products: $data['products'],
            destinationCompanyId: $data['destination_company_id'],
            destinationWarehouseId: $data['destination_warehouse_id'],
        );
    }

    public function transactionStockCancel()
    {
        DeleteTransactionStockJob::dispatch(
            remarksId: $this->id,
            remarksType: self::class
        );
    }

    public function transactionInfo()
    {
        return $this->number . " / " . Carbon::parse($this->transaction_date)->format('Y-m-d');
    }

    /*
    | RELATIONSHIP
    */
    public function transactionStock()
    {
        return $this->hasOne(TransactionStock::class, 'remarks_id', 'id')->where('remarks_type', self::class);
    }
}
