<?php

namespace App\Livewire\Logistic\Import\StartStock;

use Carbon\Carbon;
use Livewire\Component;
use App\Helpers\General\Alert;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\Core\UserStateHandler;
use App\Helpers\General\ImportDataHelper;
use App\Traits\Livewire\WithImportExcel;
use App\Helpers\Logistic\Stock\StockHandler;
use App\Models\Logistic\Master\Product\Product;
use App\Repositories\Logistic\Master\Unit\UnitRepository;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;

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

    public function mount()
    {
        $this->import_excel = [
            [
                "data" => null,
                "skip_rows" => 1,
                "class" => 'col-4',
                "className" => Product::class,
                "name" => "Import Stok Awal Produk (Rumah Tangga)",
                "format" => "formatImportStartStockProductRumahTangga",
                'storeHandler' => 'store'
            ],
            [
                "data" => null,
                "skip_rows" => 1,
                "class" => 'col-4',
                "className" => Product::class,
                "name" => "Import Stok Awal Produk (Gizi)",
                "format" => "formatImportStartStockProductGizi",
                'storeHandler' => 'store'
            ],
        ];

        $this->transactionDate = Carbon::now()->format("Y-m-d");
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
            $this->companyId = $userState['company_id'];
            $this->warehouses = $userState['warehouses'];
            $this->warehouseId = $userState['warehouse_id'];
        } else {
            $this->companyId = $userState['company_id'];
            $this->warehouses = $userState['warehouses'];
            $this->warehouseId = $userState['warehouse_id'];
        }
    }

    public function formatImportStartStockProductGizi()
    {
        return function ($row) {
            $warehouseId = Crypt::decrypt($this->warehouseId);
            $product_kode_simrs = $row[0];
            $product_name = $row[1];
            $product_quantity = $row[2];
            $product_total_price = $row[3];
            $product_price = $product_total_price / $product_quantity;

            $product = ProductRepository::findBy(whereClause: [
                ['kode_simrs', $product_kode_simrs]
            ]);

            if (!$product) {
                Log::debug("GIZI - KODE TIDAK DITEMUKAN: " . $product_kode_simrs);
                return null;
            }

            StockHandler::createStock(
                $product->id,
                1,
                $warehouseId,
                $this->transactionDate,
                $product_quantity,
                $product_price,
                null,
                null,
                null
            );
        };
    }

    public function formatImportStartStockProductRumahTangga()
    {
        return function ($row) {
            $warehouseId = Crypt::decrypt($this->warehouseId);
            $product_kode_simrs = $row[0];
            $product_name = $row[1];
            $product_quantity = $row[2];
            $product_unit_name = isset(ImportDataHelper::TRANSLATE_UNIT[strtoupper($row[3])]) ? ImportDataHelper::TRANSLATE_UNIT[strtoupper($row[3])] : strtoupper($row[3]);
            $product_price = $row[4];
            $unit_detail = UnitDetailRepository::findBy(whereClause: [
                ['name', strtoupper($product_unit_name)]
            ]);

            if (!$unit_detail) {
                $title_unit = isset(ImportDataHelper::TITLE_UNIT[$product_unit_name]) ? ImportDataHelper::TITLE_UNIT[$product_unit_name] : $product_unit_name;
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
            } else {
                $unit = UnitRepository::findBy(whereClause: [
                    ['id', $unit_detail->unit_id]
                ]);
            }

            $product = ProductRepository::findBy(whereClause: [
                ['kode_simrs', $product_kode_simrs]
            ]);

            if (!$product) {
                Log::debug("RT - KODE TIDAK DITEMUKAN: " . $product_kode_simrs);
                return null;
                // $product = ProductRepository::create([
                //     'unit_id' => $unit->id,
                //     'name' => $product_name,
                //     'type' => Product::TYPE_PRODUCT_WITH_STOCK,
                //     'kode_simrs' => $product_kode_simrs,
                //     'kode_sakti' => null,
                // ]);
            }

            $resultConvert = StockHandler::convertUnitPrice($product_quantity, $product_price, $unit_detail->id);

            StockHandler::createStock(
                $product->id,
                1,
                $warehouseId,
                $this->transactionDate,
                $resultConvert['quantity'],
                $resultConvert['price'],
                null,
                null,
                null
            );
        };
    }

    public function render()
    {
        return view('livewire.logistic.import.start-stock.index');
    }
}
