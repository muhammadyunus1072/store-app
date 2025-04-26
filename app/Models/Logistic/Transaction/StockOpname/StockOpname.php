<?php

namespace App\Models\Logistic\Transaction\StockOpname;

use App\Settings\SettingLogistic;
use App\Traits\Document\HasApproval;
use App\Traits\Logistic\HasTransactionStock;
use App\Helpers\General\NumberGenerator;
use App\Models\Core\Company\Company;
use App\Models\Document\Master\ApprovalConfig;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Warehouse\Warehouse;
use App\Models\Logistic\Transaction\StockOpname\StockOpnameDetail;
use App\Models\Logistic\Transaction\TransactionStock\TransactionStock;
use App\Permissions\AccessLogistic;
use App\Permissions\PermissionHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Sis\TrackHistory\HasTrackHistory;

class StockOpname extends Model
{
    use HasFactory, SoftDeletes, HasApproval, HasTrackHistory, HasTransactionStock;

    protected $fillable = [
        'location_id',
        'location_type',
        'company_id',
        'stock_opname_date',
        'status',
        'note',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model->number = NumberGenerator::simpleYearCode(self::class, "SO", $model->transaction_date);

            $model = $model->location->saveInfo($model, 'location');
            $model = $model->company->saveInfo($model);
        });

        self::updating(function ($model) {
            if ($model->company_id != $model->getOriginal('company_id')) {
                $model = $model->company->saveInfo($model);
            }
            if ($model->location_id != $model->getOriginal('location_id')) {
                $model = $model->location->saveInfo($model, 'location');
            }
        });

        self::deleted(function ($model) {
            $model->transactionStockCancel();

            foreach ($model->stockOpnameDetails as $item) {
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
        logger(SettingLogistic::get(SettingLogistic::APPROVAL_KEY_STOCK_OPNAME));
        if (empty(SettingLogistic::get(SettingLogistic::APPROVAL_KEY_STOCK_OPNAME))) {
            $this->transactionStockProcess();
            return;
        }

        $approval = ApprovalConfig::createApprovalIfMatch(SettingLogistic::get(SettingLogistic::APPROVAL_KEY_STOCK_OPNAME), $this);
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
            'transaction_date' => $this->transaction_date,
            'transaction_type' => TransactionStock::TYPE_OPNAME,
            'transaction_date' => $this->stock_opname_date,
            'source_company_id' => $this->company_id,
            'source_warehouse_id' => $this->location_id,
            'destination_company_id' => $this->company_id,
            'destination_location_id' => $this->location_id,
            'destination_location_type' => $this->location_type,
            'products' => [],
            'remarks_id' => $this->id,
            'remarks_type' => get_class($this)
        ];

        foreach ($this->stockOpnameDetails as $stockOpnameDetail) {
            // if ($stockOpnameDetail->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
            //     continue;
            // }

            $data['products'][] = [
                'product_id' => $stockOpnameDetail->product_id,
                'quantity' => $stockOpnameDetail->difference,
                'unit_detail_id' => $stockOpnameDetail->main_unit_detail_id,
                'remarks_id' => $stockOpnameDetail->id,
                'remarks_type' => get_class($stockOpnameDetail)
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
            'component' => 'logistic.transaction.stock-opname.detail',
            'data' => [
                'objId' => Crypt::encrypt($this->id),
                'isShow' => true,
            ],
        ];
    }

    public function approvalRemarksInfo()
    {
        return [
            "text" => "Pengeluaran - " . $this->number,
            "access" => PermissionHelper::transform(AccessLogistic::STOCK_OPNAME, PermissionHelper::TYPE_READ),
            "url" => route("stock_opname.show", Crypt::encrypt($this->id))
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

    public function location()
    {
        return $this->belongsTo($this->location_type, 'location_id', 'id');
    }

    public function stockOpnameDetails()
    {
        return $this->hasMany(StockOpnameDetail::class, 'stock_opname_id', 'id');
    }
}
