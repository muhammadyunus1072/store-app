<?php

namespace App\Livewire\Logistic\Transaction\StockExpense;

use Carbon\Carbon;
use Livewire\Component;
use App\Helpers\General\Alert;
use Livewire\Attributes\Validate;
use App\Models\Finance\Master\Tax;
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
use App\Repositories\Logistic\Transaction\StockExpense\StockExpenseRepository;
use App\Repositories\Purchasing\Transaction\PurchaseOrder\PurchaseOrderRepository;
use App\Repositories\Logistic\Transaction\StockExpense\StockExpenseProductRepository;
use App\Repositories\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProductRepository;
use App\Repositories\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProductTaxRepository;

class ImportDataStockExpense extends Component
{
    use WithImportExcel;

    #[Validate('required', message: 'Gudang Harus Diisi', onUpdate: false)]
    public $warehouseId;
    #[Validate('required', message: 'Tanggal Harus Diisi', onUpdate: false)]
    public $transactionDate;

    public $supplierText;

    // Helpers
    public $isMultipleCompany = false;
    public $companies = [];
    public $warehouses = [];

    public function mount()
    {
        $this->import_excel = [
            [
                "data" => null,
                "skip_rows" => 8,
                "class" => 'col-4',
                "name" => "Import Data Pengeluaran",
                "format" => "formatImportDataPengeluaran",
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

        $this->storeImport($index);
    }

    public function loadUserState()
    {
        $userState = UserStateHandler::get();
        if ($this->isMultipleCompany) {
            $this->companies = $userState['companies'];
            $this->warehouses = $userState['warehouses'];
            $this->warehouseId = $userState['warehouse_id'];
        } else {
            $this->warehouses = $userState['warehouses'];
            $this->warehouseId = $userState['warehouse_id'];
        }
    }
    
    public function formatImportDataPengeluaran()
    {
        return function ($row) {
            $companyId = 1;
            $note = 'Import Data';

            $product_kode_simrs = $row[2];
            $product_habis_pakai = $row[3]; // YA, TIDAK
            $product_name = $row[4];
            $product_unit_name = isset(UnitDetail::TRANSLATE_UNIT[strtoupper($row[5])]) ? UnitDetail::TRANSLATE_UNIT[strtoupper($row[5])] : strtoupper($row[5]);;
            $product_price = $row[7];

            if(!$product_kode_simrs)
            {
                return null;
            }
            $unit_detail = UnitDetailRepository::findBy(whereClause: [
                ['name', strtoupper($product_unit_name)]
            ]);

            $product = ProductRepository::findBy(whereClause: [
                ['kode_simrs', $product_kode_simrs]
            ]);
            if(!$product)
            {
                return null;
            }
            for ($i=1; $i <= 31; $i++) { 
                $qty = $row[ 8 + (($i - 1) * 14) + 13];
                if($qty)
                {
                    $validatedData = [
                        'company_id' => $companyId,
                        'warehouse_id' => Crypt::decrypt($this->warehouseId),
                        'transaction_date' => $this->transactionDate."-".str_pad($i, 2, '0', STR_PAD_LEFT),
                        'note' => $note,
                    ];
                    $stockExpense = StockExpenseRepository::create($validatedData);
                    $objId = $stockExpense->id;
    
                    $resultConvert = StockHandler::convertUnitPrice($qty, $product_price, $unit_detail->id);
                    $validatedData = [
                        'stock_expense_id' => $objId,
                        'product_id' => $product->id,
                        'unit_detail_id' => $unit_detail->id,
                        'quantity' => $resultConvert['quantity'],
                    ];

                    $object = StockExpenseProductRepository::create($validatedData);
                }
    
            }
        };
    }

    public function render()
    {
        return view('livewire.logistic.transaction.stock-expense.import-data-stock-expense');
    }
}
