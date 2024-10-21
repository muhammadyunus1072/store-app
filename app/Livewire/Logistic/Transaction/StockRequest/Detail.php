<?php

namespace App\Livewire\Logistic\Transaction\StockRequest;

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
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;
use App\Repositories\Logistic\Transaction\StockRequest\StockRequestRepository;
use App\Repositories\Logistic\Transaction\StockRequest\StockRequestProductRepository;


class Detail extends Component
{
    public $objId;

    public $warehouse_requester_id;
    public $warehouse_requester_text;

    public $warehouse_requested_id;
    public $warehouse_requested_text;

    #[Validate('required', message: 'Tanggal Permintaan Harus Diisi', onUpdate: false)]
    public $request_date;
    public $note;

    public $stockRequestProducts = [];
    public $stockRequestProductRemoves = [];

    public function mount()
    {

        if ($this->objId) {
            $id = Crypt::decrypt($this->objId);
            $stockRequest = StockRequestRepository::findWithDetails($id);

            $this->warehouse_requester_id = Crypt::encrypt($stockRequest->warehouse_requester_id);
            $this->warehouse_requester_text = $stockRequest->warehouse_requester_name;

            $this->warehouse_requested_id = Crypt::encrypt($stockRequest->warehouse_requested_id);
            $this->warehouse_requested_text = $stockRequest->warehouse_requested_name;
            
            $this->request_date = $stockRequest->request_date;
            $this->note = $stockRequest->note;
            
            foreach($stockRequest->stockRequestProducts as $index => $stockRequestProduct)
            {
                
                $unit_detail_choice = collect($stockRequestProduct->unitDetailChoices->toArray())->map(function($item) {
                    $item['enc_id'] = Crypt::encrypt($item['id']);
                    return $item;
                })->all();
                $unit_detail_id = collect($unit_detail_choice)->where('id', $stockRequestProduct->unit_detail_id)->pluck('enc_id')[0];
                
                $this->stockRequestProducts[] = [
                    'id' => Crypt::encrypt($stockRequestProduct->id),
                    'product_id' => Crypt::encrypt($stockRequestProduct->product_id),
                    'product_text' => $stockRequestProduct->product_name ." ( ".Product::translateType($stockRequestProduct->product_type).")",
                    "unit_detail_id" => $unit_detail_id,
                    "unit_detail_choice" => $unit_detail_choice,
                    "quantity" => NumberFormatter::valueToImask($stockRequestProduct->quantity),
                ];
            }
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

    public function removeDetail($index)
    {
        if ($this->stockRequestProducts[$index]['id']) {
            $this->stockRequestProductRemoves[] = $this->stockRequestProducts[$index]['id'];
        }
        unset($this->stockRequestProducts[$index]);
        $this->stockRequestProducts = array_values($this->stockRequestProducts);
        
    }

    public function setWarehouseRequester($data)
    {
        $data = $data['selectedOption'];
        $this->warehouse_requester_id = $data['id'];
        
    }

    public function setWarehouseRequested($data)
    {
        $data = $data['selectedOption'];
        $this->warehouse_requested_id = $data['id'];
        
    }
    public function selectProduct($data)
    {
        $data = $data['selectedOption'];
        $unit_detail_choice = UnitDetailRepository::getBy(Crypt::decrypt($data['id']));
        $unit_detail_id = collect($unit_detail_choice)->where('is_main', true)->pluck('enc_id')[0];
        $this->stockRequestProducts[] = [
            'id' => null,
            'product_id' => $data['id'],
            'product_text' => $data['text'],
            "unit_detail_id" => $unit_detail_id,
            "unit_detail_choice" => $unit_detail_choice,
            "quantity" => 0,
        ];
    }


    public function store()
    {
        if (!$this->warehouse_requester_id) {
            Alert::fail($this, "Gagal", "Peminta Gudang Belum Diinput");
            return;
        }
        if (!$this->warehouse_requester_id) {
            Alert::fail($this, "Gagal", "Permintaan Gudang Belum Diinput");
            return;
        }
        if (count($this->stockRequestProducts) == 0) {
            Alert::fail($this, "Gagal", "Data Permintaan Produk Belum Diinput");
            return;
        }
        $this->validate();

        $validatedData = [
            'warehouse_requester_id' => Crypt::decrypt($this->warehouse_requester_id),
            'warehouse_requested_id' => Crypt::decrypt($this->warehouse_requested_id),
            'request_date' => $this->request_date,
            'note' => $this->note,
        ];

        try {
            DB::beginTransaction();
            if ($this->objId) {
                $objId = Crypt::decrypt($this->objId);
                StockRequestRepository::update($objId, $validatedData);
            } else {
                $obj = StockRequestRepository::create($validatedData);
                $objId = $obj->id;
            }

            // ===============================
            // ==== STOCK REQUEST PRODUCT ====
            // ===============================
            foreach($this->stockRequestProducts as $index => $stockRequestProduct)
            {
                $validatedData = [
                    'stock_request_id' => $objId,
                    'product_id' => Crypt::decrypt($stockRequestProduct['product_id']),
                    'unit_detail_id' => Crypt::decrypt($stockRequestProduct['unit_detail_id']),
                    'quantity' => NumberFormatter::imaskToValue($stockRequestProduct['quantity']),
                ];
                if ($stockRequestProduct['id']) {
                    $stockRequestProductId = Crypt::decrypt($stockRequestProduct['id']);
                    $object = StockRequestProductRepository::update($stockRequestProductId, $validatedData);
                }else {
                    $object = StockRequestProductRepository::create($validatedData);
                    $stockRequestProductId = $object->id;   
                }
            }

            foreach ($this->stockRequestProductRemoves as $item) {
                StockRequestProductRepository::delete(Crypt::decrypt($item));
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
            DB::rollBack();
            Alert::fail($this, "Gagal", $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.logistic.transaction.stock-request.detail');
    }
}
