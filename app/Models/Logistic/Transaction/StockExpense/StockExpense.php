<?php

namespace App\Models\Logistic\Transaction\StockExpense;

use App\Helpers\Logistic\Stock\StockHandler;
use App\Helpers\General\NumberGenerator;
use App\Models\Core\Company\Company;
use App\Models\Document\Master\ApprovalConfig;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Transaction\StockExpense\StockExpenseProduct;
use App\Models\Logistic\Master\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Repositories\Core\Setting\SettingRepository;
use App\Settings\SettingLogistic;
use App\Traits\HasApproval;
use Sis\TrackHistory\HasTrackHistory;

class StockExpense extends Model
{
    use HasFactory, SoftDeletes, HasApproval, HasTrackHistory;

    protected $fillable = [
        'warehouse_id',
        'company_id',
        'expense_date',
        'approved_date',
        'cancel_date',
        'note',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model->number = NumberGenerator::generate(self::class, "SE");

            $model = $model->warehouse->saveInfo($model);
            $model = $model->company->saveInfo($model);
        });

        self::updating(function ($model) {
            if ($model->company_id != $model->getOriginal('company_id')) {
                $model = $model->company->saveInfo($model);
            }
            if ($model->warehouse_id != $model->getOriginal('warehouse_id')) {
                $model = $model->warehouse->saveInfo($model);
            }
        });

        self::deleted(function ($model) {
            $model->stockExpenseProducts()->delete();
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

        if (!isset($settings[SettingLogistic::APPROVAL_KEY_STOCK_EXPENSE]) || empty($settings[SettingLogistic::APPROVAL_KEY_STOCK_EXPENSE])) {
            $this->processStock();
        }

        $approval = ApprovalConfig::createApprovalIfMatch($settings[SettingLogistic::APPROVAL_KEY_STOCK_EXPENSE], $this);
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
        foreach ($this->stockExpenseProducts as $stockExpenseProduct) {
            if ($stockExpenseProduct->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            $data[] = [
                'id' => $stockExpenseProduct->id,
                'product_id' => $stockExpenseProduct->product_id,
                'product_name' => $stockExpenseProduct->product_name,
                'company_id' => $this->company_id,
                'warehouse_id' => $this->warehouse_id,
                'quantity' => $stockExpenseProduct->quantity,
                'unit_detail_id' => $stockExpenseProduct->unit_detail_id,
                'transaction_date' => $this->expense_date,
                'remarks_id' => $stockExpenseProduct->id,
                'remarks_type' => get_class($stockExpenseProduct)
            ];
        }

        StockHandler::substract($data);
    }

    public function updateStock()
    {
        $substractData = [];
        $updateData = [];
        $cancelData = [];

        // Prepare Stock Cancel
        $deletedStockExpenseProducts = $this->stockExpenseProducts()->onlyTrashed()->get();
        foreach ($deletedStockExpenseProducts as $deletedStockExpenseProduct) {
            if ($deletedStockExpenseProduct->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            $cancelData[] = [
                'remarks_id' => $deletedStockExpenseProduct->id,
                'remarks_type' => get_class($deletedStockExpenseProduct)
            ];
        }

        // Prepare Stock Add & Update
        foreach ($this->stockExpenseProducts as $stockExpenseProduct) {
            if ($stockExpenseProduct->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            if ($stockExpenseProduct->created_at == $stockExpenseProduct->updated_at) {
                $substractData[] = [
                    'id' => $stockExpenseProduct->id,
                    'product_id' => $stockExpenseProduct->product_id,
                    'product_name' => $stockExpenseProduct->product_name,
                    'company_id' => $this->company_id,
                    'warehouse_id' => $this->warehouse_id,
                    'quantity' => $stockExpenseProduct->quantity,
                    'unit_detail_id' => $stockExpenseProduct->unit_detail_id,
                    'transaction_date' => $this->expense_date,
                    'remarks_id' => $stockExpenseProduct->id,
                    'remarks_type' => get_class($stockExpenseProduct)
                ];
            } else {
                $updateData[] = [
                    'id' => $stockExpenseProduct->id,
                    'product_id' => $stockExpenseProduct->product_id,
                    'product_name' => $stockExpenseProduct->product_name,
                    'company_id' => $this->company_id,
                    'warehouse_id' => $this->warehouse_id,
                    'quantity' => $stockExpenseProduct->quantity,
                    'unit_detail_id' => $stockExpenseProduct->unit_detail_id,
                    'transaction_date' => $this->expense_date,
                    'remarks_id' => $stockExpenseProduct->id,
                    'remarks_type' => get_class($stockExpenseProduct)
                ];
            }
        }

        StockHandler::cancel($cancelData);
        StockHandler::substract($substractData);
        StockHandler::updateSubstract($updateData);
    }

    public function cancelStock()
    {
        $data = [];
        foreach ($this->stockExpenseProducts as $stockExpenseProduct) {
            if ($stockExpenseProduct->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            $data[] = [
                'remarks_id' => $stockExpenseProduct->id,
                'remarks_type' => get_class($stockExpenseProduct)
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

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    public function stockExpenseProducts()
    {
        return $this->hasMany(StockExpenseProduct::class, 'stock_expense_id', 'id');
    }
}
