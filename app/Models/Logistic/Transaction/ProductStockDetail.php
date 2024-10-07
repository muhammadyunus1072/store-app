<?php

namespace App\Models\Logistic\Transaction;

use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Logistic\Transaction\ProductDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductStockDetail extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'product_detail_id',
        'quantity',
    ];

    public function productDetail()
    {
        return $this->belongsTo(ProductDetail::class, 'product_detail_id', 'id');
    }
}
