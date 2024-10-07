<?php

namespace App\Helpers;

class RomanConverter
{
    public static function number2Roman($number)
    {
        if ($number == 1) {
            $month = "I";
        } elseif ($number == 2) {
            $month = "II";
        } elseif ($number == 3) {
            $month = "III";
        } elseif ($number == 4) {
            $month = "IV";
        } elseif ($number == 5) {
            $month = "V";
        } elseif ($number == 6) {
            $month = "VI";
        } elseif ($number == 7) {
            $month = "VII";
        } elseif ($number == 8) {
            $month = "VIII";
        } elseif ($number == 9) {
            $month = "IX";
        } elseif ($number == 10) {
            $month = "X";
        } elseif ($number == 11) {
            $month = "XI";
        } else {
            $month = "XII";
        }

        return $month;
    }
}
