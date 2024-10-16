<?php

namespace App\Models\Logistic\Transaction\StockExpense;

use App\Helpers\Logistic\StockHelper;
use App\Helpers\NumberGenerator;
use App\Models\Core\Company\Company;
use App\Models\Document\Master\ApprovalConfig;
use App\Models\Logistic\Master\Product\Product;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Logistic\Master\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Logistic\Transaction\StockExpense\StockExpenseProduct;
use App\Repositories\Core\Setting\SettingRepository;
use App\Settings\SettingLogistic;
use App\Traits\HasApproval;

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
        foreach ($this->stockExpenseProducts as $stockExpenseProduct) {
            if ($stockExpenseProduct->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            StockHelper::substractStock(
                productId: $stockExpenseProduct->product_id,
                companyId: $this->company_id,
                warehouseId: $this->warehouse_id,
                quantity: $stockExpenseProduct->quantity,
                unitDetailId: $stockExpenseProduct->unit_detail_id,
                remarksId: $stockExpenseProduct->id,
                remarksType: get_class($stockExpenseProduct),
                remarksNote: '-',
            );
        }
    }

    public function updateStock()
    {
        foreach ($this->stockExpenseProducts as $stockExpenseProduct) {
            if ($stockExpenseProduct->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            if ($stockExpenseProduct->created_at == $stockExpenseProduct->updated_at) {
                // Create
                StockHelper::substractStock(
                    productId: $stockExpenseProduct->product_id,
                    companyId: $this->company_id,
                    warehouseId: $this->warehouse_id,
                    quantity: $stockExpenseProduct->quantity,
                    unitDetailId: $stockExpenseProduct->unit_detail_id,
                    remarksId: $stockExpenseProduct->id,
                    remarksType: get_class($stockExpenseProduct),
                    remarksNote: '-',
                );
            } else {
                // Update
                StockHelper::updateStockHistory(
                    remarksId: $stockExpenseProduct->id,
                    remarksType: get_class($stockExpenseProduct),
                    remarksNote: '-',
                    transactionSign: -1,

                    unitDetailId: $stockExpenseProduct->unit_detail_id,
                    quantity: $stockExpenseProduct->quantity,
                    transactionDate: $this->expense_date,
                    newRemarksNote: '-',
                );
            }
        }
    }

    public function cancelStock()
    {
        foreach ($this->stockExpenseProducts as $stockExpenseProduct) {
            if ($stockExpenseProduct->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            StockHelper::cancelStock(
                remarksId: $stockExpenseProduct->id,
                remarksType: get_class($stockExpenseProduct),
            );
        }
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
