<?php

namespace App\Helpers\General;

class RomanConverter
{
    private static array $map = [
        1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD',
        100 => 'C', 90 => 'XC', 50 => 'L', 40 => 'XL',
        10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV',
        1 => 'I'
    ];

    public static function toRoman(int $number): string
    {
        if ($number < 1 || $number > 3999) {
            throw new \InvalidArgumentException("Number must be between 1 and 3999.");
        }

        $roman = '';
        foreach (self::$map as $value => $symbol) {
            $count = intdiv($number, $value);
            $roman .= str_repeat($symbol, $count);
            $number %= $value;
        }

        return $roman;
    }
    
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
