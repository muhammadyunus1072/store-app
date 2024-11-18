<?php

namespace App\Livewire\Purchasing\Master\Supplier;

use Exception;
use App\Helpers\General\Alert;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\Purchasing\Master\Supplier\SupplierRepository;
use App\Repositories\Purchasing\Master\Supplier\SupplierCategoryRepository;


class Detail extends Component
{
    public $objId;

    #[Validate('required', message: 'Nama Supplier Harus Diisi', onUpdate: false)]
    public $name;

    public $supplierCategories = [];

    public function mount()
    {
        if ($this->objId) {
            $id = Crypt::decrypt($this->objId);
            $supplier = SupplierRepository::findWithDetails($id);

            $this->name = $supplier->name;

            foreach ($supplier->supplierCategories as $supplier_category) {
                $this->supplierCategories[] = [
                    'id' => Crypt::encrypt($supplier_category->category_supplier_id),
                    'text' => $supplier_category->category_supplier->name,
                ];
            }
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            $this->redirectRoute('supplier.edit', $this->objId);
        } else {
            $this->redirectRoute('supplier.create');
        }
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('supplier.index');
    }

    public function selectCategorySupplier($data)
    {
        $this->supplierCategories[] = [
            'id' => $data['id'],
            'text' => $data['text'],
        ];
    }

    public function unselectCategorySupplier($data)
    {
        $index = array_search($data['id'], array_column($this->supplierCategories, 'id'));
        if ($index !== false) {
            unset($this->supplierCategories[$index]);
        }
    }

    public function store()
    {
        if (count($this->supplierCategories) == 0) {
            Alert::fail($this, "Gagal", "Kategori Supplier Belum Diinput");
            return;
        }
        $this->validate();

        $validatedData = [
            'name' => $this->name,
        ];

        try {
            DB::beginTransaction();
            if ($this->objId) {
                $objId = Crypt::decrypt($this->objId);
                SupplierRepository::update($objId, $validatedData);
            } else {
                $obj = SupplierRepository::create($validatedData);
                $objId = $obj->id;
            }

            foreach ($this->supplierCategories as $supplier_category) {
                SupplierCategoryRepository::createIfNotExist([
                    'supplier_id' => $objId,
                    'category_supplier_id' => Crypt::decrypt($supplier_category['id']),
                ]);
            }

            SupplierCategoryRepository::deleteExcept($objId, array_map(function ($item) {
                return Crypt::decrypt($item['id']);
            }, $this->supplierCategories));

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
        return view('livewire.purchasing.master.supplier.detail');
    }
}
