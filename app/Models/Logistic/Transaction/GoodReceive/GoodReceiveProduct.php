<?php

namespace App\Models\Logistic\Transaction\GoodReceive;

use App\Models\Finance\Master\Tax;
use App\Helpers\Logistic\StockHelper;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Unit\UnitDetail;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Logistic\Transaction\GoodReceive\GoodReceive;
use App\Models\Logistic\Transaction\GoodReceive\GoodReceiveProductTax;
use App\Models\Logistic\Transaction\GoodReceive\GoodReceiveProductAttachment;
use App\Repositories\Logistic\Transaction\ProductDetailHistory\ProductDetailHistoryRepository;

class GoodReceiveProduct extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'good_receive_id',
        'purchase_order_product_id',
        'product_id',
        'unit_detail_id',
        'quantity',
        'price',
        'code',
        'batch',
        'expired_date',
    ];

    protected $guarded = ['id'];

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
            foreach ($model->goodReceiveOrderProductTaxes as $item) {
                $item->delete();
            }
            foreach ($model->goodReceiveProductAttachments as $item) {
                $item->delete();
            }
        });
    }

    public function isDeletable()
    {
        return true;
    }

    public function isEditable()
    {
        return true;
    }

    /*
    | RELATIONSHIP
    */

    public function goodReceive()
    {
        return $this->belongsTo(GoodReceive::class, 'good_receive_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function unitDetailChoices()
    {
        return $this->hasMany(UnitDetail::class, 'unit_id', 'product_unit_id');
    }

    public function unitDetail()
    {
        return $this->belongsTo(UnitDetail::class, 'unit_detail_id', 'id');
    }

    public function ppn(): HasOne
    {
        return $this->hasOne(GoodReceiveProductTax::class, 'good_receive_product_id')
            ->where('tax_type', Tax::TYPE_PPN);
    }

    public function pph(): HasOne
    {
        return $this->hasOne(GoodReceiveProductTax::class, 'good_receive_product_id')
            ->where('tax_type', Tax::TYPE_PPH);
    }


    public function goodReceiveOrderProductTaxes()
    {
        return $this->hasMany(GoodReceiveProductTax::class, 'good_receive_product_id', 'id');
    }

    public function goodReceiveProductAttachments()
    {
        return $this->hasMany(GoodReceiveProductAttachment::class, 'good_receive_product_id', 'id');
    }
}
