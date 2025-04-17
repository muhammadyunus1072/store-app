<?php

namespace App\Livewire\Logistic\Transaction\StockOpname;

use App\Helpers\Core\UserStateHandler;
use App\Helpers\General\Alert;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Helpers\General\NumberFormatter;
use App\Helpers\Logistic\Stock\StockHandler;
use App\Models\Logistic\Master\Warehouse\Warehouse;
use App\Models\Logistic\Transaction\StockOpname\StockOpnameDetail;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;
use App\Repositories\Logistic\Transaction\StockOpname\StockOpnameRepository;
use App\Repositories\Logistic\Transaction\StockOpname\StockOpnameDetailRepository;
use App\Settings\SettingCore;
use Carbon\Carbon;

class Detail extends Component
{
    public $objId;
    public $newObjId;
    public $isShow;

    public $number;
    #[Validate('required', message: 'Tanggal Stok Opname Harus Diisi', onUpdate: false)]
    public $transactionDate;
    public $note;

    public $companyId;
    public $companyText;

    public $warehouseId;
    public $warehouseText;

    public $stockOpnameDetails = [];
    public $stockOpnameDetailRemoves = [];

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
        return view('livewire.logistic.transaction.stock-opname.detail');
    }

    public function mount()
    {
        $this->loadSetting();
        $this->loadUserState();

        $this->transactionDate = Carbon::now()->format("Y-m-d");
        $this->note = "";

        if ($this->objId) {
            $stockOpname = StockOpnameRepository::find(Crypt::decrypt($this->objId));
            $this->number = $stockOpname->number;
            $this->transactionDate = Carbon::parse($stockOpname->transaction_date)->format("Y-m-d");
            $this->note = $stockOpname->note;

            $this->companyId = Crypt::encrypt($stockOpname->company_id);
            $this->companyText = $stockOpname->company_name;

            $this->warehouseId = Crypt::encrypt($stockOpname->location_id);
            $this->warehouseText = $stockOpname->location_name;

            $this->oldCompanyId = $this->companyId;
            $this->oldWarehouseId = $this->warehouseId;
            $this->oldTransactionDate = $this->transactionDate;

            foreach ($stockOpname->stockOpnameDetails as $stockOpnameDetail) {
                $unitDetailChoice = UnitDetailRepository::getOptions($stockOpnameDetail->real_unit_detail_unit_id);
                // dd($unitDetailChoice);
                $unitDetailId = collect($unitDetailChoice)->filter(function ($obj) use ($stockOpnameDetail) {
                    return Crypt::decrypt($obj['id']) == $stockOpnameDetail->real_unit_detail_id;
                })->first()['id'];

                $this->stockOpnameDetails[] = [
                    'id' => Crypt::encrypt($stockOpnameDetail->id),
                    'product_id' => Crypt::encrypt($stockOpnameDetail->product_id),
                    'product_text' => $stockOpnameDetail->getText(),
                    "unit_detail_id" => $unitDetailId,
                    "unit_detail_choice" => $unitDetailChoice,
                    "quantity" => NumberFormatter::valueToImask($stockOpnameDetail->real_stock),

                    'system_unit_name' => $stockOpnameDetail->system_unit_name,
                    'system_stock' => number_format($stockOpnameDetail->system_stock),
                    'real_stock' => number_format($stockOpnameDetail->real_stock),
                    'difference' => number_format($stockOpnameDetail->difference),

                    // Validity Purposes
                    "old_quantity" => $stockOpnameDetail->real_stock,
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

    public function updated($property, $value)
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

        if (str_contains($property, "real_stock") || str_contains($property, "real_unit_detail_id")) {
            $properties = explode(".", $property);
            $index = $properties[1];
            $this->stockOpnameDetails[$index]['difference'] = imaskToValue($this->stockOpnameDetails[$index]['real_stock']) - $this->stockOpnameDetails[$index]['system_stock'];
            // consoleLog($this, $this->stockOpnameDetails[$index]);
            // consoleLog($this, $property);
            // consoleLog($this, $value);
            // if(str_contains($property, "real_unit_detail_id")){
            //     consoleLog($this, $this->stockOpnameDetails[$index]['unit_detail_choice']);
            //     $value = collect($this->stockOpnameDetails[$index]['unit_detail_choice'])->where('id', '=', $value)->first()['value'];
            //     $this->stockOpnameDetails[$index]['real_unit_detail_value'] = $value;
            //     consoleLog($this, $value);
            // }
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            $this->redirectRoute('stock_opname.edit', $this->objId);
        } else {
            $this->redirectRoute('stock_opname.show', $this->newObjId);
        }
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('stock_opname.index');
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
        if (count($this->stockOpnameDetails) == 0) {
            Alert::fail($this, "Gagal", "Barang-barang yang dikeluarkan belum diinput");
            return;
        }

        // Check Stock
        $this->refreshStock();
        foreach ($this->stockOpnameDetails as $item) {
            if (!$item['is_stock_available']) {
                Alert::fail($this, "Gagal", "Stok {$item['product_text']} Tidak Mencukupi");
                return;
            }
        }

        $validatedData = [
            'company_id' => Crypt::decrypt($this->companyId),
            'location_id' => Crypt::decrypt($this->warehouseId),
            'location_type' => Warehouse::class,
            'stock_opname_date' => $this->transactionDate,
            'status' => "STOCK OPNAME",
            'note' => $this->note,
        ];

        try {
            DB::beginTransaction();

            consoleLog($this, $this->stockOpnameDetails);
            if ($this->objId) {
                $decId = Crypt::decrypt($this->objId);
                StockOpnameRepository::update($decId, $validatedData);
                $stockOpname = StockOpnameRepository::find($decId);
            } else {
                $stockOpname = StockOpnameRepository::create($validatedData);
                $this->newObjId = Crypt::encrypt($stockOpname->id);
            }

            // ===============================
            // ==== STOCK opname PRODUCT ====
            // ===============================
            foreach ($this->stockOpnameDetails as $stockOpnameDetail) {
                $validatedData = [
                    'stock_opname_id' => $stockOpname->id,
                    'product_id' => Crypt::decrypt($stockOpnameDetail['product_id']),
                    'real_unit_detail_id' => Crypt::decrypt($stockOpnameDetail['real_unit_detail_id']),
                    'real_stock' => NumberFormatter::imaskToValue($stockOpnameDetail['real_stock']),
                    'system_stock' => NumberFormatter::imaskToValue($stockOpnameDetail['system_stock']),
                    'difference' => NumberFormatter::imaskToValue($stockOpnameDetail['difference']),
                ];

                if ($stockOpnameDetail['id']) {
                    $stockOpnameDetailId = Crypt::decrypt($stockOpnameDetail['id']);
                    $object = StockOpnameDetailRepository::update($stockOpnameDetailId, $validatedData);
                } else {
                    $object = StockOpnameDetailRepository::create($validatedData);
                    $stockOpnameDetailId = $object->id;
                }
                consoleLog($this, $stockOpnameDetailId);
            }

            foreach ($this->stockOpnameDetailRemoves as $item) {
                StockOpnameDetailRepository::delete(Crypt::decrypt($item));
            }

            if ($this->objId) {
                $stockOpname->onUpdated();
            } else {
                $stockOpname->onCreated();
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
    | HANDLER : STOCK opname PRODUCT
    */
    public function addDetail($productId)
    {
        $product = ProductRepository::find(Crypt::decrypt($productId));
        $unitDetailChoice = UnitDetailRepository::getOptions($product->unit_id);

        $this->stockOpnameDetails[] = [
            'id' => null,
            'product_id' => $productId,
            'product_text' => $product->getText(),
            "real_unit_detail_id" => $unitDetailChoice[0]['id'],
            "real_unit_detail_value" => $unitDetailChoice[0]['value'],
            "unit_detail_choice" => $unitDetailChoice,
            "quantity" => 0,

            // Validity Purposes
            "old_quantity" => 0,
        ];

        $this->refreshStock(count($this->stockOpnameDetails) - 1);
    }

    public function removeDetail($index)
    {
        if ($this->stockOpnameDetails[$index]['id']) {
            $this->stockOpnameDetailRemoves[] = $this->stockOpnameDetails[$index]['id'];
        }
        unset($this->stockOpnameDetails[$index]);
        $this->stockOpnameDetails = array_values($this->stockOpnameDetails);
    }

    public function refreshStock($index = null)
    {
        $items = [];
        if ($index) {
            $items[$index] = $this->stockOpnameDetails[$index];
        } else {
            $items = $this->stockOpnameDetails;
        }

        foreach ($items as $index => $item) {
            if ($this->isShow) {
                $this->stockOpnameDetails[$index]['is_stock_available'] = 0;
                $this->stockOpnameDetails[$index]['row_color_class'] = "";
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
                unitDetailId: Crypt::decrypt($item["real_unit_detail_id"]),
                transactionDate: $this->transactionDate,
            );

            $this->stockOpnameDetails[$index]['system_stock'] = $stockAvailablity['current_stock'];
            $this->stockOpnameDetails[$index]['system_unit_name'] = $stockAvailablity['unit_detail_name'];
            $this->stockOpnameDetails[$index]['is_stock_available'] = $stockAvailablity['is_stock_available'];
            $this->stockOpnameDetails[$index]['row_color_class'] = $qtyToBeUsed == 0 ? '' : ($stockAvailablity['is_stock_available'] ? 'table-success' : 'table-danger');
        }
    }
}
