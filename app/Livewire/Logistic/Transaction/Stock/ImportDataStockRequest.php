<?php

namespace App\Livewire\Logistic\Transaction\StockRequest;

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
use App\Helpers\Logistic\Stock\StockHandler;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Unit\UnitDetail;
use App\Repositories\Finance\Master\Tax\TaxRepository;
use App\Repositories\Logistic\Master\Unit\UnitRepository;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;
use App\Repositories\Logistic\Transaction\StockRequest\StockRequestRepository;
use App\Repositories\Purchasing\Transaction\PurchaseOrder\PurchaseOrderRepository;
use App\Repositories\Logistic\Transaction\StockRequest\StockRequestProductRepository;
use App\Repositories\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProductRepository;
use App\Repositories\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProductTaxRepository;
use App\Repositories\Rsmh\GudangLog\PengeluaranRT\PengeluaranRTRepository;
use App\Repositories\Rsmh\Sync\SyncPengeluaranRt\SyncPengeluaranRtRepository;

class ImportDataStockRequest extends Component
{
    use WithImportExcel;

    public $importSourceWarehouseId;

    // Helpers
    public $isMultipleCompany = false;
    public $companies = [];
    public $warehouses = [];

    public $isSyncStockRequest = false;

    public function mount()
    {
        $this->loadUserState();
    }

    public function store($index)
    {
        $this->validate();
        if (!$this->warehouseId) {
            Alert::fail($this, "Gagal", "Gudang Belum Dipilih");
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
            $this->importSourceWarehouseId = $userState['warehouse_id'];
        } else {
            $this->warehouses = $userState['warehouses'];
            $this->importSourceWarehouseId = $userState['warehouse_id'];
        }
    }

    public function syncStockRequest()
    {
        try {
            DB::beginTransaction();
            
            $countPengeluaranRT = PengeluaranRTRepository::count();

            $validatedData = [
                'total' => $countPengeluaranRT,
                'source_warehouse_id' => Crypt::decrypt($this->importSourceWarehouseId),
            ];
            
            $obj = SyncPengeluaranRtRepository::create($validatedData);
            $this->isSyncStockRequest = $obj;
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
        return view('livewire.logistic.transaction.stock-request.import-data-stock-request');
    }
}
