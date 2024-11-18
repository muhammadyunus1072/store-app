<?php

namespace App\Livewire\Logistic\Master\Warehouse;

use Exception;
use App\Helpers\General\Alert;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use App\Helpers\General\NumberFormatter;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\Logistic\Master\Warehouse\WarehouseRepository;


class Detail extends Component
{
    public $objId;

    #[Validate('required', message: 'Nama Kategori Harus Diisi', onUpdate: false)]
    public $name;

    public function mount()
    {
        if ($this->objId) {
            $id = Crypt::decrypt($this->objId);
            $warehouse = WarehouseRepository::find($id);

            $this->name = $warehouse->name;
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            $this->redirectRoute('warehouse.edit', $this->objId);
        } else {
            $this->redirectRoute('warehouse.create');
        }
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('warehouse.index');
    }

    public function store()
    {
        $this->validate();

        $validatedData = [
            'name' => $this->name,
        ];

        try {
            DB::beginTransaction();
            if ($this->objId) {
                $objId = Crypt::decrypt($this->objId);
                WarehouseRepository::update($objId, $validatedData);
            } else {
                $obj = WarehouseRepository::create($validatedData);
                $objId = $obj->id;
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
        return view('livewire.logistic.master.warehouse.detail');
    }
}
