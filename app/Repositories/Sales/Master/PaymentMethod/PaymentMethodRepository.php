<?php

namespace App\Repositories\Sales\Master\PaymentMethod;

use App\Models\Sales\Master\PaymentMethod;
use App\Repositories\MasterDataRepository;

class PaymentMethodRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return PaymentMethod::class;
    }

    public static function datatable()
    {
        return PaymentMethod::query();
    }
}
