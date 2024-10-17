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
        self::created(function ($model) {
            StockHandler::calculateStock($model->productDetail->product_id);
            StockHandler::calculateStockDetail($model->product_detail_id);
            StockHandler::calculateStockWarehouse($model->productDetail->product_id, $model->productDetail->warehouse_id);
            StockHandler::calculateStockCompany($model->productDetail->product_id, $model->productDetail->company_id);
            StockHandler::calculateStockCompanyWarehouse($model->productDetail->product_id, $model->productDetail->company_id, $model->productDetail->warehouse_id);
        });

        self::updated(function ($model) {
            StockHandler::calculateStock($model->productDetail->product_id);
            StockHandler::calculateStockDetail($model->product_detail_id);
            StockHandler::calculateStockWarehouse($model->productDetail->product_id, $model->productDetail->warehouse_id);
            StockHandler::calculateStockCompany($model->productDetail->product_id, $model->productDetail->company_id);
            StockHandler::calculateStockCompanyWarehouse($model->productDetail->product_id, $model->productDetail->company_id, $model->productDetail->warehouse_id);
        });

        self::deleted(function ($model) {
            StockHandler::calculateStock($model->productDetail->product_id);
            StockHandler::calculateStockDetail($model->product_detail_id);
            StockHandler::calculateStockWarehouse($model->productDetail->product_id, $model->productDetail->warehouse_id);
            StockHandler::calculateStockCompany($model->productDetail->product_id, $model->productDetail->company_id);
            StockHandler::calculateStockCompanyWarehouse($model->productDetail->product_id, $model->productDetail->company_id, $model->productDetail->warehouse_id);
        });
    }

    /*
    | RELATIONSHIP
    */
    public function product()
    {
        return $this->belongsToMany(Product::class, 'product_details', 'product_detail_id', 'product_id');
    }

    public function productDetail()
    {
        return $this->belongsTo(ProductDetail::class, 'product_detail_id', 'id');
    }
}
