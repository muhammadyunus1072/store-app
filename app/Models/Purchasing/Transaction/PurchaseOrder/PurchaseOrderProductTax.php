<?php

namespace App\Models\Purchasing\Transaction\PurchaseOrder;

use App\Models\Finance\Master\Tax;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Sis\TrackHistory\HasTrackHistory;

class PurchaseOrderProductTax extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'purchase_order_product_id',
        'tax_id',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model = $model->tax->saveInfo($model);
        });

        self::updating(function ($model) {
            if ($model->tax_id != $model->getOriginal('tax_id')) {
                $model = $model->tax->saveInfo($model);
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

    public function purchaseOrderProduct()
    {
        return $this->belongsTo(PurchaseOrderProduct::class, 'purchase_order_product_id', 'id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id', 'id');
    }
}
