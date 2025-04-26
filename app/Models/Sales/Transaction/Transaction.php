<?php

namespace App\Models\Sales\Transaction;

use Carbon\Carbon;
use App\Settings\SettingPurchasing;
use App\Models\Core\Company\Company;
use App\Traits\Document\HasApproval;
use App\Permissions\AccessPurchasing;
use App\Permissions\PermissionHelper;
use Illuminate\Support\Facades\Crypt;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\General\NumberGenerator;
use App\Traits\Logistic\HasTransactionStock;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Document\Master\ApprovalConfig;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Warehouse\Warehouse;
use App\Models\Purchasing\Master\Supplier\Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Logistic\Transaction\TransactionStock\TransactionStock;

class Transaction extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory, HasTransactionStock;

    protected $fillable = [
        'cashier_shift_id',

        'status',
        'subtotal',
        'discount',
        'admin_fee',
        'grand_total',
        'paid_amount',
        'change_amount',

        'cancellation_reason',

        // Payment Method Information
        'payment_method_id',
    ];

    protected $guarded = ['id'];
    
    CONST STATUS_PENDING = 'pending';
    CONST STATUS_PAID = 'paid';
    CONST STATUS_CANCEL = 'cancel';
    CONST STATUS_HOLD = 'hold';

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model->number = NumberGenerator::generate(self::class, "INV", null, 5);
        });

        self::deleted(function ($model) {
            $model->transactionStockCancel();

            foreach ($model->transactionDetails as $item) {
                $item->delete();
            }
        });
    }

    public function isDeletable()
    {
        foreach ($this->transactionDetails as $item) {
            if (!$item->isDeletable()) {
                return false;
            }
        }

        return true;
    }

    public function isEditable()
    {
        return true;
    }

    public function onCreated()
    {
        $this->transactionStockProcess();
    }

    public function onUpdated()
    {
        $this->transactionStockProcess();
    }

    /*
    | TRANSACTION STOCK
    */
    public function transactionStockData(): array
    {
        $isValueAddTaxPpn = SettingPurchasing::get(SettingPurchasing::PURCHASE_ORDER_ADD_STOCK_VALUE_INCLUDE_TAX_PPN);

        $data = [
            'transaction_date' => $this->created_at,
            'transaction_type' => TransactionStock::TYPE_SALES,
            'source_company_id' => 1,
            'source_warehouse_id' => 1,
            'destination_company_id' => null,
            'destination_location_id' => null,
            'destination_location_type' => null,
            'products' => [],
            'remarks_id' => $this->id,
            'remarks_type' => get_class($this)
        ];

        foreach ($this->purchaseOrderProducts as $item) {
            if ($item->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            $data['products'][] = [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_detail_id' => $item->unit_detail_id,
                'price' => $item->price + ($isValueAddTaxPpn ? (!empty($item->ppn) ? $item->price * $item->ppn->tax_value / 100.0 : 0) : 0),
                'code' => $item->code,
                'batch' => $item->batch,
                'expired_date' => $item->expired_date,
                'remarks_id' => $item->id,
                'remarks_type' => get_class($item)
            ];
        }

        return $data;
    }

    /*
    | RELATIONSHIP
    */

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id', 'id');
    }
}
