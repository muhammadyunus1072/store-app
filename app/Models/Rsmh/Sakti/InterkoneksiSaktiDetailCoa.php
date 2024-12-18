<?php

namespace App\Models\Rsmh\Sakti;

use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InterkoneksiSaktiDetailCoa extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        "kode_upload",
        "no_dokumen",
        "kode_coa",
        "nilai_coa_detail",
        "nilai_valas_detail",
        "vol_sub_output",
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
