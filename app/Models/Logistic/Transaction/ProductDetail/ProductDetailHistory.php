<?php

namespace App\Models\Logistic\Transaction\ProductDetail;

use Sis\TrackHistory\HasTrackHistory;
use App\Helpers\Logistic\Stock\StockHandler;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Transaction\ProductDetail\ProductDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductDetailHistory extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'product_detail_id',
        'transaction_date',
        'quantity',
        'note',
        'remarks_id',
        'remarks_type',
        'remarks_note',
    ];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $productDetail = $model->productDetail()->lockForUpdate()->first();
            $productDetail->last_stock += $model->quantity;
            $productDetail->save();

            $model->start_stock = $productDetail->last_stock - $model->quantity;
            $model->last_stock = $productDetail->last_stock;
        });

        self::updating(function ($model) {
            if ($model->quantity != $model->getOriginal('quantity')) {
                $diffQty = $model->quantity - $model->getOriginal('quantity');

                $productDetail = $model->productDetail()->lockForUpdate()->first();
                $productDetail->last_stock += $diffQty;
                $productDetail->save();

                $model->start_stock = $productDetail->last_stock - $diffQty;
                $model->last_stock = $productDetail->last_stock;
            }
        });

        self::deleted(function ($model) {
            $productDetail = $model->productDetail()->lockForUpdate()->first();
            $productDetail->last_stock -= $model->quantity;
            $productDetail->save();
        });
    }

    /*
    | RELATIONSHIP
    */

    public function remarksTable()
    {
        return $this->belongsTo($this->remarks_type, 'remarks_id', 'id');
    }

    public function remarksMasterTable()
    {
        return $this->remarksTable->masterTable();
    }

    public function product()
    {
        return $this->belongsToMany(Product::class, 'product_details', 'product_detail_id', 'product_id');
    }

    public function productDetail()
    {
        return $this->belongsTo(ProductDetail::class, 'product_detail_id', 'id');
    }
}
