<?php

namespace App\Livewire\Logistic\Transaction\StockExpense;

use Exception;
use App\Helpers\Alert;
use App\Helpers\Core\UserStateHelper;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use App\Helpers\NumberFormatter;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Models\Logistic\Master\Product\Product;
use App\Repositories\Core\Company\CompanyRepository;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;
use App\Repositories\Logistic\Master\Warehouse\WarehouseRepository;
use App\Repositories\Logistic\Transaction\StockExpense\StockExpenseRepository;
use App\Repositories\Logistic\Transaction\StockExpense\StockExpenseProductRepository;
use Carbon\Carbon;

class Detail extends Component
{
    public $objId;

    #[Validate('required', message: 'Tanggal Pengeluaran Harus Diisi', onUpdate: false)]
    public $expense_date;
    public $note;

    public $company_id;
    public $company_text;

    public $warehouse_id;
    public $warehouse_text;

    public $stockExpenseProducts = [];
    public $stockExpenseProductRemoves = [];

    public function render()
    {
        return view('livewire.logistic.transaction.stock-expense.detail');
    }

    public function mount()
    {
        $this->expense_date = Carbon::now()->format("Y-m-d");

        $userState = UserStateHelper::get();
        if ($userState['company_id']) {
            $company = CompanyRepository::find($userState['company_id']);
            $this->company_id = Crypt::encrypt($company->id);
            $this->company_text = $company->name;
        }
        if ($userState['warehouse_id']) {
            $warehouse = WarehouseRepository::find($userState['warehouse_id']);
            $this->warehouse_id = Crypt::encrypt($warehouse->id);
            $this->warehouse_text = $warehouse->name;
        }

        if ($this->objId) {
            $id = Crypt::decrypt($this->objId);
            $stockExpense = StockExpenseRepository::findWithDetails($id);

            $this->company_id = Crypt::encrypt($stockExpense->company_id);
            $this->company_text = $stockExpense->company_name;

            $this->warehouse_id = Crypt::encrypt($stockExpense->warehouse_id);
            $this->warehouse_text = $stockExpense->warehouse_name;

            $this->expense_date = Carbon::parse($stockExpense->expense_date)->format("Y-m-d");
            $this->note = $stockExpense->note;

            foreach ($stockExpense->stockExpenseProducts as $stockExpenseProduct) {
                $unit_detail_choice = UnitDetailRepository::getOptions($stockExpenseProduct->unit_detail_unit_id);
                $unit_detail_id = collect($unit_detail_choice)->filter(function ($obj) use ($stockExpenseProduct) {
                    return Crypt::decrypt($obj['id']) == $stockExpenseProduct->unit_detail_id;
                })->first()['id'];

                $this->stockExpenseProducts[] = [
                    'id' => Crypt::encrypt($stockExpenseProduct->id),
                    'product_id' => Crypt::encrypt($stockExpenseProduct->product_id),
                    'product_text' => $stockExpenseProduct->product_name . " ( " . Product::translateType($stockExpenseProduct->product_type) . ")",
                    "unit_detail_id" => $unit_detail_id,
                    "unit_detail_choice" => $unit_detail_choice,
                    "quantity" => NumberFormatter::valueToImask($stockExpenseProduct->quantity),
                ];
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
        if (!$this->company_id) {
            Alert::fail($this, "Gagal", "Perusahaan Belum Diinput");
            return;
        }
        if (!$this->warehouse_id) {
            Alert::fail($this, "Gagal", "Gudang Belum Diinput");
            return;
        }
        if (count($this->stockExpenseProducts) == 0) {
            Alert::fail($this, "Gagal", "Data Pengeluaran Produk Belum Diinput");
            return;
        }

        $this->validate();

        $validatedData = [
            'company_id' => Crypt::decrypt($this->company_id),
            'warehouse_id' => Crypt::decrypt($this->warehouse_id),
            'expense_date' => $this->expense_date,
            'note' => $this->note,
        ];

        try {
            DB::beginTransaction();
            if ($this->objId) {
                $objId = Crypt::decrypt($this->objId);
                StockExpenseRepository::update($objId, $validatedData);
            } else {
                $obj = StockExpenseRepository::create($validatedData);
                $objId = $obj->id;
            }

            // ===============================
            // ==== STOCK EXPENSE PRODUCT ====
            // ===============================
            foreach ($this->stockExpenseProducts as $index => $stockExpenseProduct) {
                $validatedData = [
                    'stock_expense_id' => $objId,
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
        $unit_detail_choice = UnitDetailRepository::getOptions($product->unit_id);

        $this->stockExpenseProducts[] = [
            'id' => null,
            'product_id' => Crypt::encrypt($product->id),
            'product_text' => $product->name,
            "unit_detail_id" => $unit_detail_choice[0]['id'],
            "unit_detail_choice" => $unit_detail_choice,
            "quantity" => 0,
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
