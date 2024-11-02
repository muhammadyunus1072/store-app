<?php

namespace App\Models\Logistic\Transaction\TransactionStock;

use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Unit\UnitDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sis\TrackHistory\HasTrackHistory;

class TransactionStockProduct extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'transaction_stock_id',
        'product_id',
        'unit_detail_id',
        'quantity',
        'price',
        'code',
        'batch',
        'expired_date',
        'remarks_id',
        'remarks_type'
    ];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model = $model->product->saveInfo($model);
            $model = $model->unitDetail->saveInfo($model);
        });

        self::updating(function ($model) {
            if ($model->product_id != $model->getOriginal('product_id')) {
                $model = $model->product->saveInfo($model);
            }
            if ($model->unit_detail_id != $model->getOriginal('unit_detail_id')) {
                $model = $model->unitDetail->saveInfo($model);
            }
        });

        self::deleted(function ($model) {
            foreach ($model->attachments as $item) {
                $item->delete();
            }
        });
    }

    /*
    | RELATIONSHIP
    */
    public function transactionStock()
    {
        return $this->belongsTo(TransactionStock::class, 'transaction_stock_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function unitDetail()
    {
        return $this->belongsTo(UnitDetail::class, 'unit_detail_id', 'id');
    }

    public function attachments()
    {
        return $this->hasMany(TransactionStockProductAttachment::class, 'transaction_stock_product_id', 'id');
    }
}
