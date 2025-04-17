<?php

namespace App\Models\Logistic\Transaction\ProductDetail;

use Sis\TrackHistory\HasTrackHistory;
use App\Models\Core\Company\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductDetail extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'product_id',
        'company_id',
        'location_id',
        'location_type',
        'location_note',
        'entry_date',
        'expired_date',
        'batch',
        'price',
        'code',
        'last_stock',
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

    public function location()
    {
        return $this->belongsTo($this->location_type, 'location_id', 'id');
    }

    public function histories()
    {
        return $this->hasMany(ProductDetailHistory::class, 'product_detail_id', 'id');
    }

    public function attachments()
    {
        return $this->hasMany(ProductDetailAttachment::class, 'product_detail_id', 'id');
    }
}
