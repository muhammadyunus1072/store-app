<?php

namespace App\Models\Purchasing\Transaction\PurchaseOrder;

use App\Models\Finance\Master\Tax;
use App\Permissions\AccessLogistic;
use App\Permissions\PermissionHelper;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Logistic\Stock\StockHandler;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Unit\UnitDetail;
use App\Traits\Logistic\HasProductDetailHistory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrder;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProductTax;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProductAttachment;
use App\Permissions\AccessPurchasing;

class PurchaseOrderProduct extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory, HasProductDetailHistory;

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

    const TRANSLATE_NAME = 'Pembelian';

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model->saveConvertResult();

            $model = $model->product->saveInfo($model);
            $model = $model->unitDetail->saveInfo($model);
            $model = $model->mainUnitDetail->saveInfo($model, 'main_unit_detail');
        });

        self::updating(function ($model) {
            if ($model->product_id != $model->getOriginal('product_id')) {
                $model = $model->product->saveInfo($model);
            }
            
            if ($model->unit_detail_id != $model->getOriginal('unit_detail_id')) {
                $model = $model->unitDetail->saveInfo($model);
            }

            if (
                $model->unit_detail_id != $model->getOriginal('unit_detail_id')
                || $model->quantity != $model->getOriginal('quantity')
                || $model->price != $model->getOriginal('price')
            ) {
                $model->saveConvertResult();
                $model = $model->mainUnitDetail->saveInfo($model, 'main_unit_detail');
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

    public function saveConvertResult()
    {
        $convertResult = StockHandler::convertUnitPrice($this->quantity, $this->price, $this->unit_detail_id);

        $this->converted_quantity = $convertResult['quantity'];
        $this->converted_price = $convertResult['price'];
        $this->main_unit_detail_id = $convertResult['unit_detail_id'];
    }

    /*
    | PRODUCT DETAIL HISTORY
    */

    public function masterTable()
    {
        return $this->purchaseOrder();
    }

    public function translatedName(): string
    {
        return self::TRANSLATE_NAME;
    }

    public function remarksTableInfo(): array
    {
        return [
            "translated_name" => self::TRANSLATE_NAME,
            "access_name" => PermissionHelper::transform(AccessPurchasing::PURCHASE_ORDER, PermissionHelper::TYPE_READ),
            "route_name" => "purchase_order.edit"
        ];
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

    public function mainUnitDetail()
    {
        return $this->belongsTo(UnitDetail::class, 'main_unit_detail_id', 'id');
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
