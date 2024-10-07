<?php

namespace App\Models\Logistic\Transaction\GoodReceive;

use App\Helpers\Logistic\StockHelper;
use App\Helpers\NumberGenerator;
use App\Models\Core\Company\Company;
use App\Models\Logistic\Master\Product\Product;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Logistic\Master\Warehouse\Warehouse;
use App\Models\Purchasing\Master\Supplier\Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrder;
use App\Models\Logistic\Transaction\GoodReceive\GoodReceiveProduct;

class GoodReceive extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'purchase_order_id',
        'company_id',
        'supplier_id',
        'supplier_invoice_number',
        'warehouse_id',
        'receive_date',
        'note',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model->number = NumberGenerator::generate(self::class, "GR");

            $model = $model->supplier->saveInfo($model);
            $model = $model->company->saveInfo($model);
            $model = $model->warehouse->saveInfo($model);
        });

        self::updating(function ($model) {
            if ($model->supplier_id != $model->getOriginal('supplier_id')) {
                $model = $model->supplier->saveInfo($model);
            }
            if ($model->company_id != $model->getOriginal('company_id')) {
                $model = $model->company->saveInfo($model);
            }
            if ($model->warehouse_id != $model->getOriginal('warehouse_id')) {
                $model = $model->warehouse->saveInfo($model);
            }
        });

        self::deleted(function ($model) {
            foreach ($model->goodReceiveProducts as $item) {
                $item->delete();
            }
        });
    }

    public function processStock()
    {
        foreach ($this->goodReceiveProducts as $grProduct) {
            if ($grProduct->product->type == Product::TYPE_PRODUCT_WITH_STOCK) {
                StockHelper::addStock(
                    productId: $grProduct->product_id,
                    companyId: $this->company_id,
                    warehouseId: $this->warehouse_id,
                    quantity: $grProduct->quantity,
                    unitDetailId: $grProduct->unit_detail_id,
                    transactionDate: $this->receive_date,
                    price: $grProduct->price,
                    code: $grProduct->code,
                    batch: $grProduct->batch,
                    expiredDate: $grProduct->expired_date,
                    remarksId: $grProduct->id,
                    remarksType: get_class($grProduct)
                );
            }
        }
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

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    public function goodReceiveProducts()
    {
        return $this->hasMany(GoodReceiveProduct::class, 'good_receive_id', 'id');
    }
}
