<?php

namespace App\Models\Logistic\Transaction\StockRequest;

use App\Helpers\Logistic\Stock\StockHandler;
use Carbon\Carbon;
use App\Traits\HasApproval;
use App\Helpers\General\NumberGenerator;
use App\Models\Core\Setting\Setting;
use App\Models\Document\Master\ApprovalConfig;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Warehouse\Warehouse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Logistic\Transaction\StockRequest\StockRequestProduct;
use App\Repositories\Document\Master\ApprovalConfig\ApprovalConfigRepository;
use App\Repositories\Document\Master\StatusApproval\StatusApprovalRepository;
use App\Repositories\Logistic\Transaction\StockRequest\StockRequestRepository;
use App\Repositories\Core\Setting\SettingRepository;
use App\Repositories\Document\Transaction\ApprovalRepository;
use App\Repositories\Document\Transaction\ApprovalUserRepository;
use App\Repositories\Document\Transaction\ApprovalHistoryRepository;
use App\Settings\SettingLogistic;
use Sis\TrackHistory\HasTrackHistory;

class StockRequest extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory, HasApproval;

    protected $fillable = [
        'warehouse_requester_id',
        'warehouse_requested_id',
        'request_date',
        'approved_date',
        'cancel_date',
        'note',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model->number = NumberGenerator::generate(self::class, "SR");
            $model = $model->warehouseRequester->saveInfo($model, 'warehouse_requester');
            $model = $model->warehouseRequested->saveInfo($model, 'warehouse_requested');
        });

        self::updating(function ($model) {
            if ($model->getOriginal('warehouse_requester_id') != $model->warehouse_requester_id) {
                $model = $model->warehouseRequester->saveInfo($model, 'warehouse_requester');
            }
            if ($model->getOriginal('warehouse_requested_id') != $model->warehouse_requested_id) {
                $model = $model->warehouseRequested->saveInfo($model, 'warehouse_requested');
            }
        });

        self::deleted(function ($model) {
            $model->stockRequestProducts()->delete();
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

    public function onCreated()
    {
        $setting = SettingRepository::findBy(whereClause: [['name' => SettingLogistic::NAME]]);
        $settings = json_decode($setting->setting, true);

        if (!isset($settings[SettingLogistic::APPROVAL_KEY_STOCK_REQUEST]) || empty($settings[SettingLogistic::APPROVAL_KEY_STOCK_REQUEST])) {
            $this->processStock();
        }

        $approval = ApprovalConfig::createApprovalIfMatch($settings[SettingLogistic::APPROVAL_KEY_STOCK_REQUEST], $this);
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
        foreach ($this->stockRequestProducts as $stockRequestProduct) {
            if ($stockRequestProduct->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            $data[] = [
                'id' => $stockRequestProduct->id,
                'product_id' => $stockRequestProduct->product_id,
                'product_name' => $stockRequestProduct->product_name,
                'company_requester_id' => $this->company_requester_id,
                'warehouse_requester_id' => $this->warehouse_requester_id,
                'company_requested_id' => $this->company_requested_id,
                'warehouse_requested_id' => $this->warehouse_requested_id,
                'quantity' => $stockRequestProduct->quantity,
                'unit_detail_id' => $stockRequestProduct->unit_detail_id,
                'transaction_date' => $this->request_date,
                'remarks_id' => $stockRequestProduct->id,
                'remarks_type' => get_class($stockRequestProduct)
            ];
        }

        StockHandler::transfer($data);
    }

    public function updateStock()
    {
        $transferData = [];
        $updateData = [];
        $cancelData = [];

        // Prepare Stock Cancel
        $deletedStockRequestProducts = $this->stockRequestProducts()->onlyTrashed()->get();
        foreach ($deletedStockRequestProducts as $deletedStockRequestProduct) {
            if ($deletedStockRequestProduct->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            $cancelData[] = [
                'remarks_id' => $deletedStockRequestProduct->id,
                'remarks_type' => get_class($deletedStockRequestProduct)
            ];
        }

        // Prepare Stock Add & Update
        foreach ($this->stockRequestProducts as $stockRequestProduct) {
            if ($stockRequestProduct->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            if ($stockRequestProduct->created_at == $stockRequestProduct->updated_at) {
                $transferData[] = [
                    'id' => $stockRequestProduct->id,
                    'product_id' => $stockRequestProduct->product_id,
                    'product_name' => $stockRequestProduct->product_name,
                    'company_requester_id' => $this->company_requester_id,
                    'warehouse_requester_id' => $this->warehouse_requester_id,
                    'company_requested_id' => $this->company_requested_id,
                    'warehouse_requested_id' => $this->warehouse_requested_id,
                    'quantity' => $stockRequestProduct->quantity,
                    'unit_detail_id' => $stockRequestProduct->unit_detail_id,
                    'transaction_date' => $this->request_date,
                    'remarks_id' => $stockRequestProduct->id,
                    'remarks_type' => get_class($stockRequestProduct)
                ];
            } else {
                $updateData[] = [
                    'id' => $stockRequestProduct->id,
                    'product_id' => $stockRequestProduct->product_id,
                    'product_name' => $stockRequestProduct->product_name,
                    'company_requester_id' => $this->company_requester_id,
                    'warehouse_requester_id' => $this->warehouse_requester_id,
                    'company_requested_id' => $this->company_requested_id,
                    'warehouse_requested_id' => $this->warehouse_requested_id,
                    'quantity' => $stockRequestProduct->quantity,
                    'unit_detail_id' => $stockRequestProduct->unit_detail_id,
                    'transaction_date' => $this->request_date,
                    'remarks_id' => $stockRequestProduct->id,
                    'remarks_type' => get_class($stockRequestProduct)
                ];
            }
        }

        StockHandler::cancel($cancelData);
        StockHandler::transfer($transferData);
        StockHandler::updateTransfer($updateData);
    }

    public function cancelStock()
    {
        $data = [];
        foreach ($this->stockRequestProducts as $stockRequestProduct) {
            if ($stockRequestProduct->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            $data[] = [
                'remarks_id' => $stockRequestProduct->id,
                'remarks_type' => get_class($stockRequestProduct)
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
    public function warehouseRequester()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_requester_id', 'id');
    }

    public function warehouseRequested()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_requested_id', 'id');
    }

    public function stockRequestProducts()
    {
        return $this->hasMany(StockRequestProduct::class, 'stock_request_id', 'id');
    }
}
