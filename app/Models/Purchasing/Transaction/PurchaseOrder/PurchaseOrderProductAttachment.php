<?php

namespace App\Models\Purchasing\Transaction\PurchaseOrder;

use App\Helpers\General\ImageLocationHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Sis\TrackHistory\HasTrackHistory;

class PurchaseOrderProductAttachment extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'purchase_order_product_id',
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

    public function purchaseOrderProduct()
    {
        return $this->belongsTo(PurchaseOrderProduct::class, 'purchase_order_product_id', 'id');
    }
}
