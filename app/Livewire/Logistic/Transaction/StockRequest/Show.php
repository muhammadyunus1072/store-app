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
use App\Repositories\Document\Transaction\ApprovalRepository;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;
use App\Repositories\Logistic\Transaction\StockRequest\StockRequestRepository;
use App\Repositories\Logistic\Transaction\StockRequest\StockRequestProductRepository;


class Show extends Component
{
    public $objId;
    public $approvalId;

    public $destination_warehouse_id;
    public $destination_warehouse_text;

    public $source_warehouse_id;
    public $source_warehouse_text;

    #[Validate('required', message: 'Tanggal Permintaan Harus Diisi', onUpdate: false)]
    public $transaction_date;
    public $note;

    public $stockRequestProducts = [];
    public $stockRequestProductRemoves = [];

    public function mount()
    {

        if ($this->approvalId) {

            $approvalId = Crypt::decrypt($this->approvalId);
            $approval = ApprovalRepository::findWithDetails($approvalId);
            $stockRequest = StockRequestRepository::findWithDetails($approval->remarks_id);
            
            $this->objId = Crypt::encrypt($stockRequest->id);
            $id = Crypt::decrypt($this->objId);

            $this->destination_warehouse_id = Crypt::encrypt($stockRequest->destination_warehouse_id);
            $this->destination_warehouse_text = $stockRequest->destination_warehouse_name;

            $this->source_warehouse_id = Crypt::encrypt($stockRequest->source_warehouse_id);
            $this->source_warehouse_text = $stockRequest->source_warehouse_name;
            
            $this->transaction_date = $stockRequest->transaction_date;
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
            $this->redirectRoute('approval.edit', $this->approvalId);
        } else {
            $this->redirectRoute('approval.edit', $this->approvalId);
        }
    }
    
    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('approval.edit', $this->approvalId);
    }

    public function removeDetail($index)
    {
        if ($this->stockRequestProducts[$index]['id']) {
            $this->stockRequestProductRemoves[] = $this->stockRequestProducts[$index]['id'];
        }
        unset($this->stockRequestProducts[$index]);
        $this->stockRequestProducts = array_values($this->stockRequestProducts);
        
    }

    public function setWarehouseDestination($data)
    {
        $data = $data['selectedOption'];
        $this->destination_warehouse_id = $data['id'];
        
    }

    public function setWarehouseSource($data)
    {
        $data = $data['selectedOption'];
        $this->source_warehouse_id = $data['id'];
        
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
        if (!$this->destination_warehouse_id) {
            Alert::fail($this, "Gagal", "Peminta Gudang Belum Diinput");
            return;
        }
        if (!$this->destination_warehouse_id) {
            Alert::fail($this, "Gagal", "Permintaan Gudang Belum Diinput");
            return;
        }
        if (count($this->stockRequestProducts) == 0) {
            Alert::fail($this, "Gagal", "Data Permintaan Produk Belum Diinput");
            return;
        }
        $this->validate();

        $validatedData = [
            'destination_warehouse_id' => Crypt::decrypt($this->destination_warehouse_id),
            'source_warehouse_id' => Crypt::decrypt($this->source_warehouse_id),
            'transaction_date' => $this->transaction_date,
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
        return view('livewire.logistic.transaction.stock-request.show');
    }
}
