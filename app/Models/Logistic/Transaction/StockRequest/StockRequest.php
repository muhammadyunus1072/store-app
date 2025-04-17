<?php

namespace App\Models\Logistic\Transaction\StockRequest;


use App\Settings\SettingLogistic;
use Illuminate\Support\Facades\Log;
use App\Models\Core\Company\Company;
use App\Traits\Document\HasApproval;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\General\NumberGenerator;
use App\Traits\Logistic\HasTransactionStock;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Document\Master\ApprovalConfig;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Logistic\Transaction\StockRequest\StockRequestProduct;
use App\Models\Logistic\Transaction\TransactionStock\TransactionStock;
use App\Permissions\AccessLogistic;
use App\Permissions\PermissionHelper;
use Illuminate\Support\Facades\Crypt;

class StockRequest extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory, HasApproval, HasTransactionStock;

    protected $fillable = [
        'source_company_id',
        'source_warehouse_id',
        'destination_company_id',
        'destination_location_id',
        'destination_location_type',
        'transaction_date',
        'note',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model->number = NumberGenerator::simpleYearCode(self::class, "SP", $model->transaction_date);
            $model = $model->companyDestination->saveInfo($model, 'destination_company');
            $model = $model->companySource->saveInfo($model, 'source_company');
            $model = $model->warehouseSource->saveInfo($model, 'source_warehouse');
            $model = $model->locationDestination->saveInfo($model, 'destination_location');
        });

        self::updating(function ($model) {
            if ($model->getOriginal('destination_company_id') != $model->destination_company_id) {
                $model = $model->companyDestination->saveInfo($model, 'destination_company');
            }
            if ($model->getOriginal('source_company_id') != $model->source_company_id) {
                $model = $model->companySource->saveInfo($model, 'source_company');
            }
            if ($model->getOriginal('destination_location_id') != $model->destination_location_id) {
                $model = $model->locationDestination->saveInfo($model, 'destination_location');
            }
        });

        self::deleted(function ($model) {
            $model->transactionStockCancel();

            foreach ($model->stockRequestProducts as $item) {
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

    public function onCreated()
    {
        if (empty(SettingLogistic::get(SettingLogistic::APPROVAL_KEY_STOCK_REQUEST))) {
            $this->transactionStockProcess();
            return;
        }

        $approval = ApprovalConfig::createApprovalIfMatch(SettingLogistic::get(SettingLogistic::APPROVAL_KEY_STOCK_REQUEST), $this);
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
        $data = [
            'id' => $this->id,
            'transaction_date' => $this->transaction_date,
            'transaction_type' => TransactionStock::TYPE_TRANSFER,
            'source_company_id' => $this->source_company_id,
            'source_warehouse_id' => $this->source_warehouse_id,
            'destination_company_id' => $this->destination_company_id,
            'destination_location_id' => $this->destination_location_id,
            'destination_location_type' => $this->destination_location_type,
            'products' => [],
            'remarks_id' => $this->id,
            'remarks_type' => get_class($this)
        ];

        foreach ($this->stockRequestProducts as $stockRequestProduct) {
            if ($stockRequestProduct->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            $data['products'][] = [
                'product_id' => $stockRequestProduct->product_id,
                'quantity' => $stockRequestProduct->quantity,
                'unit_detail_id' => $stockRequestProduct->unit_detail_id,
                'remarks_id' => $stockRequestProduct->id,
                'remarks_type' => get_class($stockRequestProduct)
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
            'component' => 'logistic.transaction.stock-request.detail',
            'data' => [
                'objId' => Crypt::encrypt($this->id),
                'isShow' => true,
            ],
        ];
    }

    public function approvalRemarksInfo()
    {
        return [
            "text" => "Permintaan - " . $this->number,
            "access" => PermissionHelper::transform(AccessLogistic::STOCK_REQUEST, PermissionHelper::TYPE_READ),
            "url" => route("stock_request.show", Crypt::encrypt($this->id))
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
    public function companyDestination()
    {
        return $this->belongsTo(Company::class, 'destination_company_id', 'id');
    }

    public function companySource()
    {
        return $this->belongsTo(Company::class, 'source_company_id', 'id');
    }

    public function locationDestination()
    {
        return $this->belongsTo($this->destination_location_type, 'destination_location_id', 'id');
    }

    public function warehouseSource()
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id', 'id');
    }

    public function stockRequestProducts()
    {
        return $this->hasMany(StockRequestProduct::class, 'stock_request_id', 'id');
    }
}
