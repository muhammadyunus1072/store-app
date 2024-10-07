<?php

namespace App\Livewire\Core\Setting\Logistic;

use Exception;
use App\Models\Core\Setting\Setting;
use App\Helpers\Alert;
use App\Helpers\Logistic\StockHelper;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Repositories\Core\Setting\SettingRepository;
use App\Repositories\Finance\Master\Tax\TaxRepository;

class Detail extends Component
{
    public $objId;

    public $name;
    public $product_code;
    public $product_batch;
    public $product_substract_stock_method;
    public $product_substract_stock_method_choice = StockHelper::SUBSTRACT_STOCK_METHOD_CHOICE;
    public $product_expired_date;
    public $product_attachment;
    public $approval_key_stock_request;
    public $approval_key_stock_expense;
    public $tax_ppn_good_receive_id;
    public $tax_ppn_good_receive_text;

    public function mount()
    {
        $this->name = Setting::NAME_LOGISTIC;
        $this->product_substract_stock_method = StockHelper::SUBSTRACT_STOCK_METHOD_FIFO;

        if ($this->objId) {
            $setting = SettingRepository::find(Crypt::decrypt($this->objId));
            $this->name = $setting->name;

            $settings = json_decode($setting->setting);
            $this->product_code = $settings->product_code;
            $this->product_batch = $settings->product_batch;
            $this->product_substract_stock_method = $settings->product_substract_stock_method;
            $this->product_expired_date = $settings->product_expired_date;
            $this->product_attachment = $settings->product_attachment;
            $this->approval_key_stock_request = (isset($settings->approval_key_stock_request)) ? $settings->approval_key_stock_request : null;
            $this->approval_key_stock_expense = (isset($settings->approval_key_stock_expense)) ? $settings->approval_key_stock_expense : null;

            if ($settings->tax_ppn_good_receive_id) {
                $tax = TaxRepository::find($settings->tax_ppn_good_receive_id);
                $this->tax_ppn_good_receive_id = Crypt::encrypt($tax->id);
                $this->tax_ppn_good_receive_text = $tax->getText();
            }
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        $this->redirectRoute('setting_logistic.index');
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('setting_logistic.index');
    }

    public function store()
    {
        $validatedData = [
            'name' => $this->name,
            'setting' => json_encode(
                [
                    'product_code' => $this->product_code,
                    'product_batch' => $this->product_batch,
                    'product_substract_stock_method' => $this->product_substract_stock_method,
                    'product_expired_date' => $this->product_expired_date,
                    'product_attachment' => $this->product_attachment,
                    'approval_key_stock_request' => $this->approval_key_stock_request,
                    'approval_key_stock_expense' => $this->approval_key_stock_expense,
                    'tax_ppn_good_receive_id' => $this->tax_ppn_good_receive_id ? Crypt::decrypt($this->tax_ppn_good_receive_id) : "",
                ]
            ),
        ];

        try {
            DB::beginTransaction();
            if ($this->objId) {
                $objId = Crypt::decrypt($this->objId);
                SettingRepository::update($objId, $validatedData);
            } else {
                $obj = SettingRepository::create($validatedData);
                $objId = $obj->id;
            }

            DB::commit();

            Alert::confirmation(
                $this,
                Alert::ICON_SUCCESS,
                "Berhasil",
                "Akses Berhasil Diperbarui",
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
        return view('livewire.core.setting.logistic.detail');
    }
}
