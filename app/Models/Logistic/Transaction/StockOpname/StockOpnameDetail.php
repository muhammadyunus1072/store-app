<?php

namespace App\Models\Logistic\Transaction\StockOpname;

use App\Permissions\AccessLogistic;
use App\Permissions\PermissionHelper;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Logistic\Stock\StockHandler;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Unit\UnitDetail;
use App\Traits\Logistic\HasProductDetailHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Logistic\Transaction\StockOpname\StockOpname;
use Illuminate\Support\Facades\Crypt;

class StockOpnameDetail extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory, HasProductDetailHistory;

    protected $fillable = [
        'stock_opname_id',
        'system_stock',
        'difference',
        'real_stock',
        'real_unit_detail_id',
        'product_id',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model->saveConvertResult();

            $model = $model->product->saveInfo($model);
            $model = $model->realUnitDetail->saveInfo($model, 'real_unit_detail');
            $model = $model->mainUnitDetail->saveInfo($model, 'main_unit_detail');
        });

        self::updating(function ($model) {
            if ($model->product_id != $model->getOriginal('product_id')) {
                $model = $model->product->saveInfo($model);
            }

            if ($model->unit_detail_id != $model->getOriginal('unit_detail_id')) {
                $model = $model->unitDetail->saveInfo($model);
            }

            if (
                $model->unit_detail_id != $model->getOriginal('unit_detail_id')
                || $model->quantity != $model->getOriginal('quantity')
            ) {
                $model->saveConvertResult();
                $model = $model->mainUnitDetail->saveInfo($model, 'main_unit_detail');
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

    public function getText()
    {
        return "{$this->product_name} / {$this->product->kode_simrs} / {$this->product->kode_sakti}";
    }

    public function saveConvertResult()
    {
        $convertResult = StockHandler::convertUnitPrice($this->real_stock, 0, $this->real_unit_detail_id);

        $this->converted_real_stock = $convertResult['quantity'];
        $this->main_unit_detail_id = $convertResult['unit_detail_id'];
    }

    /*
    | HAS PRODUCT DETAIL HISTORY
    */
    public function productDetailHistoryRemarksInfo(): array
    {
        return [
            "text" => "Pengeluaran - " . $this->stockOpnameStockOpname->number,
            "access" => PermissionHelper::transform(AccessLogistic::STOCK_EXPENSE, PermissionHelper::TYPE_READ),
            "url" => route("stock_expense.show", Crypt::encrypt($this->stock_expense_id))
        ];
    }

    /*
    | RELATIONSHIP
    */

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id')->withTrashed();
    }

    public function realUnitDetail()
    {
        return $this->belongsTo(UnitDetail::class, 'real_unit_detail_id', 'id');
    }

    public function mainUnitDetail()
    {
        return $this->belongsTo(UnitDetail::class, 'main_unit_detail_id', 'id');
    }

    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_id', 'id');
    }
}
