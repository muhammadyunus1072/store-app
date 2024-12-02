<?php

namespace App\Models\Logistic\Transaction\StockRequest;

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
use App\Models\Logistic\Transaction\StockRequest\StockRequest;
use Illuminate\Support\Facades\Crypt;

class StockRequestProduct extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory, HasProductDetailHistory;

    protected $fillable = [
        'stock_request_id',
        'product_id',
        'unit_detail_id',
        'quantity',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model->saveConvertResult();

            $model = $model->product->saveInfo($model);
            $model = $model->unitDetail->saveInfo($model);
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
        return "{$this->product_name} / {$this->product_kode_simrs} / {$this->product_kode_sakti}";
    }

    public function saveConvertResult()
    {
        $convertResult = StockHandler::convertUnitPrice($this->quantity, 0, $this->unit_detail_id);

        $this->converted_quantity = $convertResult['quantity'];
        $this->main_unit_detail_id = $convertResult['unit_detail_id'];
    }

    /*
    | HAS PRODUCT DETAIL HISTORY
    */
    public function productDetailHistoryRemarksInfo(): array
    {
        return [
            "text" => "Permintaan - " . $this->stockRequest->number,
            "access" => PermissionHelper::transform(AccessLogistic::STOCK_REQUEST, PermissionHelper::TYPE_READ),
            "url" => route("stock_request.show", Crypt::encrypt($this->stock_request_id))
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

    public function mainUnitDetail()
    {
        return $this->belongsTo(UnitDetail::class, 'main_unit_detail_id', 'id');
    }

    public function stockRequest()
    {
        return $this->belongsTo(StockRequest::class, 'stock_request_id', 'id');
    }
}
