<?php

namespace App\Models\Purchasing\Transaction\PurchaseOrder;

use App\Settings\SettingPurchasing;
use App\Traits\Logistic\HasTransactionStock;
use App\Traits\Document\HasApproval;
use App\Helpers\General\NumberGenerator;
use App\Models\Core\Company\Company;
use App\Models\Document\Master\ApprovalConfig;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Warehouse\Warehouse;
use App\Models\Logistic\Transaction\TransactionStock\TransactionStock;
use App\Models\Purchasing\Master\Supplier\Supplier;
use App\Permissions\AccessPurchasing;
use App\Permissions\PermissionHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Crypt;
use Sis\TrackHistory\HasTrackHistory;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory, HasApproval, HasTransactionStock;

    protected $fillable = [
        'company_id',
        'supplier_id',
        'supplier_invoice_number',
        'warehouse_id',
        'transaction_date',
        'note',
        'no_spk',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model->number = NumberGenerator::simpleYearCode(self::class, "TR", $model->transaction_date);

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
            $model->transactionStockCancel();

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
        if (empty(SettingPurchasing::get(SettingPurchasing::APPROVAL_KEY_PURCHASE_ORDER))) {
            $this->transactionStockProcess();
            return;
        }

        $approval = ApprovalConfig::createApprovalIfMatch(SettingPurchasing::get(SettingPurchasing::APPROVAL_KEY_PURCHASE_ORDER), $this);
        if (!$approval) {
            $this->transactionStockProcess();
        }
    }

    public function onUpdated()
    {
        if (!$this->isHasApproval() || $this->isApprovalDone()) {
            $this->transactionStockProcess();
        }
    }

    /*
    | TRANSACTION STOCK
    */
    public function transactionStockData(): array
    {
        $isValueAddTaxPpn = SettingPurchasing::get(SettingPurchasing::PURCHASE_ORDER_ADD_STOCK_VALUE_INCLUDE_TAX_PPN);

        $data = [
            'transaction_date' => $this->transaction_date,
            'transaction_type' => TransactionStock::TYPE_ADD,
            'source_company_id' => $this->company_id,
            'source_warehouse_id' => $this->warehouse_id,
            'destination_company_id' => $this->warehouse_id,
            'destination_location_id' => $this->warehouse_id,
            'destination_location_type' => Warehouse::class,
            'products' => [],
            'remarks_id' => $this->id,
            'remarks_type' => get_class($this)
        ];

        foreach ($this->purchaseOrderProducts as $item) {
            if ($item->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            $data['products'][] = [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_detail_id' => $item->unit_detail_id,
                'price' => $item->price + ($isValueAddTaxPpn ? (!empty($item->ppn) ? $item->price * $item->ppn->tax_value / 100.0 : 0) : 0),
                'code' => $item->code,
                'batch' => $item->batch,
                'expired_date' => $item->expired_date,
                'remarks_id' => $item->id,
                'remarks_type' => get_class($item)
            ];
        }

        return $data;
    }

    /*
    | APPROVAL
    */
    public function approvalRemarksView()
    {
        return [
            'component' => 'purchasing.transaction.purchase-order.detail',
            'data' => [
                'objId' => Crypt::encrypt($this->id),
                'isShow' => true,
            ],
        ];
    }

    public function approvalRemarksInfo()
    {
        return [
            "text" => "Pembelian - " . $this->number,
            "access" => PermissionHelper::transform(AccessPurchasing::PURCHASE_ORDER, PermissionHelper::TYPE_READ),
            "url" => route("purchase_order.show", Crypt::encrypt($this->id))
        ];
    }

    public function onApprovalDone()
    {
        $this->transactionStockProcess();
    }

    public function onApprovalRevertDone()
    {
        $this->transactionStockCancel();
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
