<?php

namespace App\Models\Purchasing\Master\Supplier;

use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Purchasing\Master\Supplier\Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Purchasing\Master\CategorySupplier\CategorySupplier;

class SupplierCategory extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'supplier_id',
        'category_supplier_id',
    ];

    protected $guarded = ['id'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }
    public function category_supplier()
    {
        return $this->belongsTo(CategorySupplier::class, 'category_supplier_id', 'id');
    }
}
