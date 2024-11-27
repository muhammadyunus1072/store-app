<?php

namespace App\Livewire\Core\ImportData;

use Livewire\Component;
use App\Traits\Livewire\WithImportExcel;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Master\Unit\UnitDetail;
use App\Repositories\Logistic\Master\Unit\UnitRepository;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;

class MasterData extends Component
{
    use WithImportExcel;

    const TRANSLATE_UNIT = [
        'BUAH',
        'KOTAK',
        'LEMBAR',
        'ROLL',
        'BOTOL',
        'PAK' => 'PACK',
        'BUNGKUS',
        'RIM',
        'TUBE',
        'SET',
        'BOX',
        'UNIT',
        'KALENG',
        'KG',
        'PASANG',
        'SACHET',
        'JERIGEN',
        'LITER',
        'BUKU',
        'BATANG',
        'KEPING',
        'METER',
        'LUSIN',
        'PCS',
        'DUS',
        'TABUNG',
        'PACK',
        'PAKET',
        'METER PERSE' => 'METER PERSEGI',
        'BALL',
        'DOOS',
        'KARUNG',
        'SAK',
        'DRUM',
        'LS',
        'PAIL',
        'GALON',
        'STEL',
        'POTONG',
        'EXEMPLAR',
        'BAG',
        'LUNI',
        'IKAT',
        'HURUF',
        'KUBIK',
        'ZAK',
        'METER CUBIC',
        'HIACE',
        'YARD',
        'CAN',
        'EMBER',
        'DUZ',
        'GULUNG',
        'KANTONG',
        'BKS' => 'BUNGKUS',
        'BTR' => 'BUTIR',
        'BH' => 'BUAH',
        'SISIR',
        'KTK' => 'KOTAK',
        'IKT' => 'IKAT',
        'BIJI',
        'KLG' => 'KALENG',
        'BTL' => 'BOTOL',
        'PCH',
        'GLN' => 'GALON',
        'CUP',
        'SCT' => 'SACHET',
        'SCH' => 'SACHET',
        'LBR' => 'LEMBAR',
        'GLG',
        'PC',
        'BKH',
        'PSG' => 'PASANG',
    ];

    const TITLE_UNIT = [
        'BUAH',
        'KOTAK',
        'LEMBAR',
        'ROLL',
        'BOTOL',
        'PACK',
        'BUNGKUS',
        'RIM',
        'TUBE',
        'SET',
        'BOX',
        'UNIT',
        'KALENG',
        'KG' => 'BERAT',
        'PASANG',
        'SACHET',
        'JERIGEN',
        'LITER',
        'BUKU',
        'BATANG',
        'KEPING',
        'METER' => 'PANJANG',
        'LUSIN',
        'PCS',
        'DUS',
        'TABUNG',
        'PAKET',
        'METER PERSEGI',
        'BALL',
        'DOOS',
        'KARUNG',
        'SAK',
        'DRUM',
        'LS',
        'PAIL',
        'GALON',
        'STEL',
        'POTONG',
        'EXEMPLAR',
        'BAG',
        'LUNI',
        'IKAT',
        'HURUF',
        'KUBIK',
        'ZAK',
        'METER CUBIC',
        'HIACE',
        'YARD',
        'CAN',
        'EMBER',
        'DUZ',
        'GULUNG',
        'KANTONG',
        'BUTIR',
        'SISIR',
        'BIJI',
        'PCH',
        'CUP',
        'GLG',
        'PC',
        'BKH',
    ];

    public function mount()
    {
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
                "skip_rows" => 1,
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
            $unit_name = isset(self::TRANSLATE_UNIT[strtoupper($row[2])]) ? self::TRANSLATE_UNIT[strtoupper($row[2])] : strtoupper($row[2]);
            $product_name = $row[1];
            $product_type = Product::TYPE_PRODUCT_WITH_STOCK;
            $product_kode_simrs = $row[0];
            $product_kode_sakti = $row[3];
            $unit_detail = UnitDetailRepository::findBy(whereClause: [
                ['name', $unit_name]
            ]);

            if (!$unit_detail) {
                $title_unit = isset(self::TITLE_UNIT[$unit_name]) ? self::TITLE_UNIT[$unit_name] : $unit_name;
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

            ProductRepository::create([
                'unit_id' => $unit->id,
                'name' => $product_name,
                'type' => $product_type,
                'kode_simrs' => $product_kode_simrs,
                'kode_sakti' => $product_kode_sakti,
            ]);
        };
    }

    public function formatImportMasterDataProductGiziKatering()
    {
        return function ($row) {
            $unit_name = isset(self::TRANSLATE_UNIT[strtoupper($row[4])]) ? self::TRANSLATE_UNIT[strtoupper($row[4])] : strtoupper($row[4]);
            $product_name = $row[3];
            $product_type = $row[0] == 'YA' ? Product::TYPE_PRODUCT_WITHOUT_STOCK : Product::TYPE_PRODUCT_WITH_STOCK;
            $product_kode_simrs = $row[1];
            $product_kode_sakti = $row[2];
            $unit_detail = UnitDetailRepository::findBy(whereClause: [
                ['name', $unit_name]
            ]);

            if (!$unit_detail) {
                $title_unit = isset(self::TITLE_UNIT[$unit_name]) ? self::TITLE_UNIT[$unit_name] : $unit_name;
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

    public function formatImportMasterDataProductGiziPasien()
    {
        return function ($row) {
            $unit_name = isset(self::TRANSLATE_UNIT[strtoupper($row[4])]) ? self::TRANSLATE_UNIT[strtoupper($row[4])] : strtoupper($row[4]);
            $product_name = $row[3];
            $product_type = $row[2] == 'YA' ? Product::TYPE_PRODUCT_WITHOUT_STOCK : Product::TYPE_PRODUCT_WITH_STOCK;
            $product_kode_simrs = $row[0];
            $product_kode_sakti = $row[1];
            $unit_detail = UnitDetailRepository::findBy(whereClause: [
                ['name', $unit_name]
            ]);

            if (!$unit_detail) {
                $title_unit = isset(self::TITLE_UNIT[$unit_name]) ? self::TITLE_UNIT[$unit_name] : $unit_name;
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

    public function render()
    {
        return view('livewire.core.import-data.master-data');
    }
}
