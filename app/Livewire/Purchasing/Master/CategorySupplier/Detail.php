<?php

namespace App\Livewire\Purchasing\Master\CategorySupplier;

use Exception;
use App\Helpers\General\Alert;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\Purchasing\Master\CategorySupplier\CategorySupplierRepository;


class Detail extends Component
{
    public $objId;

    #[Validate('required', message: 'Nama Kategori Harus Diisi', onUpdate: false)]
    public $name;

    public function mount()
    {
        if ($this->objId) {
            $id = Crypt::decrypt($this->objId);
            $category_supplier = CategorySupplierRepository::find($id);

            $this->name = $category_supplier->name;
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            $this->redirectRoute('category_supplier.edit', $this->objId);
        } else {
            $this->redirectRoute('category_supplier.create');
        }
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('category_supplier.index');
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
                CategorySupplierRepository::update($objId, $validatedData);
            } else {
                $obj = CategorySupplierRepository::create($validatedData);
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
        return view('livewire.purchasing.master.category-supplier.detail');
    }
}
