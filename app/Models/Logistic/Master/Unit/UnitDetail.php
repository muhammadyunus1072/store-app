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
