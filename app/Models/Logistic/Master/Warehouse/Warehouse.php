<?php

namespace App\Models\Logistic\Master\Warehouse;

use App\Models\Core\Company\CompanyWarehouse;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'name',
        'id_sub',
        'id_bagian',
        'id_direktorat',
    ];

    protected $guarded = ['id'];

    public function saveInfo($object, $prefix = "warehouse")
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
    public function companyWarehouses()
    {
        return $this->hasMany(CompanyWarehouse::class, 'warehouse_id', 'id');
    }
}
