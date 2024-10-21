<?php

namespace App\Models\Logistic\Transaction\GoodReceive;

use App\Helpers\General\ImageLocationHelper;
use Sis\TrackHistory\HasTrackHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Logistic\Transaction\GoodReceive\GoodReceiveProduct;

class GoodReceiveProductAttachment extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'good_receive_product_id',
        'file_name',
        'original_file_name',
        'note',
    ];

    protected $guarded = ['id'];

    public function getFile()
    {
        return Storage::url(ImageLocationHelper::FILE_GOOD_RECEIVE_PRODUCT_LOCATION . $this->file_name);
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

    public function goodReceiveProduct()
    {
        return $this->belongsTo(GoodReceiveProduct::class, 'good_receive_product_id', 'id');
    }
}
