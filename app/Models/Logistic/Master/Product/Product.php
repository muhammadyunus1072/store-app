<?php

namespace App\Models\Logistic\Master\Product;

use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Logistic\Master\Unit\Unit;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Logistic\Master\Unit\UnitDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Logistic\Master\Product\ProductCategory;
use App\Models\Rsmh\Sakti\InterkoneksiSaktiCoa;
use App\Models\Rsmh\Sakti\InterkoneksiSaktiKbki;

class Product extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'unit_id',
        'plu',
        'name',
        'type',
        'min_stock',
        'max_stock',
    ];

    protected $guarded = ['id'];

    const TYPE_PRODUCT_WITH_STOCK = 'product_with_stock';
    const TYPE_PRODUCT_WITHOUT_STOCK = 'product_without_stock';
    const TYPE_SERVICE = 'service';
    const TYPE_CHOICE = [
        self::TYPE_PRODUCT_WITH_STOCK => "Barang dengan Stok",
        self::TYPE_PRODUCT_WITHOUT_STOCK => "Barang tanpa Stok",
        self::TYPE_SERVICE => "Jasa",
    ];

    protected static function onBoot()
    {
        self::deleted(function ($model) {
            $model->productCategories()->delete();
        });
    }

    public static function translateType($type)
    {
        return self::TYPE_CHOICE[$type];
    }

    public function saveInfo($object, $prefix = "product")
    {
        // $object[$prefix . "_plu"] = $this->plu;
        $object[$prefix . "_name"] = $this->name;
        $object[$prefix . "_type"] = $this->type;

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

    public function getText()
    {
        return "{$this->name}";
    }

    public function getTranslatedType()
    {
        return self::translateType($this->type);
    }

    /*
    | RELATIONSHIP
    */

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    public function productCategories()
    {
        return $this->hasMany(ProductCategory::class, 'product_id', 'id');
    }

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class, 'product_id', 'id');
    }
}
