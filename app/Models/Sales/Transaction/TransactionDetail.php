<?php

namespace App\Models\Sales\Transaction;

use App\Models\Finance\Master\Tax;
use App\Permissions\AccessLogistic;
use App\Permissions\PermissionHelper;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Logistic\Stock\StockHandler;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Product\ProductUnit;
use App\Models\Logistic\Master\Unit\UnitDetail;
use App\Traits\Logistic\HasProductDetailHistory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrder;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProductTax;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProductAttachment;
use App\Permissions\AccessPurchasing;
use Illuminate\Support\Facades\Crypt;

class TransactionDetail extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory, HasProductDetailHistory;

    protected $fillable = [
        'transaction_id',

        // Product Information
        'product_id',

        // Product Unit Information
        'product_unit_id',

        // Unit Detail Information
        'quantity',

        // Main Unit Detail Information
        'converted_quantity',
        'converted_price',
        "main_unit_detail_id",
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model->saveConvertResult();

            $model = $model->product->saveInfo($model);
            $model = $model->productUnit->saveInfo($model);
            $model = $model->mainUnitDetail->saveInfo($model, 'main_unit_detail');
        });

        self::updating(function ($model) {
            if ($model->product_id != $model->getOriginal('product_id')) {
                $model = $model->product->saveInfo($model);
            }

            if ($model->product_unit_id != $model->getOriginal('product_unit_id')) {
                $model = $model->productUnit->saveInfo($model);
            }

            if (
                $model->product_unit_unit_detail_id != $model->getOriginal('product_unit_unit_detail_id')
                || $model->quantity != $model->getOriginal('quantity')
                || $model->price != $model->getOriginal('price')
            ) {
                $model->saveConvertResult();
                $model = $model->mainUnitDetail->saveInfo($model, 'main_unit_detail');
            }
        });
    }

    public function getText()
    {
        return "{$this->product_name} / {$this->product->kode_simrs} / {$this->product->kode_sakti}";
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
        $convertResult = StockHandler::convertProductUnitPrice($this->quantity, $this->price, $this->product_unit_id);

        $this->converted_quantity = $convertResult['quantity'];
        $this->converted_price = $convertResult['price'];
        $this->main_unit_detail_id = $convertResult['unit_detail_id'];
    }

    /*
    | HAS PRODUCT DETAIL HISTORY
    */
    public function productDetailHistoryRemarksInfo(): array
    {
        return [
            "text" => "Pembelian - " . $this->purchaseOrder->number,
            "access" => PermissionHelper::transform(AccessPurchasing::PURCHASE_ORDER, PermissionHelper::TYPE_READ),
            "url" => route("purchase_order.show", Crypt::encrypt($this->purchase_order_id))
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
        return $this->belongsTo(Product::class, 'product_id', 'id')->withTrashed();
    }

    public function productUnit()
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id', 'id');
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
