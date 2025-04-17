<?php

namespace App\Livewire\Logistic\Transaction\StockRequest;

use App\Helpers\Core\UserStateHandler;
use Exception;
use App\Helpers\General\Alert;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Helpers\General\NumberFormatter;
use App\Helpers\Logistic\Stock\StockHandler;
use App\Models\Logistic\Master\DisplayRack\DisplayRack;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Models\Logistic\Transaction\StockRequest\StockRequestProduct;
use App\Repositories\Logistic\Master\DisplayRack\DisplayRackRepository;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;
use App\Repositories\Logistic\Transaction\StockRequest\StockRequestRepository;
use App\Repositories\Logistic\Transaction\StockRequest\StockRequestProductRepository;
use App\Settings\SettingCore;
use Carbon\Carbon;

class Detail extends Component
{
    public $objId;
    public $newObjId;
    public $isShow;

    public $number;
    #[Validate('required', message: 'Tanggal Harus Diisi', onUpdate: false)]
    public $transactionDate;
    public $note;

    public $destinationCompanyId;
    public $destinationCompanyText;
    public $destinationWarehouseId;
    public $destinationWarehouseText;
    public $destinationDisplayRackId;
    public $destinationDisplayRackText;

    public $sourceCompanyId;
    public $sourceCompanyText;
    public $sourceWarehouseId;
    public $sourceWarehouseText;

    public $stockRequestProducts = [];
    public $stockRequestProductRemoves = [];

    // Helpers
    public $isMultipleCompany = false;
    public $destinationCompanies = [];
    public $destinationWarehouses = [];
    public $destinationDisplayRacks = [];

    // Edit Validity Purposes
    public $oldSourceCompanyId;
    public $oldSourceWarehouseId;
    public $oldDestinationCompanyId;
    public $oldDestinationWarehouseId;
    public $oldTransactionDate;

    public function render()
    {
        return view('livewire.logistic.transaction.stock-request.detail');
    }

    public function mount()
    {
        $this->loadSetting();
        $this->loadUserState();

        $this->transactionDate = Carbon::now()->format("Y-m-d");
        $this->note = "";

        if ($this->objId) {
            $stockRequest = StockRequestRepository::find(Crypt::decrypt($this->objId));
            $this->number = $stockRequest->number;
            $this->transactionDate = Carbon::parse($stockRequest->transaction_date)->format("Y-m-d");
            $this->note = $stockRequest->note;

            $this->destinationCompanyId = Crypt::encrypt($stockRequest->destination_company_id);
            $this->destinationCompanyText = $stockRequest->destination_company_name;
            $this->destinationWarehouseId = Crypt::encrypt($stockRequest->destination_warehouse_id);
            $this->destinationWarehouseText = $stockRequest->destination_warehouse_name;

            $this->sourceCompanyId = Crypt::encrypt($stockRequest->source_company_id);
            $this->sourceCompanyText = $stockRequest->source_company_name;
            $this->sourceWarehouseId = Crypt::encrypt($stockRequest->source_warehouse_id);
            $this->sourceWarehouseText = $stockRequest->source_warehouse_name;

            $this->oldSourceCompanyId = $this->sourceCompanyId;
            $this->oldSourceWarehouseId = $this->sourceWarehouseId;
            $this->oldDestinationCompanyId = $this->destinationCompanyId;
            $this->oldDestinationWarehouseId = $this->destinationWarehouseId;
            $this->oldTransactionDate = $this->transactionDate;

            foreach ($stockRequest->stockRequestProducts as $stockRequestProduct) {
                $unitDetailChoice = UnitDetailRepository::getOptions($stockRequestProduct->unit_detail_unit_id);
                $unitDetailId = collect($unitDetailChoice)->filter(function ($obj) use ($stockRequestProduct) {
                    return Crypt::decrypt($obj['id']) == $stockRequestProduct->unit_detail_id;
                })->first()['id'];

                $this->stockRequestProducts[] = [
                    'id' => Crypt::encrypt($stockRequestProduct->id),
                    'product_id' => Crypt::encrypt($stockRequestProduct->product_id),
                    'product_text' => $stockRequestProduct->getText(),
                    "unit_detail_id" => $unitDetailId,
                    "unit_detail_choice" => $unitDetailChoice,
                    "quantity" => NumberFormatter::valueToImask($stockRequestProduct->quantity),

                    // Validity Purposes
                    "old_quantity" => $stockRequestProduct->quantity,
                    "old_source_company_id" => $this->sourceCompanyId,
                    "old_source_warehouse_id" => $this->sourceWarehouseId,
                    "old_destination_company_id" => $this->destinationCompanyId,
                    "old_destination_warehouse_id" => $this->destinationWarehouseId,
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
            $this->destinationCompanies = $userState['companies'];
            $this->destinationCompanyId = $userState['company_id'];
            $this->destinationWarehouses = $userState['warehouses'];
            $this->destinationWarehouseId = $userState['warehouse_id'];
        } else {
            $this->destinationCompanyId = $userState['company_id'];
            $this->sourceCompanyId = $userState['company_id'];
            $this->destinationWarehouses = $userState['warehouses'];
            $this->destinationWarehouseId = $userState['warehouse_id'];
            $this->destinationDisplayRacks = DisplayRackRepository::all()->map(function ($item) {
                return [
                    'id' => Crypt::encrypt($item->id),
                    'name' => $item->name,
                ];
            })->toArray();
            $this->destinationDisplayRackId = $this->destinationDisplayRacks[0]['id'];
        }
    }

    public function updated($property)
    {
        if (
            $property == 'sourceCompanyId'
            || $property == 'sourceWarehouseId'
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
            $this->redirectRoute('stock_request.edit', $this->objId);
        } else {
            $this->redirectRoute('stock_request.show', $this->newObjId);
        }
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('stock_request.index');
    }

    public function store()
    {
        if (!$this->destinationCompanyId) {
            Alert::fail($this, "Gagal", "Perusahaan Peminta Belum Diinput");
            return;
        }
        if (!$this->destinationDisplayRackId) {
            Alert::fail($this, "Gagal", "Gudang Peminta Belum Diinput");
            return;
        }
        if (!$this->sourceCompanyId) {
            Alert::fail($this, "Gagal", "Perusahaan Diminta Belum Diinput");
            return;
        }
        if (!$this->sourceWarehouseId) {
            Alert::fail($this, "Gagal", "Gudang Diminta Belum Diinput");
            return;
        }
        if (count($this->stockRequestProducts) == 0) {
            Alert::fail($this, "Gagal", "Barang-barang diminta belum diinput");
            return;
        }

        // Check Stock
        $this->refreshStock();
        foreach ($this->stockRequestProducts as $item) {
            if (!$item['is_stock_available']) {
                Alert::fail($this, "Gagal", "Stok {$item['product_text']} Tidak Mencukupi");
                return;
            }
        }

        $this->validate();

        $validatedData = [
            'destination_company_id' => Crypt::decrypt($this->destinationCompanyId),
            'source_company_id' => Crypt::decrypt($this->sourceCompanyId),
            'destination_location_id' => Crypt::decrypt($this->destinationDisplayRackId),
            'destination_location_type' => DisplayRack::class,
            'source_warehouse_id' => Crypt::decrypt($this->sourceWarehouseId),
            'transaction_date' => $this->transactionDate,
            'note' => $this->note,
        ];

        try {
            DB::beginTransaction();
            if ($this->objId) {
                $decId = Crypt::decrypt($this->objId);
                StockRequestRepository::update($decId, $validatedData);
                $stockRequest = StockRequestRepository::find($decId);
            } else {
                $stockRequest = StockRequestRepository::create($validatedData);
                $this->newObjId = Crypt::encrypt($stockRequest->id);
            }

            // ===============================
            // ==== STOCK REQUEST PRODUCT ====
            // ===============================
            foreach ($this->stockRequestProducts as $stockRequestProduct) {
                $validatedData = [
                    'stock_request_id' => $stockRequest->id,
                    'product_id' => Crypt::decrypt($stockRequestProduct['product_id']),
                    'unit_detail_id' => Crypt::decrypt($stockRequestProduct['unit_detail_id']),
                    'quantity' => NumberFormatter::imaskToValue($stockRequestProduct['quantity']),
                ];
                if ($stockRequestProduct['id']) {
                    $stockRequestProductId = Crypt::decrypt($stockRequestProduct['id']);
                    $object = StockRequestProductRepository::update($stockRequestProductId, $validatedData);
                } else {
                    $object = StockRequestProductRepository::create($validatedData);
                    $stockRequestProductId = $object->id;
                }
            }

            foreach ($this->stockRequestProductRemoves as $item) {
                StockRequestProductRepository::delete(Crypt::decrypt($item));
            }

            if ($this->objId) {
                $stockRequest->onUpdated();
            } else {
                $stockRequest->onCreated();
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
        } catch (Exception $e) {
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

        $this->stockRequestProducts[] = [
            'id' => null,
            'product_id' => Crypt::encrypt($product->id),
            'product_text' => $product->getText(),
            "unit_detail_id" => $unitDetailChoice[0]['id'],
            "unit_detail_choice" => $unitDetailChoice,
            "quantity" => 0,

            // Validity Purposes
            "old_quantity" => 0,
        ];

        $this->refreshStock(count($this->stockRequestProducts) - 1);
    }

    public function removeDetail($index)
    {
        if ($this->stockRequestProducts[$index]['id']) {
            $this->stockRequestProductRemoves[] = $this->stockRequestProducts[$index]['id'];
        }
        unset($this->stockRequestProducts[$index]);
        $this->stockRequestProducts = array_values($this->stockRequestProducts);
    }

    public function refreshStock($index = null)
    {
        $items = [];
        if ($index) {
            $items[$index] = $this->stockRequestProducts[$index];
        } else {
            $items = $this->stockRequestProducts;
        }

        foreach ($items as $index => $item) {
            if ($this->isShow) {
                $this->stockRequestProducts[$index]['current_stock'] = 0;
                $this->stockRequestProducts[$index]['current_stock_unit_name'] = 0;
                $this->stockRequestProducts[$index]['is_stock_available'] = 0;
                $this->stockRequestProducts[$index]['row_color_class'] = "";
                continue;
            }

            if (
                $this->oldSourceCompanyId == $this->sourceCompanyId
                && $this->oldSourceWarehouseId == $this->sourceWarehouseId
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
                companyId: Crypt::decrypt($this->sourceCompanyId),
                warehouseId: Crypt::decrypt($this->sourceWarehouseId),
                qtyToBeUsed: $qtyToBeUsed,
                unitDetailId: Crypt::decrypt($item["unit_detail_id"]),
                transactionDate: $this->transactionDate,
            );

            $this->stockRequestProducts[$index]['current_stock'] = $stockAvailablity['current_stock'];
            $this->stockRequestProducts[$index]['current_stock_unit_name'] = $stockAvailablity['unit_detail_name'];
            $this->stockRequestProducts[$index]['is_stock_available'] = $stockAvailablity['is_stock_available'];
            $this->stockRequestProducts[$index]['row_color_class'] = $qtyToBeUsed == 0 ? '' : ($stockAvailablity['is_stock_available'] ? 'table-success' : 'table-danger');
        }
    }
}
