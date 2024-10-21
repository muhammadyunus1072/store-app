<?php

namespace App\Livewire\Logistic\Master\CategoryProduct;

use Exception;
use App\Helpers\General\Alert;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use App\Helpers\General\NumberFormatter;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Repositories\Logistic\Master\CategoryProduct\CategoryProductRepository;


class Detail extends Component
{
    public $objId;

    #[Validate('required', message: 'Nama Kategori Harus Diisi', onUpdate: false)]
    public $name;

    public function mount()
    {
        if ($this->objId) {
            $id = Crypt::decrypt($this->objId);
            $category_product = CategoryProductRepository::find($id);

            $this->name = $category_product->name;
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            $this->redirectRoute('category_product.edit', $this->objId);
        } else {
            $this->redirectRoute('category_product.create');
        }
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('category_product.index');
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
                CategoryProductRepository::update($objId, $validatedData);
            } else {
                $obj = CategoryProductRepository::create($validatedData);
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
        return view('livewire.logistic.master.category-product.detail');
    }
}
