<?php

namespace App\Livewire\Purchasing\Transaction\PurchaseOrder;

use Carbon\Carbon;
use Livewire\Component;
use App\Helpers\General\Alert;
use Livewire\Attributes\Validate;
use App\Models\Finance\Master\Tax;
use Illuminate\Support\Facades\DB;
use App\Settings\SettingPurchasing;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\Core\UserStateHandler;
use App\Helpers\General\NumberFormatter;
use App\Traits\Livewire\WithImportExcel;
use App\Jobs\Rsmh\Sync\SyncPembelianRTJob;
use App\Models\Rsmh\GudangLog\PembelianRT;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Unit\UnitDetail;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProduct;
use App\Repositories\Finance\Master\Tax\TaxRepository;
use App\Repositories\Logistic\Master\Unit\UnitRepository;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;
use App\Repositories\Rsmh\GudangLog\PembelianRT\PembelianRTRepository;
use App\Repositories\Rsmh\Sync\SyncPembelianRT\SyncPembelianRTRepository;
use App\Repositories\Rsmh\GudangLog\PembelianRT2024\PembelianRT2024Repository;
use App\Repositories\Purchasing\Transaction\PurchaseOrder\PurchaseOrderRepository;
use App\Repositories\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProductRepository;
use App\Repositories\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProductTaxRepository;

class ImportDataPurchaseOrder extends Component
{
    use WithImportExcel;

    #[Validate('required', message: 'Gudang Harus Diisi', onUpdate: false)]
    public $pembelianRTWarehouseId;

    #[Validate('required', message: 'Gudang Harus Diisi', onUpdate: false)]
    public $warehouseId;
    #[Validate('required', message: 'Tanggal Harus Diisi', onUpdate: false)]
    public $transactionDate;

    public $supplierId;
    public $supplierText;

    // Sync
    public $isSyncProgress = false;

    // Helpers
    public $isMultipleCompany = false;
    public $companies = [];
    public $warehouses = [];

    public function mount()
    {

        $this->isSyncProgress = SyncPembelianRTRepository::findBy(whereClause:[
            ['is_done', false]
        ]);
        $this->import_excel = [
            [
                "data" => null,
                "skip_rows" => 8,
                "class" => 'col-4',
                "name" => "Import Data Pembelian",
                "format" => "formatImportDataPembelian",
                'storeHandler' => 'store'
            ],
        ];

        $this->transactionDate = Carbon::now()->format("Y-m");
        $this->loadUserState();
    }

    public function store($index)
    {
        $this->validate();
        if (!$this->warehouseId) {
            Alert::fail($this, "Gagal", "Gudang Belum Dipilih");
            return;
        }

        if (!$this->supplierId) {
            Alert::fail($this, "Gagal", "Supplier Belum Dipilih");
            return;
        }

        $this->storeImport($index);
    }

    public function loadUserState()
    {
        $userState = UserStateHandler::get();
        if ($this->isMultipleCompany) {
            $this->companies = $userState['companies'];
            $this->warehouses = $userState['warehouses'];
            $this->warehouseId = $userState['warehouse_id'];
            $this->pembelianRTWarehouseId = $userState['warehouse_id'];
        } else {
            $this->warehouses = $userState['warehouses'];
            $this->warehouseId = $userState['warehouse_id'];
            $this->pembelianRTWarehouseId = $userState['warehouse_id'];
        }
    }
    
    public function formatImportDataPembelian()
    {
        return function ($row) {
            $companyId = 1;
            $note = 'Import Data';

            $product_kode_simrs = $row[2];
            $product_type = $row[3] == 'YA' ? Product::TYPE_PRODUCT_WITHOUT_STOCK : Product::TYPE_PRODUCT_WITH_STOCK;
            $product_habis_pakai = $row[3]; // YA, TIDAK
            $product_name = $row[4];
            $product_unit_name = isset(UnitDetail::TRANSLATE_UNIT[strtoupper($row[5])]) ? UnitDetail::TRANSLATE_UNIT[strtoupper($row[5])] : strtoupper($row[5]);;
            $product_price = $row[7];
            $product_price_ppn = $row[8];

            if(!$product_kode_simrs)
            {
                return null;
            }

            $taxPpn = TaxRepository::find(SettingPurchasing::get(SettingPurchasing::TAX_PPN_ID));
            $taxPpnId = $taxPpn->id;

            $product = ProductRepository::findBy(whereClause: [
                ['kode_simrs', $product_kode_simrs]
            ]);

            $unit_detail = UnitDetailRepository::findBy(whereClause: [
                ['name', strtoupper($product_unit_name)]
            ]);

            if(!$product)
            {
                if (!$unit_detail) {
                    $title_unit = isset(UnitDetail::TITLE_UNIT[$product_unit_name]) ? UnitDetail::TITLE_UNIT[$product_unit_name] : $product_unit_name;
                    $unit = UnitRepository::findBy(whereClause: [
                        ['title', $title_unit]
                    ]);
    
                    if (!$unit) {
                        $unit = UnitRepository::create([
                            'title' => $title_unit,
                        ]);
                    }
    
                    $unit_detail = UnitDetailRepository::create([
                        'unit_id' => $unit->id,
                        'is_main' => true,
                        'name' => $product_unit_name,
                        'value' => 1,
                    ]);
                } else{
                    $unit = UnitRepository::findBy(whereClause: [
                        ['id', $unit_detail->unit_id]
                    ]);
                }
                
                
                $product = ProductRepository::create([
                    'unit_id' => $unit->id,
                    'name' => $product_name,
                    'type' => $product_type,
                    'kode_simrs' => $product_kode_simrs,
                    'kode_sakti' => null,
                ]);
            }

            for ($i=1; $i <= 31; $i++) { 
                $qty = $row[ 8 + (($i - 1) * 14) + 13];
                if($qty)
                {
                    $validatedData = [
                        'supplier_id' => Crypt::decrypt($this->supplierId),
                        'company_id' => $companyId,
                        'warehouse_id' => Crypt::decrypt($this->warehouseId),
                        'transaction_date' => $this->transactionDate."-".str_pad($i, 2, '0', STR_PAD_LEFT),
                        'note' => $note,
                        'supplier_invoice_number' => null,
                    ];
                    $purchaseOrder = PurchaseOrderRepository::create($validatedData);
                    $objId = $purchaseOrder->id;
    
                    $validatedData = [
                        'purchase_order_id' => $objId,
                        'product_id' => $product->id,
                        'unit_detail_id' => $unit_detail->id,
                        'quantity' => $qty,
                        'price' => $product_price ? $product_price : $product_price_ppn,
                        'code' => null,
                        'batch' => null,
                        'expired_date' => null
                    ];

                    $object = PurchaseOrderProductRepository::create($validatedData);
                    $purchaseOrderProductId = $object->id;

                    if ($product_price_ppn != $product_price && $product_price) {

                        PurchaseOrderProductTaxRepository::create([
                            'purchase_order_product_id' => $purchaseOrderProductId,
                            'tax_id' => $taxPpnId,
                        ]);
                    }
                }
    
            }
        };
    }

    public function syncPembelianRT2024()
    {
        try {
            DB::beginTransaction();
            $countPembelian = PembelianRTRepository::count();

            $validatedData = [
                'total' => $countPembelian,
                'warehouse_id' => Crypt::decrypt($this->pembelianRTWarehouseId),
            ];

            $obj = SyncPembelianRTRepository::create($validatedData);
            $this->isSyncProgress = $obj;
            DB::commit();

            Alert::confirmation(
                $this,
                Alert::ICON_SUCCESS,
                "Berhasil",
                "Data Berhasil Diperbarui",
                "on-dialog-confirm",
                "on-dialog-cancel",
                "Oke",
                "Tutup",
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::fail($this, "Gagal", $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.purchasing.transaction.purchase-order.import-data-purchase-order');
    }
}
