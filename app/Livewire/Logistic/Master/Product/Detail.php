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
use App\Repositories\Rsmh\Sakti\InterkoneksiSaktiCoa\InterkoneksiSaktiCoaRepository;
use App\Repositories\Rsmh\Sakti\InterkoneksiSaktiKbki\InterkoneksiSaktiKbkiRepository;
use App\Settings\InterkoneksiSaktiSetting;

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

    public $interkoneksi_sakti_persentase_tkdn;
    public $interkoneksi_sakti_kategori_pdn;
    public $interkoneksi_sakti_kbki_id_choice = [];
    public $interkoneksi_sakti_kbki_id;
    public $interkoneksi_sakti_coa_id_choice = [];
    public $interkoneksi_sakti_coa_id;

    public $category_products = [];

    public function mount()
    {
        $this->loadInterkoneksiSaktiSetting();

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

            $this->interkoneksi_sakti_persentase_tkdn = $product->interkoneksi_sakti_persentase_tkdn;
            $this->interkoneksi_sakti_kategori_pdn = $product->interkoneksi_sakti_kategori_pdn;
            $this->interkoneksi_sakti_kbki_id = $product->interkoneksi_sakti_kbki_id;
            $this->interkoneksi_sakti_coa_id = $product->interkoneksi_sakti_coa_id;

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

    public function loadInterkoneksiSaktiSetting()
    {
        $this->interkoneksi_sakti_persentase_tkdn = InterkoneksiSaktiSetting::get(InterkoneksiSaktiSetting::BARANG_PERSENTASE_TKDN);
        $this->interkoneksi_sakti_kategori_pdn = InterkoneksiSaktiSetting::get(InterkoneksiSaktiSetting::BARANG_KATEGORI_PDN);
        $this->interkoneksi_sakti_kbki_id = InterkoneksiSaktiSetting::get(InterkoneksiSaktiSetting::BARANG_INTERKONEKSI_SAKTI_KBKI_ID);
        $this->interkoneksi_sakti_coa_id = InterkoneksiSaktiSetting::get(InterkoneksiSaktiSetting::HEADER_INTERKONEKSI_SAKTI_COA_12_ID);

        $this->interkoneksi_sakti_kbki_id_choice = InterkoneksiSaktiKbkiRepository::all()->pluck('nama', 'id');
        $this->interkoneksi_sakti_coa_id_choice = InterkoneksiSaktiCoaRepository::all()->pluck('kode', 'id');
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
            'interkoneksi_sakti_persentase_tkdn' => $this->interkoneksi_sakti_persentase_tkdn,
            'interkoneksi_sakti_kategori_pdn' => $this->interkoneksi_sakti_kategori_pdn,
            'interkoneksi_sakti_kbki_id' => $this->interkoneksi_sakti_kbki_id,
            'interkoneksi_sakti_coa_id' => $this->interkoneksi_sakti_coa_id,
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
