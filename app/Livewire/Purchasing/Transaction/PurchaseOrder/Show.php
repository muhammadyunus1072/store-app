<?php

namespace App\Livewire\Purchasing\Transaction\PurchaseOrder;

use Exception;
use App\Helpers\General\Alert;
use Livewire\Component;
use Illuminate\Support\Str;
use App\Helpers\General\NumberFormatter;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Models\Logistic\Master\Product\Product;
use App\Repositories\Document\Transaction\ApprovalRepository;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;
use App\Repositories\Document\Transaction\ApprovalUserRepository;
use App\Repositories\Purchasing\Transaction\PurchaseRequest\PurchaseRequestRepository;
use App\Repositories\Purchasing\Transaction\PurchaseRequest\PurchaseRequestProductRepository;


class Show extends Component
{
    public $objId;
    public $approvalId;
    public $userId;
    public $position;

    public $is_enabled;

    #[Validate('required', message: 'Tanggal Permintaan Supplier Harus Diisi', onUpdate: false)]
    public $request_date;
    public $note;

    // Approval
    public $approval_note;
    #[Validate('required', message: 'Penentu Berurutan Harus Diisi', onUpdate: false)]
    public $is_sequentially = false;
    public $approvalUsers = [];
    public $approval_user_removes = [];

    public $purchaseRequestProducts = [];
    public $purchase_request_product_removes = [];

    public function mount()
    {
        if ($this->approvalId) {
            $approvalId = Crypt::decrypt($this->approvalId);
            $approval = ApprovalRepository::findWithDetails($approvalId);
            $purchase_request = PurchaseRequestRepository::findWithDetails($approval->remarks_id);
            
            $this->objId = Crypt::encrypt($purchase_request->id);
            $this->request_date = $purchase_request->request_date;
            $this->note = $purchase_request->note;

            foreach($purchase_request->purchaseRequestProducts as $purchase_request_product)
            {
                $unit_detail_choice = collect($purchase_request_product->unitDetailChoices->toArray())->map(function($item) {
                    $item['enc_id'] = Crypt::encrypt($item['id']);
                    return $item;
                })->all();
                $unit_detail_id = collect($unit_detail_choice)->where('id', $purchase_request_product->unit_detail_id)->pluck('enc_id')[0];
                $this->purchaseRequestProducts[] = [
                    'id' => Crypt::encrypt($purchase_request_product->id),
                    'product_id' => Crypt::encrypt($purchase_request_product->product_id),
                    'product_text' => $purchase_request_product->product_name ." ( ".Product::translateType($purchase_request_product->product_type).")",
                    'key' => Str::random(30),
                    "unit_detail_id" => Crypt::encrypt($unit_detail_id),
                    "unit_detail_choice" => $unit_detail_choice,
                    "quantity" => NumberFormatter::valueToImask($purchase_request_product->quantity),
                ];
            }
            $approval = $purchase_request->approval()->first();
            
            $this->approval_note = $approval->note;
            $this->is_sequentially = $approval->is_sequentially;

            foreach($approval->approvalUsers as $approval_user)
            {
                $this->approvalUsers[] = [
                    'id' => Crypt::encrypt($approval_user->id),
                    'user_id' => Crypt::encrypt($approval_user->user_id),
                    'user_text' => $approval_user->user->name,
                    'key' => Str::random(30),
                    "position" => NumberFormatter::valueToImask($approval_user->position),
                ];
            }

            $this->is_enabled = $this->position == 1;
        }
    }

    public function removeDetail($index)
    {
        if ($this->purchaseRequestProducts[$index]['id']) {
            $this->purchase_request_product_removes[] = $this->purchaseRequestProducts[$index]['id'];
        }

        unset($this->purchaseRequestProducts[$index]);
    }

    public function selectProduct($data)
    {
        $data = $data['selectedOption'];
        $unit_detail_choice = UnitDetailRepository::getBy(Crypt::decrypt($data['id']));
        $unit_detail_id = collect($unit_detail_choice)->where('is_main', true)->pluck('enc_id')[0];
        $this->purchaseRequestProducts[] = [
            'id' => null,
            'product_id' => $data['id'],
            'product_text' => $data['text'],
            'key' => Str::random(30),
            "unit_detail_id" => $unit_detail_id,
            "unit_detail_choice" => $unit_detail_choice,
            "quantity" => 0,
        ];
    }


    public function removeApprover($index)
    {
        if ($this->approvalUsers[$index]['id']) {
            $this->approval_user_removes[] = $this->approvalUsers[$index]['id'];
        }

        unset($this->approvalUsers[$index]);
    }

    public function selectUser($data)
    {
        $data = $data['selectedOption'];
        $exists = collect($this->approvalUsers)->contains('user_id', $data['id']);

        if (!$exists) {
            $this->approvalUsers[] = [
                'id' => null,
                'user_id' => $data['id'],
                'user_text' => $data['text'],
                'key' => Str::random(30),
                "position" => 0,
            ];
        }
    }

    public function store()
    {

        if (count($this->purchaseRequestProducts) == 0) {
            Alert::fail($this, "Gagal", "Data Permintaan Produk Belum Diinput");
            return;
        }
        $this->validate();

        $validatedData = [
            'request_date' => $this->request_date,
            'note' => $this->note,
        ];

        try {
            DB::beginTransaction();
            if ($this->objId) {
                $objId = Crypt::decrypt($this->objId);
                PurchaseRequestRepository::update($objId, $validatedData);
            } else {
                $obj = PurchaseRequestRepository::create($validatedData);
                $objId = $obj->id;
            }

            foreach($this->purchaseRequestProducts as $index => $purchase_request_product)
            {
                $validatedData = [
                    'purchase_request_id' => $objId,
                    'product_id' => Crypt::decrypt($purchase_request_product['product_id']),
                    'unit_detail_id' => Crypt::decrypt($purchase_request_product['unit_detail_id']),
                    'quantity' => NumberFormatter::imaskToValue($purchase_request_product['quantity']),
                ];
                if ($purchase_request_product['id']) {
                    $object = PurchaseRequestProductRepository::update(Crypt::decrypt($purchase_request_product['id']), $validatedData);
                }else {
                    $object = PurchaseRequestProductRepository::create($validatedData);
                }
            }

            foreach ($this->purchase_request_product_removes as $item) {
                PurchaseRequestProductRepository::delete(Crypt::decrypt($item));
            }

            // Approval
            $validatedData = [
                'note' => $this->approval_note,
                'is_sequentially' => $this->is_sequentially,
            ];
            if ($this->objId) {
                $approvalId = PurchaseRequestRepository::find($objId)->approval()->first()->id;
                ApprovalRepository::update($approvalId, $validatedData);
            } 

            foreach($this->approvalUsers as $index => $approval_user)
            {
                $validatedData = [
                    'approval_id' => $approvalId,
                    'user_id' => Crypt::decrypt($approval_user['user_id']),
                    'position' => NumberFormatter::imaskToValue($approval_user['position']),
                ];

                if (!$approval_user['id']) {
                    ApprovalUserRepository::create($validatedData);
                }else{
                    ApprovalUserRepository::update(Crypt::decrypt($approval_user['id']), $validatedData);
                }
            }

            foreach ($this->approval_user_removes as $item) {
                ApprovalUserRepository::delete(Crypt::decrypt($item));
            }
            DB::commit();

            Alert::success($this, "Berhasil", "Data Berhasil Diperbarui");
            $this->dispatch('refreshApproval');
        } catch (Exception $e) {
            DB::rollBack();
            Alert::fail($this, "Gagal", $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.logistic.transaction.good-receive.show');
    }
}
