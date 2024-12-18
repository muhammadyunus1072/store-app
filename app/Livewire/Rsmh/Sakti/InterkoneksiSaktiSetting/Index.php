<?php

namespace App\Livewire\Rsmh\Sakti\InterkoneksiSaktiSetting;

use Exception;
use App\Helpers\General\Alert;
use App\Repositories\Rsmh\Sakti\InterkoneksiSaktiCoa\InterkoneksiSaktiCoaRepository;
use App\Repositories\Rsmh\Sakti\InterkoneksiSaktiKbki\InterkoneksiSaktiKbkiRepository;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Repositories\Rsmh\Sakti\InterkoneksiSaktiSetting\InterkoneksiSaktiSettingRepository;


class Index extends Component
{
    public $barang_interkoneksi_sakti_kbki_id_choice = [];
    public $barang_interkoneksi_sakti_kbki_id;
    public $barang_persentase_tkdn;
    public $barang_kategori_pdn;
    public $barang_kode_uakpb;

    public $coa_vol_sub_output;

    public $header_interkoneksi_sakti_coa_12_id_choice = [];
    public $header_interkoneksi_sakti_coa_12_id;
    public $header_kode_satker;
    public $header_kategori;
    public $header_nama_penerima;
    public $header_no_rekening;
    public $header_kode_mata_uang;
    public $header_nilai_kurs;
    public $header_npwp;
    public $header_uraian_dokumen;

    public function mount()
    {
        $this->barang_interkoneksi_sakti_kbki_id_choice = InterkoneksiSaktiKbkiRepository::all()->pluck('nama', 'id');
        $this->header_interkoneksi_sakti_coa_12_id_choice = InterkoneksiSaktiCoaRepository::all()->pluck('kode', 'id');

        $interkoneksi_sakti_setting = InterkoneksiSaktiSettingRepository::find(1);
        if($interkoneksi_sakti_setting)
        {
            $this->barang_interkoneksi_sakti_kbki_id = $interkoneksi_sakti_setting->barang_interkoneksi_sakti_kbki_id;
            $this->barang_persentase_tkdn = $interkoneksi_sakti_setting->barang_persentase_tkdn;
            $this->barang_kategori_pdn = $interkoneksi_sakti_setting->barang_kategori_pdn;
            $this->barang_kode_uakpb = $interkoneksi_sakti_setting->barang_kode_uakpb;
            $this->coa_vol_sub_output = $interkoneksi_sakti_setting->coa_vol_sub_output;
            $this->header_interkoneksi_sakti_coa_12_id = $interkoneksi_sakti_setting->header_interkoneksi_sakti_coa_12_id;
            $this->header_kode_satker = $interkoneksi_sakti_setting->header_kode_satker;
            $this->header_kategori = $interkoneksi_sakti_setting->header_kategori;
            $this->header_nama_penerima = $interkoneksi_sakti_setting->header_nama_penerima;
            $this->header_no_rekening = $interkoneksi_sakti_setting->header_no_rekening;
            $this->header_kode_mata_uang = $interkoneksi_sakti_setting->header_kode_mata_uang;
            $this->header_nilai_kurs = $interkoneksi_sakti_setting->header_nilai_kurs;
            $this->header_npwp = $interkoneksi_sakti_setting->header_npwp;
            $this->header_uraian_dokumen = $interkoneksi_sakti_setting->header_uraian_dokumen;        
        }

    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        $this->redirectRoute('interkoneksi_sakti_setting.index');
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('interkoneksi_sakti_setting.index');
    }

    public function store()
    {
        try {
            DB::beginTransaction();
            $interkoneksi_sakti_setting = InterkoneksiSaktiSettingRepository::find(1);

            if($interkoneksi_sakti_setting)
            {
                InterkoneksiSaktiSettingRepository::update(1, [
                    'barang_interkoneksi_sakti_kbki_id' => $this->barang_interkoneksi_sakti_kbki_id,
                    'barang_persentase_tkdn' => $this->barang_persentase_tkdn,
                    'barang_kategori_pdn' => $this->barang_kategori_pdn,
                    'barang_kode_uakpb' => $this->barang_kode_uakpb,
                    'coa_vol_sub_output' => $this->coa_vol_sub_output,
                    'header_interkoneksi_sakti_coa_12_id' => $this->header_interkoneksi_sakti_coa_12_id,
                    'header_kode_satker' => $this->header_kode_satker,
                    'header_kategori' => $this->header_kategori,
                    'header_nama_penerima' => $this->header_nama_penerima,
                    'header_no_rekening' => $this->header_no_rekening,
                    'header_kode_mata_uang' => $this->header_kode_mata_uang,
                    'header_nilai_kurs' => $this->header_nilai_kurs,
                    'header_npwp' => $this->header_npwp,
                    'header_uraian_dokumen' => $this->header_uraian_dokumen,
                ]);
            } else {
                InterkoneksiSaktiSettingRepository::create([
                    'barang_interkoneksi_sakti_kbki_id' => $this->barang_interkoneksi_sakti_kbki_id,
                    'barang_persentase_tkdn' => $this->barang_persentase_tkdn,
                    'barang_kategori_pdn' => $this->barang_kategori_pdn,
                    'barang_kode_uakpb' => $this->barang_kode_uakpb,
                    'coa_vol_sub_output' => $this->coa_vol_sub_output,
                    'header_interkoneksi_sakti_coa_12_id' => $this->header_interkoneksi_sakti_coa_12_id,
                    'header_kode_satker' => $this->header_kode_satker,
                    'header_kategori' => $this->header_kategori,
                    'header_nama_penerima' => $this->header_nama_penerima,
                    'header_no_rekening' => $this->header_no_rekening,
                    'header_kode_mata_uang' => $this->header_kode_mata_uang,
                    'header_nilai_kurs' => $this->header_nilai_kurs,
                    'header_npwp' => $this->header_npwp,
                    'header_uraian_dokumen' => $this->header_uraian_dokumen,
                ]);
            }

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
        return view('livewire.rsmh.sakti.interkoneksi-sakti-setting.index');
    }
}