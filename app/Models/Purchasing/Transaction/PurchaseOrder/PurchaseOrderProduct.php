<?php

namespace App\Models\Purchasing\Transaction\PurchaseOrder;

use App\Helpers\Logistic\Stock\StockHandler;
use App\Models\Finance\Master\Tax;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Unit\UnitDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Sis\TrackHistory\HasTrackHistory;

class PurchaseOrderProduct extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'purchase_order_id',
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
            foreach ($model->purchaseOrderProductTaxes as $item) {
                $item->delete();
            }
            foreach ($model->purchaseOrderProductAttachments as $item) {
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

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function unitDetail()
    {
        return $this->belongsTo(UnitDetail::class, 'unit_detail_id', 'id');
    }

    public function ppn(): HasOne
    {
        return $this->hasOne(PurchaseOrderProductTax::class, 'purchase_order_product_id')
            ->where('tax_type', Tax::TYPE_PPN);
    }

    public function pph(): HasOne
    {
        return $this->hasOne(PurchaseOrderProductTax::class, 'purchase_order_product_id')
            ->where('tax_type', Tax::TYPE_PPH);
    }

    public function purchaseOrderProductTaxes()
    {
        return $this->hasMany(PurchaseOrderProductTax::class, 'purchase_order_product_id', 'id');
    }

    public function purchaseOrderProductAttachments()
    {
        return $this->hasMany(PurchaseOrderProductAttachment::class, 'purchase_order_product_id', 'id');
    }
}
