<?php

namespace App\Livewire\Core\Setting\Purchasing;

use App\Helpers\General\Alert;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Repositories\Core\Setting\SettingRepository;
use App\Repositories\Finance\Master\Tax\TaxRepository;
use App\Settings\SettingPurchasing;

class Index extends Component
{
    public $objId;

    public $name;
    public $setting = [];

    public function mount()
    {
        $this->name = SettingPurchasing::NAME;

        // Init
        foreach (SettingPurchasing::ALL as $key => $value) {
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
            if ($this->setting[SettingPurchasing::TAX_PPN_ID]) {
                $tax = TaxRepository::find($this->setting[SettingPurchasing::TAX_PPN_ID]);
                $this->setting[SettingPurchasing::TAX_PPN_ID] = Crypt::encrypt($tax->id);
                $this->setting[SettingPurchasing::TAX_PPN_ID . "_text"] = $tax->getText();
            }
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        $this->redirectRoute('setting_purchasing.index');
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('setting_purchasing.index');
    }

    public function store()
    {
        $formattedSetting = $this->setting;

        // Handle : Tax ID
        if ($formattedSetting[SettingPurchasing::TAX_PPN_ID]) {
            $formattedSetting[SettingPurchasing::TAX_PPN_ID] = Crypt::decrypt($formattedSetting[SettingPurchasing::TAX_PPN_ID]);
        }

        try {
            DB::beginTransaction();
            if ($this->objId) {
                SettingRepository::update(Crypt::decrypt($this->objId), [
                    'name' => $this->name,
                    'setting' => json_encode($formattedSetting),
                ]);
            } else {
                SettingRepository::create([
                    'name' => $this->name,
                    'setting' => json_encode($formattedSetting),
                ]);
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
            DB::rollBack();
            Alert::fail($this, "Gagal", $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.core.setting.purchasing.index');
    }
}
