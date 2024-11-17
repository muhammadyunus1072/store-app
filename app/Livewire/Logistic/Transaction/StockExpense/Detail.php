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
    public $newObjId;
    public $isShow;

    public $number;
    #[Validate('required', message: 'Tanggal Pengeluaran Harus Diisi', onUpdate: false)]
    public $note;
    public $transactionDate;

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

    // Edit Validity Purposes
    public $oldCompanyId;
    public $oldWarehouseId;
    public $oldTransactionDate;

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
            $this->number = $stockExpense->number;
            $this->transactionDate = Carbon::parse($stockExpense->transaction_date)->format("Y-m-d");
            $this->note = $stockExpense->note;

            $this->companyId = Crypt::encrypt($stockExpense->company_id);
            $this->companyText = $stockExpense->company_name;

            $this->warehouseId = Crypt::encrypt($stockExpense->warehouse_id);
            $this->warehouseText = $stockExpense->warehouse_name;

            $this->oldCompanyId = $this->companyId;
            $this->oldWarehouseId = $this->warehouseId;
            $this->oldTransactionDate = $this->transactionDate;

            foreach ($stockExpense->stockExpenseProducts as $stockExpenseProduct) {
                $unitDetailChoice = UnitDetailRepository::getOptions($stockExpenseProduct->unit_detail_unit_id);
                $unitDetailId = collect($unitDetailChoice)->filter(function ($obj) use ($stockExpenseProduct) {
                    return Crypt::decrypt($obj['id']) == $stockExpenseProduct->unit_detail_id;
                })->first()['id'];

                $this->stockExpenseProducts[] = [
                    'id' => Crypt::encrypt($stockExpenseProduct->id),
                    'product_id' => Crypt::encrypt($stockExpenseProduct->product_id),
                    'product_text' => $stockExpenseProduct->product_name,
                    "unit_detail_id" => $unitDetailId,
                    "unit_detail_choice" => $unitDetailChoice,
                    "quantity" => NumberFormatter::valueToImask($stockExpenseProduct->quantity),

                    // Validity Purposes
                    "old_quantity" => $stockExpenseProduct->quantity,
                ];
            }

            $this->refreshStock();
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
        if (
            $property == 'companyId'
            || $property == 'warehouseId'
            || $property == 'transactionDate'
        ) {
            $this->refreshStock();
        }

        if (str_contains($property, "quantity")) {
            $properties = explode(".", $property);
            $index = $properties[1];
            $this->refreshStock($index);
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            $this->redirectRoute('stock_expense.edit', $this->objId);
        } else {
            $this->redirectRoute('stock_expense.show', $this->newObjId);
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

        // Check Stock
        $this->refreshStock();
        foreach ($this->stockExpenseProducts as $item) {
            if (!$item['is_stock_available']) {
                Alert::fail($this, "Gagal", "Stok {$item['product_text']} Tidak Mencukupi");
                return;
            }
        }

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
                $this->newObjId = Crypt::encrypt($stockExpense->id);
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
            $this->newObjId = null;
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

        $this->stockExpenseProducts[] = [
            'id' => null,
            'product_id' => $productId,
            'product_text' => $product->name,
            "unit_detail_id" => $unitDetailChoice[0]['id'],
            "unit_detail_choice" => $unitDetailChoice,
            "quantity" => 0,

            // Validity Purposes
            "old_quantity" => 0,
        ];

        $this->refreshStock(count($this->stockExpenseProducts) - 1);
    }

    public function removeDetail($index)
    {
        if ($this->stockExpenseProducts[$index]['id']) {
            $this->stockExpenseProductRemoves[] = $this->stockExpenseProducts[$index]['id'];
        }
        unset($this->stockExpenseProducts[$index]);
        $this->stockExpenseProducts = array_values($this->stockExpenseProducts);
    }

    public function refreshStock($index = null)
    {
        $items = [];
        if ($index) {
            $items[$index] = $this->stockExpenseProducts[$index];
        } else {
            $items = $this->stockExpenseProducts;
        }

        foreach ($items as $index => $item) {
            if ($this->isShow) {
                $this->stockExpenseProducts[$index]['current_stock'] = 0;
                $this->stockExpenseProducts[$index]['current_stock_unit_name'] = 0;
                $this->stockExpenseProducts[$index]['is_stock_available'] = 0;
                $this->stockExpenseProducts[$index]['row_color_class'] = "";
                continue;
            }

            if (
                $this->oldCompanyId == $this->companyId
                && $this->oldWarehouseId == $this->warehouseId
                && $this->oldTransactionDate == $this->transactionDate
            ) {
                // Check By Quantity Change
                $qtyToBeUsed = NumberFormatter::imaskToValue($item['quantity']) - $item['old_quantity'];
            } else {
                // Check By Input Quantity
                $qtyToBeUsed = NumberFormatter::imaskToValue($item['quantity']);
            }

            $stockAvailablity = StockHandler::getStockAvailablity(
                productId: Crypt::decrypt($item['product_id']),
                companyId: Crypt::decrypt($this->companyId),
                warehouseId: Crypt::decrypt($this->warehouseId),
                qtyToBeUsed: $qtyToBeUsed,
                unitDetailId: Crypt::decrypt($item["unit_detail_id"]),
                transactionDate: $this->transactionDate,
            );

            $this->stockExpenseProducts[$index]['current_stock'] = $stockAvailablity['current_stock'];
            $this->stockExpenseProducts[$index]['current_stock_unit_name'] = $stockAvailablity['unit_detail_name'];
            $this->stockExpenseProducts[$index]['is_stock_available'] = $stockAvailablity['is_stock_available'];
            $this->stockExpenseProducts[$index]['row_color_class'] = $qtyToBeUsed == 0 ? '' : ($stockAvailablity['is_stock_available'] ? 'table-success' : 'table-danger');
        }
    }
}
