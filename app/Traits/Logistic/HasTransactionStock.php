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

        CreateUpdateTransactionStockJob::dispatchSync(
            remarksId: $this->id,
            remarksType: self::class,
            transactionDate: $data['transaction_date'],
            transactionType: $data['transaction_type'],
            sourceCompanyId: $data['source_company_id'],
            sourceWarehouseId: $data['source_warehouse_id'],
            products: $data['products'],
            destinationCompanyId: $data['destination_company_id'],
            destinationLocationId: $data['destination_location_id'],
            destinationLocationType: $data['destination_location_type'],
        );
    }

    public function transactionStockCancel()
    {
        DeleteTransactionStockJob::dispatch(
            remarksId: $this->id,
            remarksType: self::class
        );
    }

    public function transactionStockInfo()
    {
        return $this->number . " / " . Carbon::parse($this->transaction_date)->format('Y-m-d');
    }

    public function transactionStockStatus()
    {
        if (empty($this->transactionStock)) {
            return "<div class='badge badge-secondary'>Belum Diproses</div>";
        }

        if ($this->transactionStock->status == TransactionStock::STATUS_NOT_PROCESSED) {
            if ($this->transactionStock->status_message) {
                return "<div class='badge badge-danger'>Terdapat Masalah Proses</div>";
            } else {
                return "<div class='badge badge-secondary'>Belum Diproses</div>";
            }
        } else if ($this->transactionStock->status == TransactionStock::STATUS_REPROCESSED) {
            if ($this->transactionStock->status_message) {
                return "<div class='badge badge-danger'>Terdapat Masalah Proses Ulang</div>";
            } else {
                return "<div class='badge badge-secondary'>Menunggu Proses Ulang</div>";
            }
        } else if ($this->transactionStock->status == TransactionStock::STATUS_DONE_PROCESSED) {
            return "<div class='badge badge-success'>Berhasil Diproses</div>";
        } else if ($this->transactionStock->status == TransactionStock::STATUS_DELETE) {
            return "<div class='badge badge-warning'>Akan Dihapus</div>";
        }
    }

    /*
    | RELATIONSHIP
    */
    public function transactionStock()
    {
        return $this->hasOne(TransactionStock::class, 'remarks_id', 'id')->where('remarks_type', self::class);
    }
}
