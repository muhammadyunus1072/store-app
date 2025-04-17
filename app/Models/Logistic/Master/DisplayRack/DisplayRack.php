<?php

namespace App\Models\Logistic\Master\DisplayRack;

use App\Models\Core\Company\CompanyDisplayRack;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DisplayRack extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'name',
    ];

    protected $guarded = ['id'];

    public function saveInfo($object, $prefix = "display_rack")
    {
        $object[$prefix . "_name"] = $this->name;
        // $object[$prefix . "_id_sub"] = $this->id_sub;
        // $object[$prefix . "_id_bagian"] = $this->id_bagian;
        // $object[$prefix . "_id_direktorat"] = $this->id_direktorat;

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
    public function companyDisplayRacks()
    {
        return $this->hasMany(CompanyDisplayRack::class, 'display_rack_id', 'id');
    }
}
