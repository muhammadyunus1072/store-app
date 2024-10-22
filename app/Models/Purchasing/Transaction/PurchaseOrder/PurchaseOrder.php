<?php

namespace App\Models\Purchasing\Transaction\PurchaseOrder;

use App\Settings\SettingLogistic;
use App\Traits\Document\HasApproval;
use App\Helpers\General\NumberGenerator;
use App\Helpers\Logistic\Stock\StockHandler;
use App\Models\Core\Company\Company;
use App\Models\Document\Master\ApprovalConfig;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Warehouse\Warehouse;
use App\Models\Purchasing\Master\Supplier\Supplier;
use App\Settings\SettingPurchasing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Sis\TrackHistory\HasTrackHistory;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory, HasApproval;

    protected $fillable = [
        'purchase_order_id',
        'company_id',
        'supplier_id',
        'supplier_invoice_number',
        'warehouse_id',
        'receive_date',
        'note',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model->number = NumberGenerator::generate(self::class, "GR");

            $model = $model->supplier->saveInfo($model);
            $model = $model->company->saveInfo($model);
            $model = $model->warehouse->saveInfo($model);
        });

        self::updating(function ($model) {
            if ($model->supplier_id != $model->getOriginal('supplier_id')) {
                $model = $model->supplier->saveInfo($model);
            }
            if ($model->company_id != $model->getOriginal('company_id')) {
                $model = $model->company->saveInfo($model);
            }
            if ($model->warehouse_id != $model->getOriginal('warehouse_id')) {
                $model = $model->warehouse->saveInfo($model);
            }
        });

        self::deleted(function ($model) {
            foreach ($model->purchaseOrderProducts as $item) {
                $item->delete();
            }
        });
    }

    public function isDeletable()
    {
        foreach ($this->purchaseOrderProducts as $item) {
            if (!$item->isDeletable()) {
                return false;
            }
        }

        return true;
    }

    public function isEditable()
    {
        return true;
    }

    public function onCreated()
    {
        if (!empty(SettingPurchasing::get(SettingPurchasing::APPROVAL_KEY_PURCHASE_ORDER))) {
            $this->processStock();
        }

        $approval = ApprovalConfig::createApprovalIfMatch(SettingPurchasing::get(SettingPurchasing::APPROVAL_KEY_PURCHASE_ORDER), $this);
        if (!$approval) {
            $this->processStock();
        }
    }

    public function onUpdated()
    {
        if (!$this->isHasApproval() || $this->isApprovalDone()) {
            $this->updateStock();
        }
    }

    /*
    | STOCK PROCESS
    */
    public function processStock()
    {
        $isValueAddTaxPpn = SettingPurchasing::get(SettingPurchasing::PURCHASE_ORDER_ADD_STOCK_VALUE_INCLUDE_TAX_PPN);

        $data = [];
        foreach ($this->purchaseOrderProducts as $item) {
            if ($item->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            $data[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'company_id' => $this->company_id,
                'warehouse_id' => $this->warehouse_id,
                'quantity' => $item->quantity,
                'unit_detail_id' => $item->unit_detail_id,
                'transaction_date' => $this->receive_date,
                'price' => $item->price + ($isValueAddTaxPpn ? (!empty($item->ppn) ? $item->price * $item->ppn->tax_value / 100.0 : 0) : 0),
                'code' => $item->code,
                'batch' => $item->batch,
                'expired_date' => $item->expired_date,
                'remarks_id' => $item->id,
                'remarks_type' => get_class($item)
            ];
        }

        StockHandler::add($data);
    }

    public function updateStock()
    {
        $isValueAddTaxPpn = SettingPurchasing::get(SettingPurchasing::PURCHASE_ORDER_ADD_STOCK_VALUE_INCLUDE_TAX_PPN);

        $addData = [];
        $updateData = [];
        $cancelData = [];

        // Prepare Stock Cancel
        $deletedGrProducts = $this->purchaseOrderProducts()->onlyTrashed()->get();
        foreach ($deletedGrProducts as $deletedGrProduct) {
            if ($deletedGrProduct->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            $cancelData[] = [
                'remarks_id' => $deletedGrProduct->id,
                'remarks_type' => get_class($deletedGrProduct)
            ];
        }

        // Prepare Stock Add & Update
        foreach ($this->purchaseOrderProducts as $item) {
            if ($item->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            if ($item->created_at == $item->updated_at) {
                $addData[] = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'company_id' => $this->company_id,
                    'warehouse_id' => $this->warehouse_id,
                    'quantity' => $item->quantity,
                    'unit_detail_id' => $item->unit_detail_id,
                    'transaction_date' => $this->receive_date,
                    'price' => $item->price + ($isValueAddTaxPpn ? (!empty($item->ppn) ? $item->price * $item->ppn->tax_value / 100.0 : 0) : 0),
                    'code' => $item->code,
                    'batch' => $item->batch,
                    'expired_date' => $item->expired_date,
                    'remarks_id' => $item->id,
                    'remarks_type' => get_class($item)
                ];
            } else {
                $updateData[] = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'company_id' => $this->company_id,
                    'warehouse_id' => $this->warehouse_id,
                    'quantity' => $item->quantity,
                    'unit_detail_id' => $item->unit_detail_id,
                    'transaction_date' => $this->receive_date,
                    'price' => $item->price + ($isValueAddTaxPpn ? (!empty($item->ppn) ? $item->price * $item->ppn->tax_value / 100.0 : 0) : 0),
                    'code' => $item->code,
                    'batch' => $item->batch,
                    'expired_date' => $item->expired_date,
                    'remarks_id' => $item->id,
                    'remarks_type' => get_class($item)
                ];
            }
        }

        StockHandler::cancel($cancelData);
        StockHandler::add($addData);
        StockHandler::updateAdd($updateData);
    }

    public function cancelStock()
    {
        $data = [];
        foreach ($this->purchaseOrderProducts as $item) {
            if ($item->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            $data[] = [
                'remarks_id' => $item->id,
                'remarks_type' => get_class($item)
            ];
        }

        StockHandler::cancel($data);
    }

    /*
    | APPROVAL
    */
    public function approvalViewShow() {}
    public function onApprovalDone()
    {
        $this->processStock();
    }
    public function onApprovalRevertDone()
    {
        $this->cancelStock();
    }
    public function onApprovalCanceled() {}
    public function onApprovalRevertCancel() {}

    /*
    | RELATIONSHIP
    */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    public function purchaseOrderProducts()
    {
        return $this->hasMany(PurchaseOrderProduct::class, 'purchase_order_id', 'id');
    }
}
