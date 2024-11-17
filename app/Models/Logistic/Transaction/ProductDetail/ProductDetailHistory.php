<?php

namespace App\Models\Logistic\Transaction\ProductDetail;

use Sis\TrackHistory\HasTrackHistory;
use App\Models\Logistic\Master\Product\Product;
use App\Models\Logistic\Transaction\ProductDetail\ProductDetail;
use App\Repositories\Core\User\UserRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductDetailHistory extends Model
{
    use HasFactory, SoftDeletes, HasTrackHistory;

    protected $fillable = [
        'product_detail_id',
        'transaction_date',
        'quantity',
        'note',
        'remarks_id',
        'remarks_type',
        'remarks_note',
    ];

    protected static function onBoot()
    {
        self::creating(function ($model) {
            $productDetail = $model->productDetail()->lockForUpdate()->first();
            $productDetail->last_stock += $model->quantity;
            $productDetail->save();

            $model->start_stock = $productDetail->last_stock - $model->quantity;
            $model->last_stock = $productDetail->last_stock;
        });

        self::updating(function ($model) {
            if ($model->quantity != $model->getOriginal('quantity')) {
                $diffQty = $model->quantity - $model->getOriginal('quantity');

                $productDetail = $model->productDetail()->lockForUpdate()->first();
                $productDetail->last_stock += $diffQty;
                $productDetail->save();

                $model->start_stock = $productDetail->last_stock - $diffQty;
                $model->last_stock = $productDetail->last_stock;
            }
        });

        self::deleted(function ($model) {
            $productDetail = $model->productDetail()->lockForUpdate()->first();
            $productDetail->last_stock -= $model->quantity;
            $productDetail->save();
        });
    }

    public function remarksUrlButton()
    {
        if (empty($this->remarks)) {
            return "";
        }

        $authUser = UserRepository::authenticatedUser();
        $remarksInfo = $this->remarks->productDetailHistoryRemarksInfo();

        if (!$authUser->hasPermissionTo($remarksInfo['access'])) {
            return $remarksInfo['text'];
        }

        return "<a target='_blank' class='btn btn-info btn-sm' href='{$remarksInfo['url']}'>
            <i class='ki-solid ki-eye fs-1'></i>
            {$remarksInfo['text']}
        </a>";
    }

    /*
    | RELATIONSHIP
    */
    public function remarks()
    {
        return $this->belongsTo($this->remarks_type, 'remarks_id', 'id');
    }

    public function product()
    {
        return $this->belongsToMany(Product::class, 'product_details', 'product_detail_id', 'product_id');
    }

    public function productDetail()
    {
        return $this->belongsTo(ProductDetail::class, 'product_detail_id', 'id');
    }
}
