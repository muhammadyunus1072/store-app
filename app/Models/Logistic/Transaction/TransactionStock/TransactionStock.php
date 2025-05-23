<?php

namespace App\Models\Logistic\Transaction\TransactionStock;

use App\Helpers\Logistic\Stock\StockHandler;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Sis\TrackHistory\HasTrackHistory;

class TransactionStock extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    const EVENT_CREATED = 1;
    const EVENT_UPDATED = 2;
    const EVENT_DELETED = 3;

    const TYPE_ADD = "add";
    const TYPE_TRANSFER = "transfer";
    const TYPE_SUBSTRACT = "substract";
    const TYPE_OPNAME = "opname";
    const TYPE_SALES = "sales";

    const STATUS_NOT_PROCESSED = "Not Processed";
    const STATUS_REPROCESSED = "Reprocess";
    const STATUS_DONE_PROCESSED = "Done Processed";
    const STATUS_DELETE = "Delete";

    protected $fillable = [
        'status',
        'status_message',
        'transaction_type',
        'transaction_date',
        "source_company_id",
        "source_warehouse_id",
        "destination_company_id",
        "destination_location_id",
        "destination_location_type",
        'remarks_id',
        'remarks_type',
    ];

    protected static function onBoot()
    {
        self::deleted(function ($model) {
            foreach ($model->products as $item) {
                $item->delete();
            }
        });
    }

    public function process()
    {
        try {
            $data = $this->prepareData();
            logger('HERE');
            logger($this);
            logger($data);
            logger('end here');
            if ($this->transaction_type == self::TYPE_ADD) {
                StockHandler::add($data);
            } else if ($this->transaction_type == self::TYPE_TRANSFER) {
                StockHandler::transfer($data);
            } else if ($this->transaction_type == self::TYPE_OPNAME) {
                StockHandler::opname($data);
            } else if ($this->transaction_type == self::TYPE_SALES) {
                StockHandler::sales($data);
            } else {
                StockHandler::substract($data);
            }

            // Flag Processed
            $this->status = self::STATUS_DONE_PROCESSED;
            $this->status_message = null;
            $this->save();
        } catch (\Exception $e) {
            throw new \Exception($this->remarks->transactionStockInfo() . " | " . $e->getMessage());
        }
    }

    public function cancel()
    {
        $data = $this->prepareData();

        StockHandler::cancel($data);

        // Flag Not Processed
        $this->status = self::STATUS_NOT_PROCESSED;
        $this->save();
    }

    public function prepareData()
    {
        $data = [];

        foreach ($this->products as $item) {
            $data[] = [
                // Header Information
                'id' => $this->id,
                'transaction_date' => $this->transaction_date,
                'source_company_id' => $this->source_company_id,
                'source_warehouse_id' => $this->source_warehouse_id,
                'destination_company_id' => $this->destination_company_id,
                'destination_location_id' => $this->destination_location_id,
                'destination_location_type' => $this->destination_location_type,

                // Product Information
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_detail_id' => $item->unit_detail_id,
                'price' => $item->price,
                'code' => $item->code,
                'batch' => $item->batch,
                'expired_date' => $item->expired_date,
                'remarks_id' => $item->remarks_id,
                'remarks_type' => $item->remarks_type
            ];
        }

        return $data;
    }

    /*
    | RELATIONSHIP
    */
    public function products()
    {
        return $this->hasMany(TransactionStockProduct::class, 'transaction_stock_id', 'id');
    }

    public function remarks()
    {
        return $this->belongsTo($this->remarks_type, 'remarks_id', 'id');
    }
}
