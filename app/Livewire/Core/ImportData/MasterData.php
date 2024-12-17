<?php

namespace App\Livewire\Core\ImportData;

use Exception;
use Livewire\Component;
use App\Helpers\General\Alert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\Livewire\WithImportExcel;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Unit\UnitDetail;
use App\Repositories\Logistic\Master\Unit\UnitRepository;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;
use App\Repositories\Rsmh\GudangLog\Suplier\SuplierRepository ;
use App\Repositories\Rsmh\GudangLog\SubBagian\SubBagianRepository;
use App\Repositories\Rsmh\Sync\SyncSupplier\SyncSupplierRepository;
use App\Repositories\Rsmh\Sync\SyncWarehouse\SyncWarehouseRepository;

class MasterData extends Component
{
    use WithImportExcel;

    public $isSyncSupplier = false;
    public $isSyncWarehouse = false;

    public function mount()
    {
        $this->isSyncSupplier = SyncSupplierRepository::findBy(whereClause:[
            ['is_done', false]
        ]);
        $this->isSyncWarehouse = SyncWarehouseRepository::findBy(whereClause:[
            ['is_done', false]
        ]);
        $this->import_excel = [
            [
                "data" => null,
                "skip_rows" => 1,
                "class" => 'col-4',
                "className" => Product::class,
                "name" => "Import Master Data Produk (Rumah Tangga)",
                "format" => "formatImportMasterDataProductRumahTangga"
            ],
            [
                "data" => null,
                "skip_rows" => 3,
                "class" => 'col-4',
                "className" => Product::class,
                "name" => "Import Master Data Produk (Gizi Pasien)",
                "format" => "formatImportMasterDataProductGiziPasien"
            ],
            [
                "data" => null,
                "skip_rows" => 0,
                "class" => 'col-4',
                "className" => Product::class,
                "name" => "Import Master Data Produk (Gizi - Katering)",
                "format" => "formatImportMasterDataProductGiziKatering"
            ],
        ];
    }

    public function formatImportMasterDataProductRumahTangga()
    {
        return function ($row) {
            $unit_name = isset(UnitDetail::TRANSLATE_UNIT[strtoupper($row[2])]) ? UnitDetail::TRANSLATE_UNIT[strtoupper($row[2])] : strtoupper($row[2]);
            $product_name = $row[1];
            $product_type = Product::TYPE_PRODUCT_WITH_STOCK;
            $product_kode_simrs = $row[0];
            $product_kode_sakti = $row[3];
            if(!$product_kode_simrs)
            {
                return null;
            }
            $unit_detail = UnitDetailRepository::findBy(whereClause: [
                ['name', $unit_name]
            ]);

            if (!$unit_detail) {
                $title_unit = isset(UnitDetail::TITLE_UNIT[$unit_name]) ? UnitDetail::TITLE_UNIT[$unit_name] : $unit_name;
                $unit = UnitRepository::findBy(whereClause: [
                    ['title', $title_unit]
                ]);

                if (!$unit) {
                    $unit = UnitRepository::create([
                        'title' => $title_unit,
                    ]);
                }

                $unit_detail = UnitDetailRepository::create([
                    'unit_id' => $unit->id,
                    'is_main' => true,
                    'name' => $unit_name,
                    'value' => 1,
                ]);
            } else {
                $unit = UnitRepository::findBy(whereClause: [
                    ['id', $unit_detail->unit_id]
                ]);
            }
            $product = ProductRepository::findBy(whereClause: [
                ['kode_simrs', $product_kode_simrs]
            ]);

            if (!$product) {
                ProductRepository::create([
                    'unit_id' => $unit->id,
                    'name' => $product_name,
                    'type' => $product_type,
                    'kode_simrs' => $product_kode_simrs,
                    'kode_sakti' => $product_kode_sakti,
                ]);
            }
        };
    }

    public function formatImportMasterDataProductGiziKatering()
    {
        return function ($row) {
            $unit_name = isset(UnitDetail::TRANSLATE_UNIT[strtoupper($row[4])]) ? UnitDetail::TRANSLATE_UNIT[strtoupper($row[4])] : strtoupper($row[4]);
            $product_name = $row[3];
            $product_type = $row[0] == 'YA' ? Product::TYPE_PRODUCT_WITHOUT_STOCK : Product::TYPE_PRODUCT_WITH_STOCK;
            $product_kode_simrs = $row[1];
            $product_kode_sakti = $row[2];

            if(!$product_kode_simrs)
            {
                return null;
            }
            
            $unit_detail = UnitDetailRepository::findBy(whereClause: [
                ['name', $unit_name]
            ]);

            if (!$unit_detail) {
                $title_unit = isset(UnitDetail::TITLE_UNIT[$unit_name]) ? UnitDetail::TITLE_UNIT[$unit_name] : $unit_name;
                $unit = UnitRepository::findBy(whereClause: [
                    ['title', $title_unit]
                ]);

                if (!$unit) {
                    $unit = UnitRepository::create([
                        'title' => $title_unit,
                    ]);
                }

                $unit_detail = UnitDetailRepository::create([
                    'unit_id' => $unit->id,
                    'is_main' => true,
                    'name' => $unit_name,
                    'value' => 1,
                ]);
            } else {
                $unit = UnitRepository::findBy(whereClause: [
                    ['id', $unit_detail->unit_id]
                ]);
            }

            $product = ProductRepository::findBy(whereClause: [
                ['kode_simrs', $product_kode_simrs]
            ]);

            if (!$product) {
                Log::info("NEW $product_kode_simrs");
                ProductRepository::create([
                    'unit_id' => $unit->id,
                    'name' => $product_name,
                    'type' => $product_type,
                    'kode_simrs' => $product_kode_simrs,
                    'kode_sakti' => $product_kode_sakti,
                ]);
            }else{
                Log::info("OLD $product_kode_simrs");
            }
        };
    }

    public function formatImportMasterDataProductGiziPasien()
    {
        return function ($row) {
            $unit_name = isset(UnitDetail::TRANSLATE_UNIT[strtoupper($row[4])]) ? UnitDetail::TRANSLATE_UNIT[strtoupper($row[4])] : strtoupper($row[4]);
            $product_name = $row[3];
            $product_type = $row[2] == 'YA' ? Product::TYPE_PRODUCT_WITHOUT_STOCK : Product::TYPE_PRODUCT_WITH_STOCK;
            $product_kode_simrs = $row[0];
            $product_kode_sakti = $row[1];

            if(!$product_kode_simrs)
            {
                return null;
            }
            $unit_detail = UnitDetailRepository::findBy(whereClause: [
                ['name', $unit_name]
            ]);

            if (!$unit_detail) {
                $title_unit = isset(UnitDetail::TITLE_UNIT[$unit_name]) ? UnitDetail::TITLE_UNIT[$unit_name] : $unit_name;
                $unit = UnitRepository::findBy(whereClause: [
                    ['title', $title_unit]
                ]);

                if (!$unit) {
                    $unit = UnitRepository::create([
                        'title' => $title_unit,
                    ]);
                }

                $unit_detail = UnitDetailRepository::create([
                    'unit_id' => $unit->id,
                    'is_main' => true,
                    'name' => $unit_name,
                    'value' => 1,
                ]);
            } else {
                $unit = UnitRepository::findBy(whereClause: [
                    ['id', $unit_detail->unit_id]
                ]);
            }

            $product = ProductRepository::findBy(whereClause: [
                ['kode_simrs', $product_kode_simrs]
            ]);

            if (!$product) {
                ProductRepository::create([
                    'unit_id' => $unit->id,
                    'name' => $product_name,
                    'type' => $product_type,
                    'kode_simrs' => $product_kode_simrs,
                    'kode_sakti' => $product_kode_sakti,
                ]);
            }
        };
    }

    public function syncWarehouse()
    {
        try {
            DB::beginTransaction();
            
            $countSubBagian = SubBagianRepository::count();

            $validatedData = [
                'total' => $countSubBagian,
            ];

            $obj = SyncWarehouseRepository::create($validatedData);
            $this->isSyncWarehouse = $obj;
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

    public function syncSupplier()
    {
        try {
            DB::beginTransaction();
            
            $countSupplier = SuplierRepository::count();

            $validatedData = [
                'total' => $countSupplier,
            ];

            $obj = SyncSupplierRepository::create($validatedData);
            $this->isSyncSupplier = $obj;
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
        return view('livewire.core.import-data.master-data');
    }
}

