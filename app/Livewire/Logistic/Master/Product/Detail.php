<?php

namespace App\Livewire\Logistic\Master\Product;

use Exception;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use App\Helpers\General\Alert;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Settings\InterkoneksiSaktiSetting;
use App\Models\Logistic\Master\Product\Product;
use App\Repositories\Logistic\Master\Unit\UnitRepository;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;
use App\Repositories\Logistic\Master\Product\ProductUnitRepository;
use App\Repositories\Logistic\Master\Product\ProductCategoryRepository;
use App\Repositories\Rsmh\Sakti\InterkoneksiSaktiCoa\InterkoneksiSaktiCoaRepository;
use App\Repositories\Rsmh\Sakti\InterkoneksiSaktiKbki\InterkoneksiSaktiKbkiRepository;

class Detail extends Component
{
    public $objId;

    public $isShow = false;

    #[Validate('required', message: 'Nama Produk Harus Diisi', onUpdate: false)]
    public $name;

    #[Validate('required', message: 'Tipe Produk Harus Diisi', onUpdate: false)]
    public $type;
    public $type_choice;

    #[Validate('required', message: 'Satuan Harus Diisi', onUpdate: false)]
    public $unit_id;
    public $old_unit_id;
    public $unit_title;

    public $min_stock = 0;
    public $max_stock = 0;

    public $productUnits = [];
    public $oldProductUnits = [];

    public $category_products = [];

    public function mount()
    {
        $this->type_choice = Product::TYPE_CHOICE;
        if ($this->objId) {
            $id = Crypt::decrypt($this->objId);
            $product = ProductRepository::findWithDetails($id);

            $this->name = $product->name;
            $this->type = $product->type;
            $this->min_stock = valueToImask($product->min_stock);
            $this->max_stock = valueToImask($product->max_stock);
            
            $this->old_unit_id = Crypt::encrypt($product->unit_id);
            $this->unit_id = Crypt::encrypt($product->unit_id);
            $this->unit_title = $product->unit->title;

            
            foreach ($product->productCategories as $product_category) {
                $this->category_products[] = [
                    'id' => Crypt::encrypt($product_category->categoryProduct->id),
                    'text' => $product_category->categoryProduct->name,
                ];
            }
            $productUnits = $product->productUnits;

            $mainUnitDetail = collect($product->unit->unitDetails)->where('is_main', true)->first();
            foreach($productUnits as $index => $product_unit)
            {
                $this->productUnits[] = [
                    'id' => Crypt::encrypt($product_unit->id),
                    'unit_detail_id' => Crypt::encrypt($product_unit->unit_detail_id),
                    'text' => $product_unit->unitDetail->is_main ? $product_unit->unitDetail->name.' (Utama)' : $product_unit->unitDetail->name. " / ".number_format($product_unit->unitDetail->value)." {$mainUnitDetail['name']}", 
                    'selling_price' => valueToImask($product_unit->selling_price),
                    'code' => $product_unit->code,
                ];
            }
            $this->oldProductUnits = $this->productUnits;
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

    public function updatedUnitId($value)
    {
        $unitDetails = UnitRepository::findWithDetails(Crypt::decrypt($value));

        $mainUnitDetail = collect($unitDetails->unitDetails)->where('is_main', true)->first();
        $this->productUnits = [];
        foreach($unitDetails->unitDetails as $index => $unit_detail)
        {
            $old = collect($this->oldProductUnits)
            ->first(function ($item) use ($unit_detail) {
                return decrypt($item['unit_detail_id']) == $unit_detail->id;
            });
            $this->productUnits[] = [
                'id' => $old ? $old['id'] : null,
                'unit_detail_id' => Crypt::encrypt($unit_detail->id),
                'text' => $unit_detail->is_main ? $unit_detail->name. ' (Utama)' : $unit_detail->name. " / ".number_format($unit_detail->value)." {$mainUnitDetail['name']}", 
                'selling_price' => $old ? $old['selling_price'] : 0,
                'code' => '',
            ];
        }
    }

    public function store()
    {
        $this->validate();
        // consoleLog($this, $this->productUnits);
        // return;
        $validatedData = [
            'name' => $this->name,
            'unit_id' => Crypt::decrypt($this->unit_id),
            'type' => $this->type,
            'plu' => Str::random(10),
            'min_stock' => imaskToValue($this->min_stock),
            'max_stock' => imaskToValue($this->max_stock),
        ];

        try {
            DB::beginTransaction();
            if ($this->objId) {
                $objId = Crypt::decrypt($this->objId);
                ProductRepository::update($objId, $validatedData);
                if(Crypt::decrypt($this->old_unit_id) != Crypt::decrypt($this->unit_id)) {
                    ProductUnitRepository::deleteOldUnit($objId);
                }
            } else {
                $obj = ProductRepository::create($validatedData);
                $objId = $obj->id;
            }

            foreach ($this->productUnits as $product_setting) {
                
                $validatedData = [
                    'product_id' => $objId,
                    'unit_id' => Crypt::decrypt($this->unit_id),
                    'unit_detail_id' => Crypt::decrypt($product_setting['unit_detail_id']),
                    'selling_price' => imaskToValue($product_setting['selling_price']),
                    'code' => $product_setting['code'],
                ];
                if($product_setting['id']) {
                    if($product_setting['code'])
                    {
                        $isDuplicateBarcode = ProductUnitRepository::findBy([
                            ['code', $product_setting['code']],
                            ['id', '!=', Crypt::decrypt($product_setting['id'])]
                        ]);
                        if($isDuplicateBarcode)
                        {
                            throw new Exception("Barcode Sudah Terdaftar", 500);
                        }
                    }
                    ProductUnitRepository::update(Crypt::decrypt($product_setting['id']), $validatedData);
                }else{
                    if($product_setting['code'])
                    {
                        $isDuplicateBarcode = ProductUnitRepository::findBy([
                            ['code', $product_setting['code']],
                        ]);
                        if($isDuplicateBarcode)
                        {
                            throw new Exception("Barcode Sudah Terdaftar", 500);
                        }
                    }
                    ProductUnitRepository::create($validatedData);
                }
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
