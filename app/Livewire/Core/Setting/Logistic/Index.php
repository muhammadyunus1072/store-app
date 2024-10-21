<?php

namespace App\Livewire\Core\Setting\Logistic;

use Exception;
use App\Models\Core\Setting\Setting;
use App\Helpers\Alert;
use App\Helpers\Logistic\Stock\StockHandler;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Repositories\Core\Setting\SettingRepository;
use App\Repositories\Finance\Master\Tax\TaxRepository;
use App\Settings\SettingLogistic;

class Index extends Component
{
    public $objId;

    public $name;
    public $setting = [];

    public $product_code;
    public $product_batch;
    public $product_substract_stock_method;
    public $product_substract_stock_method_choice = StockHandler::SUBSTRACT_STOCK_METHOD_CHOICE;
    public $product_expired_date;
    public $product_attachment;
    public $approval_key_good_receive;
    public $approval_key_stock_request;
    public $approval_key_stock_expense;
    public $tax_ppn_good_receive_id;
    public $tax_ppn_good_receive_text;

    public function mount()
    {
        $this->name = SettingLogistic::NAME;

        // Init
        foreach (SettingLogistic::ALL as $key => $value) {
            $this->setting[$key] = $value;
        }

        // Set Variables
        $setting = SettingRepository::findBy(whereClause: [['name', $this->name]]);
        if ($setting) {
            $this->objId = Crypt::encrypt($setting->id);
            $settings = json_decode($setting->setting);

            foreach ($this->setting as $key => $value) {
                $this->setting[$key] = (isset($settings->{$key})) ? $settings->{$key} : "";
            }

            // Handle : Tax ID
            if ($this->setting[SettingLogistic::TAX_PPN_GOOD_RECEIVE_ID]) {
                $tax = TaxRepository::find($this->setting[SettingLogistic::TAX_PPN_GOOD_RECEIVE_ID]);
                $this->setting[SettingLogistic::TAX_PPN_GOOD_RECEIVE_ID] = Crypt::encrypt($tax->id);
                $this->setting[SettingLogistic::TAX_PPN_GOOD_RECEIVE_ID . "_text"] = $tax->getText();
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
        $formattedSetting = $this->setting;

        // Handle : Tax ID
        if ($formattedSetting[SettingLogistic::TAX_PPN_GOOD_RECEIVE_ID]) {
            $formattedSetting[SettingLogistic::TAX_PPN_GOOD_RECEIVE_ID] = Crypt::decrypt($formattedSetting[SettingLogistic::TAX_PPN_GOOD_RECEIVE_ID]);
        }

        try {
            DB::beginTransaction();
            if ($this->objId) {
                SettingRepository::update(Crypt::decrypt($this->objId), [
                    'name' => $this->name,
                    'setting' => json_encode($this->setting),
                ]);
            } else {
                SettingRepository::create([
                    'name' => $this->name,
                    'setting' => json_encode($this->setting),
                ]);
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
        return view('livewire.core.setting.logistic.index');
    }
}
