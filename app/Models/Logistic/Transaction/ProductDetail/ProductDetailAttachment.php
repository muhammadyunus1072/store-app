<?php

namespace App\Models\Logistic\Transaction\ProductDetail;

use App\Helpers\General\FileHelper;
use Sis\TrackHistory\HasTrackHistory;
use App\Models\Logistic\Transaction\ProductDetail\ProductDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class ProductDetailAttachment extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'product_detail_id',
        'file_name',
        'original_file_name',
        'note',
    ];

    public function getFile()
    {
        return Storage::url(FileHelper::LOCATION_PRODUCT_DETAIL_ATTACHMENT . $this->file_name);
    }

    /*
    | RELATIONSHIP
    */
    public function productDetail()
    {
        return $this->belongsTo(ProductDetail::class, 'product_detail_id', 'id');
    }
}
