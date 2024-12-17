<?php

namespace App\Models\Rsmh\Sakti;

use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InterkoneksiSaktiSetting extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'barang_interkoneksi_sakti_kbki_id',
        "barang_persentase_tkdn",
        "barang_kategori_pdn",
        "barang_kode_uakpb",

        "coa_vol_sub_output",

        'header_interkoneksi_sakti_coa_12_id',
        "header_kode_satker",
        "header_kategori",
        "header_nama_penerima",
        "header_no_rekening",
        "header_kode_mata_uang",
        "header_nilai_kurs",
        "header_npwp",
        "header_uraian_dokumen",
    ];

    protected $guarded = ['id'];

    public function isDeletable()
    {
        return true;
    }

    public function isEditable()
    {
        return true;
    }
}
