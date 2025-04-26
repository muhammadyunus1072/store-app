<?php

namespace App\Models\Logistic\Master\Product;

use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Logistic\Master\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Logistic\Master\CategoryProduct\CategoryProduct;
use App\Models\Logistic\Master\Unit\Unit;
use App\Models\Logistic\Master\Unit\UnitDetail;

class ProductUnit extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'product_id',
        'unit_id',
        'unit_detail_id',
        'selling_price',
        'code',
    ];

    protected $guarded = ['id'];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $model = $model->unitDetail->saveInfo($model, 'unit_detail_', [
                'is_main',
                'name',
                'value',
            ]);
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

    public function saveInfo($object, $prefix = "product_unit_", $data = null)
    {
        if($data)
        {
            foreach($data as $item)
            {
                $object[$prefix . "".$item] = $this->$item;
            }
        }else{
            $object[$prefix . "unit_id"] = $this->unit_id;
            $object[$prefix . "unit_detail_id"] = $this->unit_detail_id;
            $object[$prefix . "unit_detail_is_main"] = $this->unit_detail_is_main;
            $object[$prefix . "unit_detail_name"] = $this->unit_detail_name;
            $object[$prefix . "unit_detail_value"] = $this->unit_detail_value;
            $object[$prefix . "unit_detail_selling_price"] = $this->unit_detail_selling_price;
            $object[$prefix . "unit_detail_code"] = $this->unit_detail_code;
        }

        return $object;
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    public function unitDetail()
    {
        return $this->belongsTo(UnitDetail::class, 'unit_detail_id', 'id');
    }
}
