<?php

namespace App\Models\Logistic\Transaction\StockExpense;

use App\Helpers\NumberGenerator;
use App\Models\Core\Company\Company;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Logistic\Master\Warehouse\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Logistic\Transaction\StockExpense\StockExpenseProduct;

class StockExpense extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

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
