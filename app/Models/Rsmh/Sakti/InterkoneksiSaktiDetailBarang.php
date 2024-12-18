<?php

namespace App\Models\Rsmh\Sakti;

use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InterkoneksiSaktiDetailBarang extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        "kode_upload",
        "no_dokumen",
        "kode_barang",
        "jumlah_barang",
        "harga_satuan",
        "kode_uakpb",
        "kode_kbki",
        "kode_coa",
        "persentase_tkdn",
        "kategori_pdn",
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
