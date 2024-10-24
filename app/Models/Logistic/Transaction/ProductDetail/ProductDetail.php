<?php

namespace App\Models\Logistic\Transaction\ProductDetail;

use Sis\TrackHistory\HasTrackHistory;
use App\Models\Core\Company\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Logistic\Transaction\ProductStock\ProductStockDetail;
use App\Models\Logistic\Transaction\ProductStock\ProductStockWarehouse;

class ProductDetail extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'product_id',
        'company_id',
        'warehouse_id',
        'entry_date',
        'expired_date',
        'batch',
        'price',
        'code',
        'remarks_id',
        'remarks_type',
        'remarks_note',
    ];

    protected static function onBoot()
    {
        self::deleted(function ($model) {
            foreach ($model->histories as $item) {
                $item->delete();
            }
        });
    }

    /*
    | RELATIONSHIP
    */

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    public function histories()
    {
        return $this->hasMany(ProductDetailHistory::class, 'product_detail_id', 'id');
    }

    public function attachments()
    {
        return $this->hasMany(ProductDetailAttachment::class, 'product_detail_id', 'id');
    }

    public function productStockDetail()
    {
        return $this->hasOne(ProductStockDetail::class, 'product_detail_id');
    }

    public function productStockWarehouse()
    {
        return $this->hasOne(ProductStockWarehouse::class, 'product_id', 'product_id')
            ->where('warehouse_id', $this->warehouse_id);
    }
}
