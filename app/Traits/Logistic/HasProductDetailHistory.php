<?php

namespace App\Traits\Logistic;

use App\Models\Logistic\Transaction\ProductDetail\ProductDetailHistory;

trait HasProductDetailHistory
{
    abstract public function productDetailHistoryRemarksInfo(): array;

    public function productDetailHistories()
    {
        return $this->hasMany(ProductDetailHistory::class, 'remarks_id', 'id')
            ->where('remarks_type', self::class);
    }
}
