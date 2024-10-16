<?php

namespace App\Models\Logistic\Transaction\GoodReceive;

use App\Settings\SettingLogistic;
use App\Traits\HasApproval;
use App\Helpers\NumberGenerator;
use App\Helpers\Logistic\StockHelper;
use App\Models\Core\Company\Company;
use App\Models\Document\Master\ApprovalConfig;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Warehouse\Warehouse;
use App\Models\Logistic\Transaction\GoodReceive\GoodReceiveProduct;
use App\Models\Purchasing\Master\Supplier\Supplier;
use App\Repositories\Core\Setting\SettingRepository;
use App\Repositories\Logistic\Transaction\ProductDetail\ProductDetailHistoryRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Sis\TrackHistory\HasTrackHistory;

class GoodReceive extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory, HasApproval;

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

    public function isDeletable()
    {
        foreach ($this->goodReceiveProducts as $item) {
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
        $setting = SettingRepository::findBy(whereClause: [['name' => SettingLogistic::NAME]]);
        $settings = json_decode($setting->setting, true);

        if (!isset($settings[SettingLogistic::APPROVAL_KEY_GOOD_RECEIVE]) || empty($settings[SettingLogistic::APPROVAL_KEY_GOOD_RECEIVE])) {
            $this->processStock();
        }

        $approval = ApprovalConfig::createApprovalIfMatch($settings[SettingLogistic::APPROVAL_KEY_GOOD_RECEIVE], $this);
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
        $setting = SettingRepository::findBy(whereClause: [['name', SettingLogistic::NAME]]);
        $settings = json_decode($setting->setting, true);
        $isPriceIntegerValue = $settings[SettingLogistic::PRICE_INTEGER_VALUE];
        $isStockValueIncludeTaxPpn = $settings[SettingLogistic::TAX_PPN_INCLUDE_IN_STOCK_VALUE];

        if ($isPriceIntegerValue) {
            $this->processStockIntegerRule($isStockValueIncludeTaxPpn);
            return;
        }

        // Standard Process
        foreach ($this->goodReceiveProducts as $grProduct) {
            if ($grProduct->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            // Calculate Stock Value
            $price = $grProduct->price;
            if ($isStockValueIncludeTaxPpn && !empty($grProduct->ppn)) {
                $price = $grProduct->price * (100 + $grProduct->ppn->tax_value) / 100.0;
            }

            StockHelper::addStock(
                productId: $grProduct->product_id,
                companyId: $this->company_id,
                warehouseId: $this->warehouse_id,
                quantity: $grProduct->quantity,
                unitDetailId: $grProduct->unit_detail_id,
                transactionDate: $this->receive_date,
                price: $price,
                code: $grProduct->code,
                batch: $grProduct->batch,
                expiredDate: $grProduct->expired_date,
                remarksId: $grProduct->id,
                remarksType: get_class($grProduct),
                remarksNote: '-',
            );
        }
    }

    public function updateStock()
    {
        $setting = SettingRepository::findBy(whereClause: [['name', SettingLogistic::NAME]]);
        $settings = json_decode($setting->setting, true);
        $isPriceIntegerValue = $settings[SettingLogistic::PRICE_INTEGER_VALUE];
        $isStockValueIncludeTaxPpn = $settings[SettingLogistic::TAX_PPN_INCLUDE_IN_STOCK_VALUE];

        if ($isPriceIntegerValue) {
            $this->updateStockIntegerRule($isStockValueIncludeTaxPpn);
            return;
        }

        // Standard Process
        foreach ($this->goodReceiveProducts as $grProduct) {
            if ($grProduct->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            // Calculate Stock Value
            $price = $grProduct->price;
            if ($isStockValueIncludeTaxPpn && !empty($grProduct->ppn)) {
                $price = $grProduct->price * (100 + $grProduct->ppn->tax_value) / 100.0;
            }

            if ($grProduct->created_at == $grProduct->updated_at) {
                // Create
                StockHelper::addStock(
                    productId: $grProduct->product_id,
                    companyId: $this->company_id,
                    warehouseId: $this->warehouse_id,
                    quantity: $grProduct->quantity,
                    unitDetailId: $grProduct->unit_detail_id,
                    transactionDate: $this->receive_date,
                    price: $price,
                    code: $grProduct->code,
                    batch: $grProduct->batch,
                    expiredDate: $grProduct->expired_date,
                    remarksId: $grProduct->id,
                    remarksType: get_class($grProduct),
                    remarksNote: '-',
                );
            } else {
                // Update
                StockHelper::updateStockInformation(
                    remarksId: $grProduct->id,
                    remarksType: get_class($grProduct),
                    remarksNote: '-',
                    transactionSign: 1,

                    unitDetailId: $grProduct->unit_detail_id,
                    entryDate: $this->receive_date,
                    price: $price,
                    code: $grProduct->code,
                    batch: $grProduct->batch,
                    expiredDate: $grProduct->expired_date,
                    newRemarksNote: '-',
                );
                StockHelper::updateStockHistory(
                    remarksId: $grProduct->id,
                    remarksType: get_class($grProduct),
                    remarksNote: '-',
                    transactionSign: 1,

                    unitDetailId: $grProduct->unit_detail_id,
                    quantity: $grProduct->quantity,
                    transactionDate: $this->receive_date,
                    newRemarksNote: '-',
                );
            }
        }
    }

    public function cancelStock()
    {
        foreach ($this->goodReceiveProducts as $grProduct) {
            if ($grProduct->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }

            StockHelper::cancelStock(
                remarksId: $grProduct->id,
                remarksType: get_class($grProduct),
            );
        }
    }

    /*
    | STOCK PROCESS : CASE INTEGER RULE
    */
    public function convertProductsToIntegerRule($isStockValueIncludeTaxPpn)
    {
        $convertedProducts = [];
        $savedValue = 0;

        foreach ($this->goodReceiveProducts as $grProduct) {
            if ($grProduct->product_type != Product::TYPE_PRODUCT_WITH_STOCK) {
                continue;
            }
            // Calculate Stock Value
            $price = $grProduct->price;
            if ($isStockValueIncludeTaxPpn && !empty($grProduct->ppn)) {
                $price = $grProduct->price * (100 + $grProduct->ppn->tax_value) / 100.0;
            }

            $resultConvert = self::convertUnitPrice($grProduct->quantity, $price + ($savedValue / $grProduct->quantity), $grProduct->unit_detail_id);
            if (is_float($resultConvert['price'])) {
                if ($resultConvert['quantity'] > 1) {
                    /*
                    | EXAMPLE:
                    | Product A 10 Pcs @930.2 (Total 9302)
                    | Split Product A into:
                    | - Product A1 9 Pcs @930 (Total 8370)
                    |                                     }---> Total: 9302
                    | - Product A2 1 Pcs @932 (Total  932)
                    */
                    $convertedProducts[$grProduct->id] = [
                        [
                            'productId' => $grProduct->product_id,
                            'productName' => $grProduct->product_name,
                            'companyId' => $this->company_id,
                            'warehouseId' => $this->warehouse_id,
                            'quantity' => $resultConvert['quantity'] - 1,
                            'unitDetailId' => $resultConvert['unit_detail_id'],
                            'transactionDate' => $this->receive_date,
                            'price' => floor($resultConvert['price']),
                            'code' => $grProduct->code,
                            'batch' => $grProduct->batch,
                            'expiredDate' => $grProduct->expired_date,
                            'remarksId' => $grProduct->id,
                            'remarksType' => get_class($grProduct),
                            'remarksNote' => 'N Unit',
                        ],
                        [
                            'productId' => $grProduct->product_id,
                            'productName' => $grProduct->product_name,
                            'companyId' => $this->company_id,
                            'warehouseId' => $this->warehouse_id,
                            'quantity' => 1,
                            'unitDetailId' => $resultConvert['unit_detail_id'],
                            'transactionDate' => $this->receive_date,
                            'price' => floor($resultConvert['price']) + (($resultConvert['price'] - floor($resultConvert['price'])) * $resultConvert['quantity']),
                            'code' => $grProduct->code,
                            'batch' => $grProduct->batch,
                            'expiredDate' => $grProduct->expired_date,
                            'remarksId' => $grProduct->id,
                            'remarksType' => get_class($grProduct),
                            'remarksNote' => '1 Unit',
                        ]
                    ];

                    $savedValue = 0;
                } else {
                    $resultConvert = self::convertUnitPrice($grProduct->quantity, $price, $grProduct->unit_detail_id);

                    /*
                    | EXAMPLE:
                    | Product A 1 Pcs @1000.5
                    | Save 0.5 and use it at other product
                    */
                    $convertedProducts[$grProduct->id] = [[
                        'productId' => $grProduct->product_id,
                        'productName' => $grProduct->product_name,
                        'companyId' => $this->company_id,
                        'warehouseId' => $this->warehouse_id,
                        'quantity' => $resultConvert['quantity'],
                        'unitDetailId' => $resultConvert['unit_detail_id'],
                        'transactionDate' => $this->receive_date,
                        'price' => floor($resultConvert['price']),
                        'code' => $grProduct->code,
                        'batch' => $grProduct->batch,
                        'expiredDate' => $grProduct->expired_date,
                        'remarksId' => $grProduct->id,
                        'remarksType' => get_class($grProduct),
                        'remarksNote' => '-',
                    ]];

                    $savedValue += $resultConvert['price'] - floor($resultConvert['price']);
                }
                continue;
            }

            $convertedProducts[$grProduct->id] = [[
                'productId' => $grProduct->product_id,
                'productName' => $grProduct->product_name,
                'companyId' => $this->company_id,
                'warehouseId' => $this->warehouse_id,
                'quantity' => $resultConvert['quantity'],
                'unitDetailId' => $resultConvert['unit_detail_id'],
                'transactionDate' => $this->receive_date,
                'price' => floor($resultConvert['price']),
                'code' => $grProduct->code,
                'batch' => $grProduct->batch,
                'expiredDate' => $grProduct->expired_date,
                'remarksId' => $grProduct->id,
                'remarksType' => get_class($grProduct),
                'remarksNote' => '-',
            ]];
        }

        if ($savedValue != 0) {
            throw new \Exception("Nilai Total Tidak Dapat Dibulatkan");
        }

        return $convertedProducts;
    }

    public function processStockIntegerRule($isStockValueIncludeTaxPpn)
    {
        $convertedProducts = $this->convertProductsToIntegerRule($isStockValueIncludeTaxPpn);

        foreach ($convertedProducts as $groupProduct) {
            foreach ($groupProduct as $grProduct) {
                StockHelper::addStock(
                    productId: $grProduct['productId'],
                    companyId: $grProduct['companyId'],
                    warehouseId: $grProduct['warehouseId'],
                    quantity: $grProduct['quantity'],
                    unitDetailId: $grProduct['unitDetailId'],
                    transactionDate: $grProduct['transactionDate'],
                    price: $grProduct['price'],
                    code: $grProduct['code'],
                    batch: $grProduct['batch'],
                    expiredDate: $grProduct['expiredDate'],
                    remarksId: $grProduct['remarksId'],
                    remarksType: $grProduct['remarksType'],
                    remarksNote: $grProduct['remarksNote'],
                );
            }
        }
    }

    /*
    | EXPLANATION
    | o Case 2 Stock Type => 2 Stock Type
    | Solusi : 
    | 1.   Langsung melakukan update sesuai dengan remarks_note
    |
    | o Case 1 Stock Type => 2 Stock Type
    | Solusi : 
    | 1.   Perubahan jumlah dan informasi serta remarks_note dari '-' berubah menjadi 'N Unit'
    | 2.   Penambahan 1 jenis stock baru yakni '1 Unit'
    |
    | o Case 2 Stock Type => 1 Stock Type
    | Solusi : 
    | 1.   Jika stock '1 Unit' masih ada maka:
    | 1.1. Hapus stock '1 Unit'
    | 1.2. Perubahan jumlah dan informasi serta remarks_note dari 'N Unit' berubah menjadi '-'
    |
    | 2. Jika stock '1 Unit' tidak ada maka:
    | 2.1. Perubahan jumlah dan informasi 'N Unit'
    | 2.2. Perubahan jumlah dan informasi '1 Unit' dengan nilai yang sama dengan 'N Unit'
    */
    public function updateStockIntegerRule($isStockValueIncludeTaxPpn)
    {
        $convertedProducts = $this->convertProductsToIntegerRule($isStockValueIncludeTaxPpn);

        foreach ($convertedProducts as $groupProduct) {
            $histories = ProductDetailHistoryRepository::getBy(whereClause: [
                ['remarks_id', $groupProduct[0]['remarksId']],
                ['remarks_type', $groupProduct[0]['remarksType']]
            ]);

            // Case 2 Stock Type => 2 Stock Type
            if (count($histories) == count($groupProduct)) {
                foreach ($groupProduct as $grProduct) {
                    StockHelper::updateStockInformation(
                        remarksId: $grProduct['remarksId'],
                        remarksType: $grProduct['remarksType'],
                        remarksNote: $grProduct['remarksNote'],
                        transactionSign: 1,

                        unitDetailId: $grProduct['unitDetailId'],
                        entryDate: $this->receive_date,
                        price: $grProduct['price'],
                        code: $grProduct['code'],
                        batch: $grProduct['batch'],
                        expiredDate: $grProduct['expiredDate'],
                        newRemarksNote: $grProduct['remarksNote'],
                    );
                    StockHelper::updateStockHistory(
                        remarksId: $grProduct['remarksId'],
                        remarksType: $grProduct['remarksType'],
                        remarksNote: $grProduct['remarksNote'],
                        transactionSign: 1,

                        unitDetailId: $grProduct['unitDetailId'],
                        quantity: $grProduct['quantity'],
                        transactionDate: $this->receive_date,
                        newRemarksNote: $grProduct['remarksNote'],
                    );
                }
            }
            // Case 1 Stock Type => 2 Stock Type
            else if (count($histories) == 1 && count($groupProduct) == 2) {
                // First GR Product
                $grProduct = $groupProduct[0];
                StockHelper::updateStockInformation(
                    remarksId: $grProduct['remarksId'],
                    remarksType: $grProduct['remarksType'],
                    remarksNote: '-',
                    transactionSign: 1,

                    unitDetailId: $grProduct['unitDetailId'],
                    entryDate: $this->receive_date,
                    price: $grProduct['price'],
                    code: $grProduct['code'],
                    batch: $grProduct['batch'],
                    expiredDate: $grProduct['expiredDate'],
                    newRemarksNote: $grProduct['remarksNote'],
                );
                StockHelper::updateStockHistory(
                    remarksId: $grProduct['remarksId'],
                    remarksType: $grProduct['remarksType'],
                    remarksNote: '-',
                    transactionSign: 1,

                    unitDetailId: $grProduct['unitDetailId'],
                    quantity: $grProduct['quantity'],
                    transactionDate: $this->receive_date,
                    newRemarksNote: $grProduct['remarksNote'],
                );

                // Second GR Product
                $grProduct = $groupProduct[1];
                StockHelper::addStock(
                    productId: $grProduct['productId'],
                    companyId: $grProduct['companyId'],
                    warehouseId: $grProduct['warehouseId'],
                    quantity: $grProduct['quantity'],
                    unitDetailId: $grProduct['unitDetailId'],
                    transactionDate: $grProduct['transactionDate'],
                    price: $grProduct['price'],
                    code: $grProduct['code'],
                    batch: $grProduct['batch'],
                    expiredDate: $grProduct['expiredDate'],
                    remarksId: $grProduct['remarksId'],
                    remarksType: $grProduct['remarksType'],
                    remarksNote: $grProduct['remarksNote'],
                );
            }
            // Case 2 Stock Type => 1 Stock Type
            else if (count($histories) == 2 && count($groupProduct) == 1) {
                $grProduct = $groupProduct[0];

                // Check History (Note: 1 Unit)
                $stock = StockHelper::getStockByRemarks(
                    remarksId: $grProduct['remarksId'],
                    remarksType: $grProduct['remarksType'],
                    remarksNote: '1 Unit',
                    transactionSign: 1,
                    isGrouped: true,
                );

                if ($stock == 1) {
                    StockHelper::deleteStock(
                        remarksId: $grProduct['remarksId'],
                        remarksType: $grProduct['remarksType'],
                        remarksNote: '1 Unit'
                    );

                    StockHelper::updateStockInformation(
                        remarksId: $grProduct['remarksId'],
                        remarksType: $grProduct['remarksType'],
                        remarksNote: 'N Unit',
                        transactionSign: 1,

                        unitDetailId: $grProduct['unitDetailId'],
                        entryDate: $this->receive_date,
                        price: $grProduct['price'],
                        code: $grProduct['code'],
                        batch: $grProduct['batch'],
                        expiredDate: $grProduct['expiredDate'],
                        newRemarksNote: $grProduct['remarksNote'],
                    );
                    StockHelper::updateStockHistory(
                        remarksId: $grProduct['remarksId'],
                        remarksType: $grProduct['remarksType'],
                        remarksNote: 'N Unit',
                        transactionSign: 1,

                        unitDetailId: $grProduct['unitDetailId'],
                        quantity: $grProduct['quantity'],
                        transactionDate: $this->receive_date,
                        newRemarksNote: $grProduct['remarksNote'],
                    );
                } else {
                    StockHelper::updateStockInformation(
                        remarksId: $grProduct['remarksId'],
                        remarksType: $grProduct['remarksType'],
                        remarksNote: '1 Unit',
                        transactionSign: 1,

                        unitDetailId: $grProduct['unitDetailId'],
                        entryDate: $this->receive_date,
                        price: $grProduct['price'],
                        code: $grProduct['code'],
                        batch: $grProduct['batch'],
                        expiredDate: $grProduct['expiredDate'],
                        newRemarksNote: '1 Unit',
                    );
                    StockHelper::updateStockHistory(
                        remarksId: $grProduct['remarksId'],
                        remarksType: $grProduct['remarksType'],
                        remarksNote: '1 Unit',
                        transactionSign: 1,

                        unitDetailId: $grProduct['unitDetailId'],
                        quantity: 1,
                        transactionDate: $this->receive_date,
                        newRemarksNote: '1 Unit',
                    );


                    StockHelper::updateStockInformation(
                        remarksId: $grProduct['remarksId'],
                        remarksType: $grProduct['remarksType'],
                        remarksNote: 'N Unit',
                        transactionSign: 1,

                        unitDetailId: $grProduct['unitDetailId'],
                        entryDate: $this->receive_date,
                        price: $grProduct['price'],
                        code: $grProduct['code'],
                        batch: $grProduct['batch'],
                        expiredDate: $grProduct['expiredDate'],
                        newRemarksNote: 'N Unit',
                    );
                    StockHelper::updateStockHistory(
                        remarksId: $grProduct['remarksId'],
                        remarksType: $grProduct['remarksType'],
                        remarksNote: 'N Unit',
                        transactionSign: 1,

                        unitDetailId: $grProduct['unitDetailId'],
                        quantity: $grProduct['quantity'],
                        transactionDate: $this->receive_date,
                        newRemarksNote: 'N Unit',
                    );
                }
            }
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
