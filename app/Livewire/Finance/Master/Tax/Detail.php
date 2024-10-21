<?php

namespace App\Livewire\Finance\Master\Tax;

use Exception;
use App\Helpers\General\Alert;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use App\Helpers\General\NumberFormatter;
use Livewire\Attributes\Validate;
use App\Models\Finance\Master\Tax;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\Finance\Master\Tax\TaxRepository;


class Detail extends Component
{
    public $objId;

    #[Validate('required', message: 'Nama Pajak Harus Diisi', onUpdate: false)]
    public $name;

    #[Validate('required', message: 'Tipe Pajak Harus Diisi', onUpdate: false)]
    public $type;
    public $type_choice = [];

    #[Validate('required', message: 'Nilai Persen Pajak Harus Diisi', onUpdate: false)]
    public $value;

    #[Validate('required', message: 'Status Pajak Harus Diisi', onUpdate: false)]
    public $is_active = true;

    public function mount()
    {
        $this->type_choice = Tax::TYPE_CHOICE;
        $this->type = Tax::TYPE_PPN;
        if ($this->objId) {
            $id = Crypt::decrypt($this->objId);
            $tax = TaxRepository::find($id);

            $this->name = $tax->name;
            $this->type = $tax->type;
            $this->value = NumberFormatter::valueToImask($tax->value);
            $this->is_active = $tax->is_active;
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            $this->redirectRoute('tax.edit', $this->objId);
        } else {
            $this->redirectRoute('tax.create');
        }
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('tax.index');
    }

    public function store()
    {
        $this->validate();

        $validatedData = [
            'name' => $this->name,
            'type' => $this->type,
            'value' => NumberFormatter::imaskToValue($this->value),
            'is_active' => $this->is_active,
        ];

        try {
            DB::beginTransaction();
            if ($this->objId) {
                $objId = Crypt::decrypt($this->objId);
                TaxRepository::update($objId, $validatedData);
            } else {
                $obj = TaxRepository::create($validatedData);
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
        return view('livewire.finance.master.tax.detail');
    }
}
