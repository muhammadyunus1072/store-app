<?php

namespace App\Settings;

use App\Repositories\Core\Setting\SettingRepository;
use App\Repositories\Rsmh\Sakti\InterkoneksiSaktiSetting\InterkoneksiSaktiSettingRepository;

class InterkoneksiSaktiSetting
{
    const NAME = "interkoneksi_sakti_setting";

    const BARANG_INTERKONEKSI_SAKTI_KBKI_ID = 'barang_interkoneksi_sakti_kbki_id';
    const BARANG_PERSENTASE_TKDN = 'barang_persentase_tkdn';
    const BARANG_KATEGORI_PDN = 'barang_kategori_pdn';
    const BARANG_KODE_UAKPB = 'barang_kode_uakpb';
    const COA_VOL_SUB_OUTPUT = 'coa_vol_sub_output';
    const HEADER_INTERKONEKSI_SAKTI_COA_12_ID = 'header_interkoneksi_sakti_coa_12_id';
    const HEADER_KODE_SATKER = 'header_kode_satker';
    const HEADER_KATEGORI = 'header_kategori';
    const HEADER_NAMA_PENERIMA = 'header_nama_penerima';
    const HEADER_NO_REKENING = 'header_no_rekening';
    const HEADER_KODE_MATA_UANG = 'header_kode_mata_uang';
    const HEADER_NILAI_KURS = 'header_nilai_kurs';
    const HEADER_NPWP = 'header_npwp';
    const HEADER_URAIAN_DOKUMEN = 'header_uraian_dokumen';

    const ALL = [
        self::BARANG_INTERKONEKSI_SAKTI_KBKI_ID => 1,
        self::BARANG_PERSENTASE_TKDN => 1,
        self::BARANG_KATEGORI_PDN => 1,
        self::BARANG_KODE_UAKPB => 1,
        self::COA_VOL_SUB_OUTPUT => 1,
        self::HEADER_INTERKONEKSI_SAKTI_COA_12_ID => 1,
        self::HEADER_KODE_SATKER => 1,
        self::HEADER_KATEGORI => 1,
        self::HEADER_NAMA_PENERIMA => 1,
        self::HEADER_NO_REKENING => 1,
        self::HEADER_KODE_MATA_UANG => 1,
        self::HEADER_NILAI_KURS => 1,
        self::HEADER_NPWP => 1,
        self::HEADER_URAIAN_DOKUMEN => 1,
    ];

    public $parsedSetting;

    public function __construct()
    {
        $setting = InterkoneksiSaktiSettingRepository::find(1);
        $this->parsedSetting = [
            'barang_interkoneksi_sakti_kbki_id' => $setting->barang_interkoneksi_sakti_kbki_id,
            'barang_persentase_tkdn' => $setting->barang_persentase_tkdn,
            'barang_kategori_pdn' => $setting->barang_kategori_pdn,
            'barang_kode_uakpb' => $setting->barang_kode_uakpb,
            'coa_vol_sub_output' => $setting->coa_vol_sub_output,
            'header_interkoneksi_sakti_coa_12_id' => $setting->header_interkoneksi_sakti_coa_12_id,
            'header_kode_satker' => $setting->header_kode_satker,
            'header_kategori' => $setting->header_kategori,
            'header_nama_penerima' => $setting->header_nama_penerima,
            'header_no_rekening' => $setting->header_no_rekening,
            'header_kode_mata_uang' => $setting->header_kode_mata_uang,
            'header_nilai_kurs' => $setting->header_nilai_kurs,
            'header_npwp' => $setting->header_npwp,
            'header_uraian_dokumen' => $setting->header_uraian_dokumen,
        ];
    }

    public static function get($key)
    {
        $setting = app(self::class);

        if (!isset($setting->parsedSetting[$key])) {
            return null;
        }

        return $setting->parsedSetting[$key];
    }
}
