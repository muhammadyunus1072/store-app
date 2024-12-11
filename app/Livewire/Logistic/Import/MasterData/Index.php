<?php

namespace App\Livewire\Logistic\Import\MasterData;

use Exception;
use Livewire\Component;
use App\Helpers\General\Alert;
use App\Helpers\General\ImportDataHelper;
use Illuminate\Support\Facades\DB;
use App\Traits\Livewire\WithImportExcel;
use App\Models\Logistic\Master\Product\Product;
use App\Repositories\Logistic\Master\Unit\UnitRepository;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Rsmh\GudangLog\SubBagian\SubBagianRepository;
use App\Repositories\Rsmh\GudangLog\Suplier\SuplierRepository;
use App\Repositories\Rsmh\Sync\SyncSupplier\SyncSupplierRepository;
use App\Repositories\Rsmh\Sync\SyncWarehouse\SyncWarehouseRepository;

class Index extends Component
{
    use WithImportExcel;

    public $syncSupplier = false;
    public $syncWarehouse = false;

    public function render()
    {
        return view('livewire.logistic.import.master-data.index');
    }

    public function mount()
    {
        $this->import_excel = [
            [
                "data" => null,
                "skip_rows" => 1,
                "class" => 'col-4',
                "name" => "Import Master Data Produk (Rumah Tangga)",
                "onImport" => "onImportRumahTangga"
            ],
            [
                "data" => null,
                "skip_rows" => 3,
                "class" => 'col-4',
                "name" => "Import Master Data Produk (Gizi Pasien)",
                "onImport" => "onImportGiziPasien"
            ],
            [
                "data" => null,
                "skip_rows" => 1,
                "class" => 'col-4',
                "name" => "Import Master Data Produk (Gizi - Katering)",
                "onImport" => "onImportGiziKatering"
            ],
        ];

        $this->syncSupplier = SyncSupplierRepository::findBy(whereClause: [['is_done', false]]);
    }

    /*
    | IMPORT: RUMAH TANGGA
    */
    public function onImportRumahTangga($row)
    {
        $product_kode_simrs = $row[0];
        $product_name = $row[1];
        $unit_name = isset(ImportDataHelper::TRANSLATE_UNIT[strtoupper($row[2])]) ? ImportDataHelper::TRANSLATE_UNIT[strtoupper($row[2])] : strtoupper($row[2]);
        $product_kode_sakti = $row[3];

        $product = ProductRepository::findBy(whereClause: [['kode_simrs', $product_kode_simrs]]);
        if (empty($product)) {
            $unit = UnitRepository::createByUnitDetailName($unit_name);
            ProductRepository::create([
                'unit_id' => $unit->id,
                'name' => $product_name,
                'type' => Product::TYPE_PRODUCT_WITH_STOCK,
                'kode_simrs' => $product_kode_simrs,
                'kode_sakti' => $product_kode_sakti,
            ]);
        }
    }

    /*
    | IMPORT: GIZI - KATERING
    */
    public function onImportGiziKatering($row)
    {
        $product_type = $row[0] == 'YA' ? Product::TYPE_PRODUCT_WITHOUT_STOCK : Product::TYPE_PRODUCT_WITH_STOCK;
        $product_kode_simrs = $row[1];
        $product_kode_sakti = $row[2];
        $product_name = $row[3];
        $unit_name = isset(ImportDataHelper::TRANSLATE_UNIT[strtoupper($row[4])]) ? ImportDataHelper::TRANSLATE_UNIT[strtoupper($row[4])] : strtoupper($row[4]);

        $product = ProductRepository::findBy(whereClause: [['kode_simrs', $product_kode_simrs]]);
        if (empty($product)) {
            $unit = UnitRepository::createByUnitDetailName($unit_name);
            ProductRepository::create([
                'unit_id' => $unit->id,
                'name' => $product_name,
                'type' => $product_type,
                'kode_simrs' => $product_kode_simrs,
                'kode_sakti' => $product_kode_sakti,
            ]);
        }
    }

    /*
    | IMPORT: GIZI - PASIEN
    */
    public function onImportGiziPasien($row)
    {
        $product_kode_simrs = $row[0];
        $product_kode_sakti = $row[1];
        $product_type = $row[2] == 'YA' ? Product::TYPE_PRODUCT_WITHOUT_STOCK : Product::TYPE_PRODUCT_WITH_STOCK;
        $product_name = $row[3];
        $unit_name = isset(ImportDataHelper::TRANSLATE_UNIT[strtoupper($row[4])]) ? ImportDataHelper::TRANSLATE_UNIT[strtoupper($row[4])] : strtoupper($row[4]);

        $product = ProductRepository::findBy(whereClause: [['kode_simrs', $product_kode_simrs]]);
        if (empty($product)) {
            $unit = UnitRepository::createByUnitDetailName($unit_name);
            ProductRepository::create([
                'unit_id' => $unit->id,
                'name' => $product_name,
                'type' => $product_type,
                'kode_simrs' => $product_kode_simrs,
                'kode_sakti' => $product_kode_sakti,
            ]);
        }
    }

    /*
    | SYNCHRONIZE: SUPPLIER
    */
    public function syncDataSupplier()
    {
        try {
            DB::beginTransaction();
            $this->syncSupplier = SyncSupplierRepository::create([
                'total' => SuplierRepository::count(),
            ]);
            DB::commit();

            Alert::confirmation(
                $this,
                Alert::ICON_SUCCESS,
                "Berhasil",
                "Proses Sinkronisasi Berhasil Dijalankan, Silahkan Tunggu Hingga Selesai",
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

    /*
    | SYNCHRONIZE: WAREHOUSE
    */
    public function syncWarehouse()
    {
        try {
            DB::beginTransaction();
            $this->syncWarehouse = SyncWarehouseRepository::create([
                'total' => SubBagianRepository::count(),
            ]);
            DB::commit();

            Alert::confirmation(
                $this,
                Alert::ICON_SUCCESS,
                "Berhasil",
                "Proses Sinkronisasi Berhasil Dijalankan, Silahkan Tunggu Hingga Selesai",
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
}
