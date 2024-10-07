<?php

namespace App\Models\Logistic\Transaction\StockExpense;

use App\Helpers\NumberGenerator;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Unit\UnitDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Logistic\Transaction\StockExpense\StockExpense;

class StockExpenseProduct extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'stock_expense_id',
        'product_id',
        'unit_detail_id',
        'quantity',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model = $model->product->saveInfo($model);
            $model = $model->unitDetail->saveInfo($model);
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

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function unitDetailChoices()
    {
        return $this->hasMany(UnitDetail::class, 'unit_id', 'product_unit_id');
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
