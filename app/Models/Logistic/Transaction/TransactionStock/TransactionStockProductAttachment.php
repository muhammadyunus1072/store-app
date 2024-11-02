<?php

namespace App\Models\Logistic\Transaction\TransactionStock;

use App\Helpers\General\FileHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Sis\TrackHistory\HasTrackHistory;

class TransactionStockProductAttachment extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'transaction_stock_product_id',
        'file_name',
        'original_file_name',
        'note',
    ];

    protected $guarded = ['id'];

    public function getFile()
    {
        return Storage::url(FileHelper::LOCATION_PRODUCT_DETAIL_ATTACHMENT . $this->file_name);
    }

    /*
    | RELATIONSHIP
    */
    public function transactionStockProduct()
    {
        return $this->belongsTo(TransactionStockProduct::class, 'transaction_stock_product_id', 'id');
    }
}
