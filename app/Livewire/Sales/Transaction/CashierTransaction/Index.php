<?php

namespace App\Livewire\Sales\Transaction\CashierTransaction;

use Exception;
use App\Helpers\General\Alert;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use App\Helpers\General\NumberFormatter;
use Livewire\Attributes\Validate;
use App\Models\Finance\Master\Tax;
use App\Models\Sales\Transaction\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\Finance\Master\Tax\TaxRepository;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Sales\Transaction\CashierTransactionRepository;
use App\Repositories\Sales\Transaction\TransactionRepository;

class Index extends Component
{
    public $objId;

    public $transactionDetails = [];
    public $last_item = [];
    public $grand_total = 0;
    public $input;

    #[Validate('required', message: 'Uang Tunai Harus Diisi', onUpdate: false)]
    public $cash = 0;


    public function mount()
    {
        
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            $this->redirectRoute('cashier_transaction.edit', $this->objId);
        } else {
            $this->redirectRoute('cashier_transaction.create');
        }
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('cashier_transaction.index');
    }

    public function addProduct($data)
    {
        
        $product = CashierTransactionRepository::findProductByInput(false, Crypt::decrypt($data['id']));
        
        if($product)
        {
            $this->handleAddProduct($product);
        }
    }
    
    public function removeProduct($index)
    {
        $product = $this->transactionDetails[$index];
        $this->setLastItem($product['name'], $product['unit_name'], $product['unit_value'], $product['unit_selling_price'] );
        unset($this->transactionDetails[$index]);
        consoleLog($this, $this->transactionDetails);
        $this->transactionDetails = $this->transactionDetails;
        $this->calculateGrandTotal();
    }
    
    public function updatedInput($code)
    {
        if($code)
        {
            $qty = 1;
            if (strpos($code, '*') !== false) {
                $input = explode('*', $code);
                $qty = $input[0];
                $code = $input[1];
            }
            $product = CashierTransactionRepository::findProductByInput($code, false);
            
            if($product)
            {
                $this->handleAddProduct($product, $qty);
            }
        }
    }

    private function setLastItem($name, $unit_name, $unit_value, $unit_selling_price)
    {
        $this->last_item = [
            'name' => $name,
            'qty' => 1,
            'unit_name' => $unit_name,
            'unit_value' => $unit_value,
            'unit_selling_price' => $unit_selling_price,
        ];
    }

    private function handleAddProduct($product, $qty = 1)
    {
        $key = collect($this->transactionDetails)
        ->where('product_id', '=', $product['product_id'])
        ->keys()
        ->first();
        if(!is_null($key))
        {
            $this->transactionDetails[$key]['qty'] += $qty;
            $this->input = '';
            return;
        }

        $unit_detail_ids = explode(';', $product['unit_detail_ids']);
        $product_unit_ids = explode(';', $product['product_unit_ids']);
        $unit_is_mains = explode(';', $product['unit_is_mains']);
        $unit_names = explode(';', $product['unit_names']);
        $unit_values = explode(';', $product['unit_values']);
        $unit_selling_prices = explode(';', $product['unit_selling_prices']);
        $main_unit = [];
        $unit_choices = [];
        foreach($unit_is_mains as $index => $item)
        {
            $unit_detail = [
                'is_main' => $item == '1',
                'unit_detail_id' => Crypt::encrypt($unit_detail_ids[$index]), 
                'product_unit_id' => Crypt::encrypt($product_unit_ids[$index]), 
                'unit_name' => $unit_names[$index], 
                'unit_value' => $unit_values[$index], 
                'unit_selling_price' => $unit_selling_prices[$index], 5, 
            ];
            if($item == '1')
            {
                $main_unit = $unit_detail;
            }

            $unit_choices[$unit_detail_ids[$index]] = $unit_detail;
        }

        $this->setLastItem($product['name'], $main_unit['unit_name'], $main_unit['unit_value'], $main_unit['unit_selling_price'] );

        $this->transactionDetails[] = array_merge($main_unit, [
            'key' => Str::random(20),
            'plu' => $product['product_plu'],
            'qty' => $qty,
            'product_id' => Crypt::encrypt($product['product_id']),
            'name' => $product['name'],
            'price' => $main_unit['unit_selling_price'],
            'subtotal' => $main_unit['unit_selling_price'],
            'unit_detail_id' => $main_unit['unit_detail_id'],
            'choice' => $unit_choices,
            'main_unit' => $main_unit
        ]);

        $this->input = '';
        $this->calculateGrandTotal();
        return;
    }

    public function calculateGrandTotal()
    {
        $this->grand_total = collect($this->transactionDetails)->sum(function ($product) {
            $price = $product['choice'][$product['unit_detail_id']]['unit_selling_price'] ?? 0;
            return $product['qty'] * $price;
        });
        consoleLog($this, $this->grand_total);
    }

    public function handleAddPayment($value)
    {
        $this->cash = valueToImask(imaskToValue($this->cash) + $value);
    }

    public function handleExactPayment()
    {
        $this->cash = valueToImask($this->grand_total);
    }
    public function handleDeletePayment()
    {
        $this->cash = 0;
    }
    public function handleSavePayment()
    {
        Alert::fail($this, "Gagal", 'Printer Belum Terhubung');
    }
    

    public function handleSavePayment1()
    {
        $this->validate();

        consoleLog($this, $this->transactionDetails);
        return;
        $validatedData = [
            'cashier_shift_id' => 1,
            'status' => Transaction::STATUS_PAID,
            'subtotal' => $this->grand_total,
            'discount' => 0,
            'admin_fee' => 0,
            'grand_total' => $this->grand_total,
            'paid_amount' => imaskToValue($this->cash),
            'change_amount' => $this->grand_total - imaskToValue($this->cash),
            'payment_method_id' => 1
        ];

        try {
            DB::beginTransaction();
            if ($this->objId) {
                $objId = Crypt::decrypt($this->objId);
                TransactionRepository::update($objId, $validatedData);
            } else {
                $obj = TransactionRepository::create($validatedData);
                $objId = $obj->id;
            }

            foreach($this->transactionDetails as $transaction_detail)
            {
                $validatedData = [
                    'transaction_id' => $objId,
                    'product_id' => Crypt::decrypt($transaction_detail['product_id']),
                    'product_unit_id' => Crypt::decrypt($transaction_detail['product_unit_id']),
                ];
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
        return view('livewire.sales.transaction.cashier-transaction.index');
    }
}
