<?php

namespace App\Livewire\Logistic\Import\StockExpense;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Crypt;
use App\Helpers\General\Alert;
use App\Helpers\Core\UserStateHandler;
use App\Traits\Livewire\WithImportExcel;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Transaction\StockExpense\StockExpenseRepository;
use App\Repositories\Logistic\Transaction\StockExpense\StockExpenseProductRepository;
use Illuminate\Support\Facades\Log;

class Index extends Component
{
    use WithImportExcel;

    #[Validate('required', message: 'Gudang Harus Diisi', onUpdate: false)]
    public $warehouseId;
    #[Validate('required', message: 'Tahun Bulan Harus Diisi', onUpdate: false)]
    public $periode;
    public $noteGizi = 'Import Data Gizi';

    // Helpers
    public $isMultipleCompany = false;
    public $companies = [];
    public $warehouses = [];
    public $createdStockExpenseIds = [];

    public function render()
    {
        return view('livewire.logistic.import.stock-expense.index');
    }

    public function mount()
    {
        $this->import_excel = [
            [
                "data" => null,
                "skip_rows" => 8,
                "class" => 'col-4',
                'storeHandler' => 'store',
                "name" => "Import Data Pengeluaran",
                'onImportStart' => 'onImportGiziStart',
                "onImport" => "onImportGizi",
                'onImportDone' => 'onImportGiziDone',
            ],
        ];

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

    /*
    | IMPORT GIZI
    */
    public function onImportGiziStart()
    {
        $this->createdStockExpenseIds = [];
        $warehouseId = Crypt::decrypt($this->warehouseId);
        $dateStart = Carbon::parse("{$this->periode}-01")->startOfMonth();
        $dateEnd = Carbon::parse("{$this->periode}-01")->endOfMonth();

        // Delete Old
        StockExpenseRepository::deleteBy(whereClause: [
            ['transaction_date', '>=', $dateStart],
            ['transaction_date', '<=', $dateEnd],
            ['note', $this->noteGizi]
        ]);

        // Create New
        while ($dateStart->lte($dateEnd)) {
            $transactionDate = $dateStart->format('Y-m-d');
            $stockExpense = StockExpenseRepository::findBy(whereClause: [['transaction_date', $transactionDate], ['note', $this->noteGizi]]);
            if (empty($stockExpense)) {
                $stockExpense = StockExpenseRepository::create([
                    'company_id' => 1,
                    'warehouse_id' => $warehouseId,
                    'transaction_date' => $transactionDate,
                    'note' => $this->noteGizi,
                ]);
            }

            $this->createdStockExpenseIds[$transactionDate] = $stockExpense->id;
            $dateStart->addDay();
        }
    }

    public function onImportGiziDone()
    {
        StockExpenseRepository::deleteWithEmptyProducts();

        $stockExpenses = StockExpenseRepository::getBy(whereClause: [['id', 'IN', $this->createdStockExpenseIds]]);
        foreach ($stockExpenses as $stockExpense) {
            $stockExpense->onUpdated();
        }
    }

    public function onImportGizi($row)
    {
        if (!$row[2]) {
            return null;
        }

        $product_kode_simrs = $row[2];
        $product_name = $row[4];
        $product_unit = $row[5];

        $product = ProductRepository::findBy(whereClause: [['kode_simrs', $product_kode_simrs]]);
        if (empty($product)) {
            Log::debug("GIZI - PEMAKAIAN {$this->periode} / - KODE TIDAK DITEMUKAN: {$product_kode_simrs};{$product_name};{$product_unit}");
            return null;
        }

        for ($i = 1; $i <= 31; $i++) {
            $transactionDate = "{$this->periode}-" . str_pad($i, 2, '0', STR_PAD_LEFT);

            // Create Stock Expense Product
            $qty = $row[8 + (($i - 1) * 14) + 13];
            if ($qty) {
                StockExpenseProductRepository::create([
                    'stock_expense_id' => $this->createdStockExpenseIds[$transactionDate],
                    'product_id' => $product->id,
                    'unit_detail_id' => $product->unit->unitDetailMain->id,
                    'quantity' => $qty,
                ]);
            }
        }
    }
}
