<?php

namespace App\Models\Purchasing\Master\Supplier;

use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Purchasing\Master\Supplier\SupplierCategory;

class Supplier extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'name',
        'kode_simrs',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::deleted(function ($model) {
            $model->supplierCategories()->delete();
        });
    }

    public function saveInfo($object, $prefix = "supplier")
    {
        $object[$prefix . "_name"] = $this->name;

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

    public function supplierCategories()
    {
        return $this->hasMany(SupplierCategory::class, 'supplier_id', 'id');
    }
}
