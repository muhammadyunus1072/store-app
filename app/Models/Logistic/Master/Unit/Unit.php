<?php

namespace App\Models\Logistic\Master\Unit;

use App\Models\Logistic\Master\Product\Product;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Logistic\Master\Unit\UnitDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Unit extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'title',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::deleted(function ($model) {
            $model->unitDetails()->delete();
        });
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
    public function unitDetailMain()
    {
        return $this->hasOne(UnitDetail::class, 'unit_id', 'id')->where('is_main', 1);
    }

    public function unitDetails()
    {
        return $this->hasMany(UnitDetail::class, 'unit_id', 'id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'unit_id', 'id');
    }
}
