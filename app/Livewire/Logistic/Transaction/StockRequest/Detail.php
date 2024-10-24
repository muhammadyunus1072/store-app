<?php

namespace App\Livewire\Logistic\Transaction\StockRequest;

use App\Helpers\Core\UserStateHandler;
use Exception;
use App\Helpers\General\Alert;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use App\Helpers\General\NumberFormatter;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Transaction\StockRequest\StockRequestProduct;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;
use App\Repositories\Logistic\Transaction\StockRequest\StockRequestRepository;
use App\Repositories\Logistic\Transaction\StockRequest\StockRequestProductRepository;
use App\Settings\SettingCore;
use Carbon\Carbon;

class Detail extends Component
{
    public $objId;

    #[Validate('required', message: 'Tanggal Harus Diisi', onUpdate: false)]
    public $transactionDate;
    public $note;

    public $requesterCompanyId;
    public $requesterCompanyText;
    public $requesterWarehouseId;
    public $requesterWarehouseText;

    public $requestedCompanyId;
    public $requestedCompanyText;
    public $requestedWarehouseId;
    public $requestedWarehouseText;

    public $stockRequestProducts = [];
    public $stockRequestProductRemoves = [];

    // Helpers
    public $isMultipleCompany = false;
    public $requesterCompanies = [];
    public $requesterWarehouses = [];
    
    public $historyRemarksIds = []; // History Datatable
    public $historyRemarksType = StockRequestProduct::class; // History Datatable

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
            $this->transactionDate = Carbon::parse($stockRequest->transaction_date)->format("Y-m-d");
            $this->note = $stockRequest->note;

            $this->requesterCompanyId = Crypt::encrypt($stockRequest->company_requester_id);
            $this->requesterCompanyText = $stockRequest->company_requester_name;
            $this->requesterWarehouseId = Crypt::encrypt($stockRequest->warehouse_requester_id);
            $this->requesterWarehouseText = $stockRequest->warehouse_requester_name;

            $this->requestedCompanyId = Crypt::encrypt($stockRequest->company_requested_id);
            $this->requestedCompanyText = $stockRequest->company_requested_name;
            $this->requestedWarehouseId = Crypt::encrypt($stockRequest->warehouse_requested_id);
            $this->requestedWarehouseText = $stockRequest->warehouse_requested_name;

            foreach ($stockRequest->stockRequestProducts as $stockRequestProduct) {
                $unitDetailChoice = UnitDetailRepository::getOptions($stockRequestProduct->unit_detail_unit_id);
                $unitDetailId = collect($unitDetailChoice)->filter(function ($obj) use ($stockRequestProduct) {
                    return Crypt::decrypt($obj['id']) == $stockRequestProduct->unit_detail_id;
                })->first()['id'];

                $this->stockRequestProducts[] = [
                    'id' => Crypt::encrypt($stockRequestProduct->id),
                    'product_id' => Crypt::encrypt($stockRequestProduct->product_id),
                    'product_text' => $stockRequestProduct->product_name,
                    "unit_detail_id" => $unitDetailId,
                    "unit_detail_choice" => $unitDetailChoice,
                    "quantity" => NumberFormatter::valueToImask($stockRequestProduct->quantity),
                ];

                // History Datatable
                $this->historyRemarksIds[] = $stockRequestProduct->id;
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
            $this->requesterCompanies = $userState['companies'];
            $this->requesterCompanyId = $userState['company_id'];
            $this->requesterWarehouses = $userState['warehouses'];
            $this->requesterWarehouseId = $userState['warehouse_id'];
        } else {
            $this->requesterCompanyId = $userState['company_id'];
            $this->requestedCompanyId = $userState['company_id'];
            $this->requesterWarehouses = $userState['warehouses'];
            $this->requesterWarehouseId = $userState['warehouse_id'];
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            $this->redirectRoute('stock_request.edit', $this->objId);
        } else {
            $this->redirectRoute('stock_request.create');
        }
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('stock_request.index');
    }

    public function store()
    {
        if (!$this->requesterCompanyId) {
            Alert::fail($this, "Gagal", "Perusahaan Peminta Belum Diinput");
            return;
        }
        if (!$this->requesterWarehouseId) {
            Alert::fail($this, "Gagal", "Gudang Peminta Belum Diinput");
            return;
        }
        if (!$this->requestedCompanyId) {
            Alert::fail($this, "Gagal", "Perusahaan Diminta Belum Diinput");
            return;
        }
        if (!$this->requestedWarehouseId) {
            Alert::fail($this, "Gagal", "Gudang Diminta Belum Diinput");
            return;
        }
        if (Crypt::decrypt($this->requesterWarehouseId) == Crypt::decrypt($this->requestedWarehouseId)) {
            Alert::fail($this, "Gagal", "Gudang Peminta dan Diminta Tidak Boleh Sama");
            return;
        }
        if (count($this->stockRequestProducts) == 0) {
            Alert::fail($this, "Gagal", "Barang-barang diminta belum diinput");
            return;
        }

        $this->validate();

        $validatedData = [
            'company_requester_id' => Crypt::decrypt($this->requesterCompanyId),
            'company_requested_id' => Crypt::decrypt($this->requestedCompanyId),
            'warehouse_requester_id' => Crypt::decrypt($this->requesterWarehouseId),
            'warehouse_requested_id' => Crypt::decrypt($this->requestedWarehouseId),
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

            // if ($this->objId) {
            //     $stockRequest->onUpdated();
            // } else {
            //     $stockRequest->onCreated();
            // }

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
            'product_text' => $product->name,
            "unit_detail_id" => $unitDetailChoice[0]['id'],
            "unit_detail_choice" => $unitDetailChoice,
            "quantity" => 0,
        ];
    }

    public function removeDetail($index)
    {
        if ($this->stockRequestProducts[$index]['id']) {
            $this->stockRequestProductRemoves[] = $this->stockRequestProducts[$index]['id'];
        }
        unset($this->stockRequestProducts[$index]);
        $this->stockRequestProducts = array_values($this->stockRequestProducts);
    }
}
