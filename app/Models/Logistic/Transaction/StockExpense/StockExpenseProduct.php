<?php

namespace App\Models\Logistic\Transaction\StockExpense;

use App\Permissions\AccessLogistic;
use App\Permissions\PermissionHelper;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\General\NumberGenerator;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Unit\UnitDetail;
use App\Traits\Logistic\HasProductDetailHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Logistic\Transaction\StockExpense\StockExpense;

class StockExpenseProduct extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory, HasProductDetailHistory;

    protected $fillable = [
        'stock_expense_id',
        'product_id',
        'unit_detail_id',
        'quantity',
    ];

    protected $guarded = ['id'];

    CONST TRANSLATE_NAME = 'Pengeluaran';

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model = $model->product->saveInfo($model);
            $model = $model->unitDetail->saveInfo($model);
        });

        self::updating(function ($model) {
            if ($model->product_id != $model->getOriginal('product_id')) {
                $model = $model->product->saveInfo($model);
            }
            if ($model->unit_detail_id != $model->getOriginal('unit_detail_id')) {
                $model = $model->unitDetail->saveInfo($model);
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

    /*
    | PRODUCT DETAIL HISTORY
    */

    public function masterTable()
    {
        return $this->stockExpense();
    }
    
    public function remarksTableInfo(): array
    {
        return [
            "translated_name" => self::TRANSLATE_NAME,
            "access_name" => PermissionHelper::transform(AccessLogistic::STOCK_EXPENSE, PermissionHelper::TYPE_READ),
            "route_name" => "stock_expense.edit"
        ];
    }

    /*
    | RELATIONSHIP
    */

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function unitDetail()
    {
        return $this->belongsTo(UnitDetail::class, 'unit_detail_id', 'id');
    }

    public function stockExpense()
    {
        return $this->belongsTo(StockExpense::class, 'stock_expense_id', 'id');
    }
}
