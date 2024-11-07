<?php

namespace App\Models\Logistic\Transaction\StockExpense;

use App\Helpers\General\NumberGenerator;
use App\Models\Core\Company\Company;
use App\Models\Document\Master\ApprovalConfig;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Transaction\StockExpense\StockExpenseProduct;
use App\Models\Logistic\Master\Warehouse\Warehouse;
use App\Models\Logistic\Transaction\TransactionStock\TransactionStock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Settings\SettingLogistic;
use App\Traits\Document\HasApproval;
use App\Traits\Logistic\HasTransactionStock;
use Sis\TrackHistory\HasTrackHistory;

class StockExpense extends Model
{
    use HasFactory, SoftDeletes, HasApproval, HasTrackHistory, HasTransactionStock;

    protected $fillable = [
        'warehouse_id',
        'company_id',
        'transaction_date',
        'note',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model->number = NumberGenerator::simpleYearCode(self::class, "SP", $model->transaction_date);

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
            $model->transactionStockCancel();

            foreach ($model->stockExpenseProducts as $item) {
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
        if (SettingLogistic::get(SettingLogistic::APPROVAL_KEY_STOCK_EXPENSE)) {
            $this->transactionStockProcess();
        }

        $approval = ApprovalConfig::createApprovalIfMatch(SettingLogistic::get(SettingLogistic::APPROVAL_KEY_STOCK_EXPENSE), $this);
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
            'transaction_type' => TransactionStock::TYPE_SUBSTRACT,
            'source_company_id' => $this->company_id,
            'source_warehouse_id' => $this->warehouse_id,
            'destination_company_id' => null,
            'destination_warehouse_id' => null,
            'products' => [],
            'remarks_id' => $this->id,
            'remarks_type' => get_class($this)
        ];

        foreach ($this->stockExpenseProducts as $stockExpenseProduct) {
            if ($stockExpenseProduct->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            $data['products'][] = [
                'product_id' => $stockExpenseProduct->product_id,
                'quantity' => $stockExpenseProduct->quantity,
                'unit_detail_id' => $stockExpenseProduct->unit_detail_id,
                'remarks_id' => $stockExpenseProduct->id,
                'remarks_type' => get_class($stockExpenseProduct)
            ];
        }

        return $data;
    }

    /*
    | APPROVAL
    */
    public function approvalViewShow() {}
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

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    public function stockExpenseProducts()
    {
        return $this->hasMany(StockExpenseProduct::class, 'stock_expense_id', 'id');
    }
}
