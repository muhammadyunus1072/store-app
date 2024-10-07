<?php

namespace App\Models\Logistic\Transaction;

use App\Models\Core\Company\Company;
use Illuminate\Support\Facades\Crypt;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Logistic\Transaction\ProductStockDetail;
use App\Models\Logistic\Transaction\ProductStockWarehouse;

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
    ];

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
