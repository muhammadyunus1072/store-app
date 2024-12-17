<?php

namespace App\Livewire\Logistic\Import\StartStock;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\General\Alert;
use App\Helpers\General\ImportDataHelper;
use App\Helpers\Core\UserStateHandler;
use App\Helpers\Logistic\Stock\StockHandler;
use App\Traits\Livewire\WithImportExcel;
use App\Models\Logistic\Master\Product\Product;
use App\Repositories\Logistic\Master\Product\ProductRepository;

class Index extends Component
{
    use WithImportExcel;

    #[Validate('required', message: 'Gudang Harus Diisi', onUpdate: false)]
    public $warehouseId;
    #[Validate('required', message: 'Tanggal Harus Diisi', onUpdate: false)]
    public $transactionDate;
    public $companyId;

    // Helpers
    public $isMultipleCompany = false;
    public $companies = [];
    public $warehouses = [];

    public function render()
    {
        return view('livewire.logistic.import.start-stock.index');
    }

    public function mount()
    {
        $this->import_excel = [
            [
                "data" => null,
                "skip_rows" => 1,
                "class" => 'col-4',
                'storeHandler' => 'store',
                "name" => "Import Stok Awal Produk (Rumah Tangga)",
                "onImport" => "onImportRumahTangga",
            ],
            [
                "data" => null,
                "skip_rows" => 1,
                "class" => 'col-4',
                'storeHandler' => 'store',
                "name" => "Import Stok Awal Produk (Gizi)",
                "onImport" => "onImportGizi",
            ],
        ];

        $this->transactionDate = Carbon::now()->format("Y-m-d");
        $this->loadUserState();
    }

    public function loadUserState()
    {
        $userState = UserStateHandler::get();
        if ($this->isMultipleCompany) {
            $this->companies = $userState['companies'];
            $this->companyId = $userState['company_id'];
            $this->warehouses = $userState['warehouses'];
            $this->warehouseId = $userState['warehouse_id'];
        } else {
            $this->companyId = $userState['company_id'];
            $this->warehouses = $userState['warehouses'];
            $this->warehouseId = $userState['warehouse_id'];
        }
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

    /*
    | IMPORT: GIZI
    */
    public function onImportGizi($row)
    {
        $product_kode_simrs = $row[0];
        $product_name = $row[1];
        $product_quantity = $row[2];
        $product_total_price = $row[3];
        $product_price = $product_total_price / $product_quantity;

        $product = ProductRepository::findBy(whereClause: [['kode_simrs', $product_kode_simrs]]);
        if (empty($product)) {
            Log::debug("GIZI - STOK AWAL - KODE TIDAK DITEMUKAN: " . $product_kode_simrs);
            return null;
        }

        StockHandler::createStock(
            $product->id,
            1,
            Crypt::decrypt($this->warehouseId),
            $this->transactionDate,
            $product_quantity,
            $product_price,
            null,
            null,
            null
        );
    }

    /*
    | IMPORT: RUMAH TANGGA
    */
    public function onImportRumahTangga($row)
    {
        $product_kode_simrs = $row[0];
        $product_name = $row[1];
        $product_quantity = $row[2];
        $product_unit_name = isset(ImportDataHelper::TRANSLATE_UNIT[strtoupper($row[3])]) ? ImportDataHelper::TRANSLATE_UNIT[strtoupper($row[3])] : strtoupper($row[3]);
        $product_price = $row[4];
        $product_total_price = $row[5];

        $product = ProductRepository::findBy(whereClause: [['kode_simrs', $product_kode_simrs]]);
        if (empty($product)) {
            Log::debug("RT - STOK AWAL - KODE TIDAK DITEMUKAN: " . $product_kode_simrs);
            return null;
        }

        StockHandler::createStock(
            $product->id,
            1,
            Crypt::decrypt($this->warehouseId),
            $this->transactionDate,
            $product_quantity,
            $product_price,
            null,
            null,
            null
        );
    }
}
