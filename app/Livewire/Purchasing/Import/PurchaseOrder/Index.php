<?php

namespace App\Livewire\Purchasing\Import\PurchaseOrder;

use Carbon\Carbon;
use Livewire\Component;
use App\Helpers\General\Alert;
use App\Helpers\Core\UserStateHandler;
use App\Traits\Livewire\WithImportExcel;
use App\Settings\SettingPurchasing;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use App\Repositories\Finance\Master\Tax\TaxRepository;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Rsmh\GudangLog\PembelianRt\PembelianRtRepository;
use App\Repositories\Rsmh\Sync\SyncPembelianRt\SyncPembelianRtRepository;
use App\Repositories\Purchasing\Transaction\PurchaseOrder\PurchaseOrderRepository;
use App\Repositories\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProductRepository;
use App\Repositories\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProductTaxRepository;

class Index extends Component
{
    use WithImportExcel;

    // Gizi
    #[Validate('required', message: 'Gudang Harus Diisi', onUpdate: false)]
    public $warehouseId;
    #[Validate('required', message: 'Tanggal Harus Diisi', onUpdate: false)]
    public $periode;
    public $supplierId;

    // Rumah Tangga
    #[Validate('required', message: 'Gudang Harus Diisi', onUpdate: false)]
    public $pembelianRTWarehouseId;

    // Sync
    public $syncPembelianRT = false;

    // Helpers
    public $isMultipleCompany = false;
    public $companies = [];
    public $warehouses = [];

    public function render()
    {
        return view('livewire.purchasing.import.purchase-order.index');
    }

    public function mount()
    {
        $this->import_excel = [
            [
                "data" => null,
                "skip_rows" => 8,
                "class" => 'col-4',
                "name" => "Import Data Pembelian Gizi",
                "format" => "importPembelianGIzi",
                'storeHandler' => 'store'
            ],
        ];

        $this->syncPembelianRT = SyncPembelianRTRepository::findBy(whereClause: [['is_done', false]]);
        $this->periode = Carbon::now()->format("Y-m");

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

    public function importPembelianGizi()
    {
        $taxPpn = TaxRepository::find(SettingPurchasing::get(SettingPurchasing::TAX_PPN_ID));
        $taxPpnId = $taxPpn->id;

        $companyId = 1;
        $supplierId = Crypt::decrypt($this->supplierId);
        $warehouseId = Crypt::decrypt($this->warehouseId);
        $periode = $this->periode;
        $note = 'Import Data Gizi';

        // Create Purchase Order
        $purchaseOrder = PurchaseOrderRepository::findBy(whereClause: [['transaction_date', $transactionDate], ['note', $note]]);
        if (empty($purchaseOrder)) {
            $purchaseOrder = PurchaseOrderRepository::create([
                'company_id' => $companyId,
                'supplier_id' => $supplierId,
                'warehouse_id' => $warehouseId,
                'transaction_date' => $transactionDate,
                'note' => $note,
            ]);
        }

        return function ($row) use ($taxPpnId, $companyId, $supplierId, $warehouseId, $periode, $note) {
            if (!$row[2]) {
                return null;
            }

            $product_kode_simrs = $row[2];
            $product_price = $row[7];
            $product_price_ppn = $row[8];

            $product = ProductRepository::findBy(whereClause: [['kode_simrs', $product_kode_simrs]]);
            if (empty($product)) {
                Log::debug("GIZI - PEMBELIAN - KODE TIDAK DITEMUKAN: " . $product_kode_simrs);
                return null;
            }

            for ($i = 1; $i <= 31; $i++) {
                $transactionDate = "$periode-" . str_pad($i, 2, '0', STR_PAD_LEFT);



                // Create Purchase Order Product
                $qty = $row[8 + (($i - 1) * 14) + 13];
                if ($qty) {
                    $purchaseOrderProduct = PurchaseOrderProductRepository::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'product_id' => $product->id,
                        'unit_detail_id' => $product->unit->unitDetailMain->id,
                        'quantity' => $qty,
                        'price' => $product_price ? $product_price : $product_price_ppn,
                        'code' => null,
                        'batch' => null,
                        'expired_date' => null
                    ]);

                    if ($product_price_ppn != $product_price) {
                        PurchaseOrderProductTaxRepository::create([
                            'purchase_order_product_id' => $purchaseOrderProduct->id,
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
            $obj = SyncPembelianRtRepository::create($validatedData);
            $this->syncPembelianRT = $obj;
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
}
