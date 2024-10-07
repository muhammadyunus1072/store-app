<?php

namespace App\Models\Logistic\Transaction;

use App\Helpers\Logistic\StockHelper;
use Illuminate\Support\Facades\Crypt;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Logistic\Transaction\ProductDetail;
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
    ];

    protected static function onBoot()
    {
        self::created(function ($model) {
            StockHelper::calculateStock($model->productDetail->product_id);
            StockHelper::calculateStockDetail($model->product_detail_id);
            StockHelper::calculateStockWarehouse($model->productDetail->product_id, $model->productDetail->warehouse_id);
            StockHelper::calculateStockCompany($model->productDetail->product_id, $model->productDetail->company_id);
            StockHelper::calculateStockCompanyWarehouse($model->productDetail->product_id, $model->productDetail->company_id, $model->productDetail->warehouse_id);
        });

        self::updated(function ($model) {
            StockHelper::calculateStock($model->productDetail->product_id);
            StockHelper::calculateStockDetail($model->product_detail_id);
            StockHelper::calculateStockWarehouse($model->productDetail->product_id, $model->productDetail->warehouse_id);
            StockHelper::calculateStockCompany($model->productDetail->product_id, $model->productDetail->company_id);
            StockHelper::calculateStockCompanyWarehouse($model->productDetail->product_id, $model->productDetail->company_id, $model->productDetail->warehouse_id);
        });

        self::deleted(function ($model) {
            StockHelper::calculateStock($model->productDetail->product_id);
            StockHelper::calculateStockDetail($model->product_detail_id);
            StockHelper::calculateStockWarehouse($model->productDetail->product_id, $model->productDetail->warehouse_id);
            StockHelper::calculateStockCompany($model->productDetail->product_id, $model->productDetail->company_id);
            StockHelper::calculateStockCompanyWarehouse($model->productDetail->product_id, $model->productDetail->company_id, $model->productDetail->warehouse_id);
        });
    }

    /*
    | RELATIONSHIP
    */
    public function productDetail()
    {
        return $this->belongsTo(ProductDetail::class, 'product_detail_id', 'id');
    }
}
