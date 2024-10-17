<?php

namespace App\Models\Logistic\Transaction\GoodReceive;

use App\Settings\SettingLogistic;
use App\Traits\HasApproval;
use App\Helpers\NumberGenerator;
use App\Helpers\Logistic\Stock\StockHandler;
use App\Models\Core\Company\Company;
use App\Models\Document\Master\ApprovalConfig;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Warehouse\Warehouse;
use App\Models\Logistic\Transaction\GoodReceive\GoodReceiveProduct;
use App\Models\Purchasing\Master\Supplier\Supplier;
use App\Repositories\Core\Setting\SettingRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Sis\TrackHistory\HasTrackHistory;

class GoodReceive extends Model
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
            foreach ($model->goodReceiveProducts as $item) {
                $item->delete();
            }
        });
    }

    public function isDeletable()
    {
        foreach ($this->goodReceiveProducts as $item) {
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
        $setting = SettingRepository::findBy(whereClause: [['name' => SettingLogistic::NAME]]);
        $settings = json_decode($setting->setting, true);

        if (!isset($settings[SettingLogistic::APPROVAL_KEY_GOOD_RECEIVE]) || empty($settings[SettingLogistic::APPROVAL_KEY_GOOD_RECEIVE])) {
            $this->processStock();
        }

        $approval = ApprovalConfig::createApprovalIfMatch($settings[SettingLogistic::APPROVAL_KEY_GOOD_RECEIVE], $this);
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
        $data = [];
        foreach ($this->goodReceiveProducts as $grProduct) {
            if ($grProduct->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            $data[] = [
                'id' => $grProduct->id,
                'product_id' => $grProduct->product_id,
                'product_name' => $grProduct->product_name,
                'company_id' => $this->company_id,
                'warehouse_id' => $this->warehouse_id,
                'quantity' => $grProduct->quantity,
                'unit_detail_id' => $grProduct->unit_detail_id,
                'transaction_date' => $this->receive_date,
                'price' => $grProduct->price,
                'tax_value' => !empty($grProduct->ppn) ? $grProduct->ppn->tax_value : 0,
                'code' => $grProduct->code,
                'batch' => $grProduct->batch,
                'expired_date' => $grProduct->expired_date,
                'remarks_id' => $grProduct->id,
                'remarks_type' => get_class($grProduct)
            ];
        }

        StockHandler::add($data);
    }

    public function updateStock()
    {
        $addData = [];
        $updateData = [];
        $cancelData = [];

        // Prepare Stock Cancel
        $deletedGrProducts = $this->goodReceiveProducts()->onlyTrashed()->get();
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
        foreach ($this->goodReceiveProducts as $grProduct) {
            if ($grProduct->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            if ($grProduct->created_at == $grProduct->updated_at) {
                $addData[] = [
                    'id' => $grProduct->id,
                    'product_id' => $grProduct->product_id,
                    'product_name' => $grProduct->product_name,
                    'company_id' => $this->company_id,
                    'warehouse_id' => $this->warehouse_id,
                    'quantity' => $grProduct->quantity,
                    'unit_detail_id' => $grProduct->unit_detail_id,
                    'transaction_date' => $this->receive_date,
                    'price' => $grProduct->price,
                    'tax_value' => !empty($grProduct->ppn) ? $grProduct->ppn->tax_value : 0,
                    'code' => $grProduct->code,
                    'batch' => $grProduct->batch,
                    'expired_date' => $grProduct->expired_date,
                    'remarks_id' => $grProduct->id,
                    'remarks_type' => get_class($grProduct)
                ];
            } else {
                $updateData[] = [
                    'id' => $grProduct->id,
                    'product_id' => $grProduct->product_id,
                    'product_name' => $grProduct->product_name,
                    'company_id' => $this->company_id,
                    'warehouse_id' => $this->warehouse_id,
                    'quantity' => $grProduct->quantity,
                    'unit_detail_id' => $grProduct->unit_detail_id,
                    'transaction_date' => $this->receive_date,
                    'price' => $grProduct->price,
                    'tax_value' => !empty($grProduct->ppn) ? $grProduct->ppn->tax_value : 0,
                    'code' => $grProduct->code,
                    'batch' => $grProduct->batch,
                    'expired_date' => $grProduct->expired_date,
                    'remarks_id' => $grProduct->id,
                    'remarks_type' => get_class($grProduct)
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
        foreach ($this->goodReceiveProducts as $grProduct) {
            if ($grProduct->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            $data[] = [
                'remarks_id' => $grProduct->id,
                'remarks_type' => get_class($grProduct)
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

    public function goodReceiveProducts()
    {
        return $this->hasMany(GoodReceiveProduct::class, 'good_receive_id', 'id');
    }
}
