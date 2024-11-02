<?php

namespace App\Livewire\Logistic\Transaction\StockExpense;

use App\Helpers\Core\UserStateHandler;
use App\Helpers\General\Alert;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Helpers\General\NumberFormatter;
use App\Helpers\Logistic\Stock\StockHandler;
use App\Models\Logistic\Transaction\StockExpense\StockExpenseProduct;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;
use App\Repositories\Logistic\Transaction\StockExpense\StockExpenseRepository;
use App\Repositories\Logistic\Transaction\StockExpense\StockExpenseProductRepository;
use App\Settings\SettingCore;
use Carbon\Carbon;

class Detail extends Component
{
    public $objId;

    #[Validate('required', message: 'Tanggal Pengeluaran Harus Diisi', onUpdate: false)]
    public $transactionDate;
    public $note;

    public $companyId;
    public $companyText;

    public $warehouseId;
    public $warehouseText;

    public $stockExpenseProducts = [];
    public $stockExpenseProductRemoves = [];

    // Helpers
    public $isMultipleCompany = false;
    public $companies = [];
    public $warehouses = [];

    public $historyRemarksIds = []; // History Datatable
    public $historyRemarksType = StockExpenseProduct::class; // History Datatable

    public function render()
    {
        return view('livewire.logistic.transaction.stock-expense.detail');
    }

    public function mount()
    {
        $this->loadSetting();
        $this->loadUserState();

        $this->transactionDate = Carbon::now()->format("Y-m-d");
        $this->note = "";

        if ($this->objId) {
            $stockExpense = StockExpenseRepository::find(Crypt::decrypt($this->objId));
            $this->transactionDate = Carbon::parse($stockExpense->transaction_date)->format("Y-m-d");
            $this->note = $stockExpense->note;

            $this->companyId = Crypt::encrypt($stockExpense->company_id);
            $this->companyText = $stockExpense->company_name;

            $this->warehouseId = Crypt::encrypt($stockExpense->warehouse_id);
            $this->warehouseText = $stockExpense->warehouse_name;

            foreach ($stockExpense->stockExpenseProducts as $stockExpenseProduct) {
                $unitDetailChoice = UnitDetailRepository::getOptions($stockExpenseProduct->unit_detail_unit_id);
                $unitDetailId = collect($unitDetailChoice)->filter(function ($obj) use ($stockExpenseProduct) {
                    return Crypt::decrypt($obj['id']) == $stockExpenseProduct->unit_detail_id;
                })->first()['id'];

                $currentStock = StockHandler::getStock(
                    productDetailId: null,
                    productId: $stockExpenseProduct->product_id,
                    companyId: $stockExpense->company_id,
                    warehouseId: $stockExpense->warehouse_id,
                    thresholdDate: $this->transactionDate
                );

                $this->stockExpenseProducts[] = [
                    'id' => Crypt::encrypt($stockExpenseProduct->id),
                    'product_id' => Crypt::encrypt($stockExpenseProduct->product_id),
                    'product_text' => $stockExpenseProduct->product_name,
                    "unit_detail_id" => $unitDetailId,
                    "unit_detail_choice" => $unitDetailChoice,
                    "quantity" => NumberFormatter::valueToImask($stockExpenseProduct->quantity),
                    'current_stock' => NumberFormatter::format($currentStock),
                    'current_stock_unit_name' => $unitDetailChoice[0]['name'],
                    "old_quantity" => NumberFormatter::valueToImask($stockExpenseProduct->quantity),
                ];

                // History Datatable
                $this->historyRemarksIds[] = $stockExpenseProduct->id;
            }
        }
    }

    public function loadSetting()
    {
        $this->isMultipleCompany = SettingCore::get(SettingCore::MULTIPLE_COMPANY);
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

    public function updated($property)
    {
        if ($property == 'companyId' || $property == 'warehouseId' || $property == 'transactionDate') {
            foreach ($this->stockExpenseProducts as $index => $item) {
                $currentStock = StockHandler::getStock(
                    productDetailId: null,
                    productId: Crypt::decrypt($item['product_id']),
                    companyId: Crypt::decrypt($this->companyId),
                    warehouseId: Crypt::decrypt($this->warehouseId),
                    thresholdDate: $this->transactionDate,
                );

                $this->stockExpenseProducts[$index]['current_stock'] = NumberFormatter::format($currentStock);
            }
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            $this->redirectRoute('stock_expense.edit', $this->objId);
        } else {
            $this->redirectRoute('stock_expense.create');
        }
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('stock_expense.index');
    }

    public function store()
    {
        if (!$this->companyId) {
            Alert::fail($this, "Gagal", "Perusahaan Belum Diinput");
            return;
        }
        if (!$this->warehouseId) {
            Alert::fail($this, "Gagal", "Gudang Belum Diinput");
            return;
        }
        if (count($this->stockExpenseProducts) == 0) {
            Alert::fail($this, "Gagal", "Barang-barang yang dikeluarkan belum diinput");
            return;
        }

        $this->validate();

        $validatedData = [
            'company_id' => Crypt::decrypt($this->companyId),
            'warehouse_id' => Crypt::decrypt($this->warehouseId),
            'transaction_date' => $this->transactionDate,
            'note' => $this->note,
        ];

        try {
            DB::beginTransaction();
            if ($this->objId) {
                $decId = Crypt::decrypt($this->objId);
                StockExpenseRepository::update($decId, $validatedData);
                $stockExpense = StockExpenseRepository::find($decId);
            } else {
                $stockExpense = StockExpenseRepository::create($validatedData);
            }

            // ===============================
            // ==== STOCK EXPENSE PRODUCT ====
            // ===============================
            foreach ($this->stockExpenseProducts as $stockExpenseProduct) {
                $validatedData = [
                    'stock_expense_id' => $stockExpense->id,
                    'product_id' => Crypt::decrypt($stockExpenseProduct['product_id']),
                    'unit_detail_id' => Crypt::decrypt($stockExpenseProduct['unit_detail_id']),
                    'quantity' => NumberFormatter::imaskToValue($stockExpenseProduct['quantity']),
                ];

                if ($stockExpenseProduct['id']) {
                    $stockExpenseProductId = Crypt::decrypt($stockExpenseProduct['id']);
                    $object = StockExpenseProductRepository::update($stockExpenseProductId, $validatedData);
                } else {
                    $object = StockExpenseProductRepository::create($validatedData);
                    $stockExpenseProductId = $object->id;
                }
            }

            foreach ($this->stockExpenseProductRemoves as $item) {
                StockExpenseProductRepository::delete(Crypt::decrypt($item));
            }

            if ($this->objId) {
                $stockExpense->onUpdated();
            } else {
                $stockExpense->onCreated();
            }

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

    /*
    | HANDLER : STOCK EXPENSE PRODUCT
    */
    public function addDetail($productId)
    {
        $product = ProductRepository::find(Crypt::decrypt($productId));
        $unitDetailChoice = UnitDetailRepository::getOptions($product->unit_id);
        $currentStock = StockHandler::getStock(
            productDetailId: null,
            productId: $product->id,
            companyId: Crypt::decrypt($this->companyId),
            warehouseId: Crypt::decrypt($this->warehouseId),
            thresholdDate: $this->transactionDate,
        );

        $this->stockExpenseProducts[] = [
            'id' => null,
            'product_id' => $productId,
            'product_text' => $product->name,
            "unit_detail_id" => $unitDetailChoice[0]['id'],
            "unit_detail_choice" => $unitDetailChoice,
            "quantity" => 0,
            'current_stock' => NumberFormatter::valueToImask($currentStock),
            'current_stock_unit_name' => $unitDetailChoice[0]['name'],
            "old_quantity" => 0,
        ];
    }

    public function removeDetail($index)
    {
        if ($this->stockExpenseProducts[$index]['id']) {
            $this->stockExpenseProductRemoves[] = $this->stockExpenseProducts[$index]['id'];
        }
        unset($this->stockExpenseProducts[$index]);
        $this->stockExpenseProducts = array_values($this->stockExpenseProducts);
    }
}
