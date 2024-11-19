<?php

namespace App\Models\Logistic\Master\Unit;

use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Logistic\Master\Unit\Unit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UnitDetail extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'unit_id',
        'is_main',
        'name',
        'value',
    ];

    CONST TRANSLATE_UNIT = [
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
    CONST TITLE_UNIT = [
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
    
    protected $guarded = ['id'];

    public function saveInfo($object, $prefix = "unit_detail")
    {
        $object[$prefix . "_unit_id"] = $this->unit_id;
        $object[$prefix . "_is_main"] = $this->is_main;
        $object[$prefix . "_name"] = $this->name;
        $object[$prefix . "_value"] = $this->value;

        return $object;
    }

    public function isDeletable()
    {
        return true;
    }

    public function isEditable()
    {
        return true;
    }

    /*
    | RELATIONSHIP
    */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }
}
