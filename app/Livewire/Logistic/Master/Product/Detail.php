<?php

namespace App\Livewire\Logistic\Master\Product;

use Exception;
use App\Helpers\General\Alert;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Models\Logistic\Master\Product\Product;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Product\ProductCategoryRepository;


class Detail extends Component
{
    public $objId;

    #[Validate('required', message: 'Nama Produk Harus Diisi', onUpdate: false)]
    public $name;

    #[Validate('required', message: 'Tipe Produk Harus Diisi', onUpdate: false)]
    public $type;
    public $type_choice;

    #[Validate('required', message: 'Satuan Harus Diisi', onUpdate: false)]
    public $unit_id;
    public $unit_title;

    public $kode_simrs;
    public $kode_sakti;

    public $category_products = [];

    public function mount()
    {
        $this->type_choice = Product::TYPE_CHOICE;
        if ($this->objId) {
            $id = Crypt::decrypt($this->objId);
            $product = ProductRepository::findWithDetails($id);

            $this->name = $product->name;
            $this->type = $product->type;
            $this->kode_simrs = $product->kode_simrs;
            $this->kode_sakti = $product->kode_sakti;
            $this->unit_id = Crypt::encrypt($product->unit_id);
            $this->unit_title = $product->unit->title;

            foreach ($product->productCategories as $product_category) {
                $this->category_products[] = [
                    'id' => Crypt::encrypt($product_category->categoryProduct->id),
                    'text' => $product_category->categoryProduct->name,
                ];
            }
        } else {
            $this->type = Product::TYPE_PRODUCT_WITH_STOCK;
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            $this->redirectRoute('product.edit', $this->objId);
        } else {
            $this->redirectRoute('product.create');
        }
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('product.index');
    }

    public function selectCategoryProduct($data)
    {
        $this->category_products[] = [
            'id' => $data['id'],
            'text' => $data['text'],
        ];
    }

    public function unselectCategoryProduct($data)
    {
        $index = array_search($data['id'], array_column($this->category_products, 'id'));
        if ($index !== false) {
            unset($this->category_products[$index]);
        }
    }

    public function store()
    {
        $this->validate();

        $validatedData = [
            'name' => $this->name,
            'kode_simrs' => $this->kode_simrs,
            'kode_sakti' => $this->kode_sakti,
            'unit_id' => Crypt::decrypt($this->unit_id),
            'type' => $this->type,
        ];

        try {
            DB::beginTransaction();
            if ($this->objId) {
                $objId = Crypt::decrypt($this->objId);
                ProductRepository::update($objId, $validatedData);
            } else {
                $obj = ProductRepository::create($validatedData);
                $objId = $obj->id;
            }

            foreach ($this->category_products as $category_product) {
                ProductCategoryRepository::createIfNotExist([
                    'product_id' => $objId,
                    'category_product_id' => Crypt::decrypt($category_product['id']),
                ]);
            }
            
            ProductCategoryRepository::deleteExcept($objId, array_map(function ($item) {
                return Crypt::decrypt($item['id']);
            }, $this->category_products));

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
        return view('livewire.logistic.master.product.detail');
    }
}
