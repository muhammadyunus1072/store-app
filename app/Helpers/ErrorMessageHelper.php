<?php

namespace App\Helpers;

class ErrorMessageHelper
{
    public static function stockNotAvailable(
        $productName,
        $unitName = null,
        $stock = null,
        $quantity = null
    ) {
        if ($stock == null && $quantity == null) {
            $msg = "Proses tidak dapat dilakukan dikarenakan telah terjadi perubahan pada stok {$productName}.";
        } else {
            $msg = "Stock {$productName} Tidak Mencukupi.";
            if ($stock !== null) {
                $strStock = NumberFormatter::format($stock);
                $msg .= "Tersedia {$strStock} {$unitName}.";
            }
            if ($quantity !== null) {
                $strQuantity = NumberFormatter::format($quantity);
                $msg .= "Dibutuhkan {$strQuantity} {$unitName}.";
            }
        }

        return $msg;
    }
}
